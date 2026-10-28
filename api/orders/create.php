<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

$rawInput = file_get_contents('php://input');
if (empty($rawInput) && php_sapi_name() === 'cli') {
    $rawInput = file_get_contents('php://stdin');
}
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = !empty($_POST) ? $_POST : [];
}

$user_id = (int)($input['user_id'] ?? $_GET['user_id'] ?? $_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required to place an order.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$delivery_address = trim($input['delivery_address'] ?? $input['address'] ?? $input['shipping_address'] ?? '');
if (empty($delivery_address)) {
    $delivery_address = 'No Address Provided';
}
$pincode = trim($input['pincode'] ?? $input['pin_code'] ?? '000000');
$notes = trim($input['notes'] ?? $input['order_notes'] ?? '');

try {
    // ── 1. Begin Database Transaction ───────────────────────────────────────────
    $pdo->beginTransaction();

    // ── 2. Collect Items (From payload or from user's database cart) ───────────────
    $items_to_order = [];

    if (!empty($input['items']) && is_array($input['items'])) {
        foreach ($input['items'] as $item) {
            $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
            $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
            if ($pid > 0 && $qty > 0) {
                $items_to_order[] = ['product_id' => $pid, 'quantity' => $qty];
            }
        }
    } elseif (!empty($input['cart_items']) && is_array($input['cart_items'])) {
        foreach ($input['cart_items'] as $item) {
            $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
            $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
            if ($pid > 0 && $qty > 0) {
                $items_to_order[] = ['product_id' => $pid, 'quantity' => $qty];
            }
        }
    } elseif (!empty($input['products']) && is_array($input['products'])) {
        foreach ($input['products'] as $item) {
            $pid = (int)($item['product_id'] ?? $item['id'] ?? 0);
            $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
            if ($pid > 0 && $qty > 0) {
                $items_to_order[] = ['product_id' => $pid, 'quantity' => $qty];
            }
        }
    } elseif (!empty($input['product_id']) || !empty($_GET['product_id'])) {
        $pid = (int)($input['product_id'] ?? $_GET['product_id']);
        $qty = (int)($input['quantity'] ?? $_GET['quantity'] ?? 1);
        if ($pid > 0 && $qty > 0) {
            $items_to_order[] = ['product_id' => $pid, 'quantity' => $qty];
        }
    }

    // If no direct items provided, load from user's database cart with lock
    if (empty($items_to_order)) {
        $stmtCart = $pdo->prepare("
            SELECT ci.id as cart_item_id, ci.product_id, ci.quantity
            FROM cart_items ci 
            JOIN carts c ON ci.cart_id = c.id 
            WHERE c.user_id = ?
            FOR UPDATE
        ");
        $stmtCart->execute([$user_id]);
        $db_cart_items = $stmtCart->fetchAll(PDO::FETCH_ASSOC);

        foreach ($db_cart_items as $ci) {
            $pid = (int)$ci['product_id'];
            $qty = (int)$ci['quantity'];
            if ($pid > 0 && $qty > 0) {
                $items_to_order[] = ['product_id' => $pid, 'quantity' => $qty];
            }
        }
    }

    if (empty($items_to_order)) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cart is empty.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── 3. Aggregate Requested Quantities by Product ID ─────────────────────────
    $qty_per_product = [];
    foreach ($items_to_order as $item) {
        $pid = $item['product_id'];
        $qty_per_product[$pid] = ($qty_per_product[$pid] ?? 0) + $item['quantity'];
    }

    // ── 4. Lock Products for Update (in sorted order to prevent deadlocks) ───────
    $product_ids = array_keys($qty_per_product);
    sort($product_ids);

    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmtProducts = $pdo->prepare("
        SELECT id, name, price, discount_price, stock_quantity, stock, is_active, images
        FROM products
        WHERE id IN ($placeholders)
        FOR UPDATE
    ");
    $stmtProducts->execute($product_ids);
    $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

    $productMap = [];
    foreach ($products as $p) {
        $productMap[(int)$p['id']] = $p;
    }

    // ── 5. Authoritative Stock & Availability Validation ─────────────────────────
    $unavailable_products = [];
    foreach ($product_ids as $pid) {
        $p = $productMap[$pid] ?? null;
        $reqQty = $qty_per_product[$pid];

        if (!$p) {
            $unavailable_products[] = [
                'product_id' => $pid,
                'product_name' => 'Unavailable Product',
                'requested_quantity' => $reqQty,
                'available_stock' => 0,
                'reason' => 'Product does not exist'
            ];
            continue;
        }

        $isActive = ((int)($p['is_active'] ?? 1) === 1);
        if (isset($p['status'])) {
            $isActive = $isActive && ($p['status'] !== 'inactive');
        }
        $current_stock = max(0, max((int)($p['stock_quantity'] ?? 0), (int)($p['stock'] ?? 0)));

        if (!$isActive) {
            $unavailable_products[] = [
                'product_id' => $pid,
                'product_name' => $p['name'],
                'requested_quantity' => $reqQty,
                'available_stock' => $current_stock,
                'reason' => 'Product is not available for purchase'
            ];
            continue;
        }

        if ($current_stock <= 0) {
            $unavailable_products[] = [
                'product_id' => $pid,
                'product_name' => $p['name'],
                'requested_quantity' => $reqQty,
                'available_stock' => 0,
                'reason' => 'Out of stock'
            ];
            continue;
        }

        if ($reqQty > $current_stock) {
            $unavailable_products[] = [
                'product_id' => $pid,
                'product_name' => $p['name'],
                'requested_quantity' => $reqQty,
                'available_stock' => $current_stock,
                'reason' => "Requested $reqQty units, but only $current_stock units available"
            ];
            continue;
        }
    }

    if (!empty($unavailable_products)) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Some products are out of stock. Please update your cart.',
            'unavailable_products' => $unavailable_products
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── 6. Price Calculation (Server-Side Authoritative) ─────────────────────────
    $validated_items = [];
    $total_amount = 0.0;

    foreach ($items_to_order as $item) {
        $pid = $item['product_id'];
        $qty = $item['quantity'];
        $p = $productMap[$pid];

        $unit_price = (float)$p['price'];
        if (isset($p['discount_price']) && (float)$p['discount_price'] > 0 && (float)$p['discount_price'] < $unit_price) {
            $unit_price = (float)$p['discount_price'];
        }

        $item_subtotal = $unit_price * $qty;
        $total_amount += $item_subtotal;

        $validated_items[] = [
            'product_id' => $pid,
            'name' => $p['name'],
            'quantity' => $qty,
            'unit_price' => $unit_price,
            'images' => $p['images'] ?? null
        ];
    }

    // ── 7. Calculate Grand Total ────────────────────────────────────────────────
    $delivery_charge = 10.00;
    if (isset($input['delivery_charge']) && is_numeric($input['delivery_charge'])) {
        $delivery_charge = (float)$input['delivery_charge'];
    }
    $tax_amount = 0.00;
    $grand_total = $total_amount + $delivery_charge + $tax_amount;

    $order_number = 'NEX' . date('Ymd') . rand(1000, 9999);
    date_default_timezone_set('Asia/Kolkata');
    $created_at = date('Y-m-d H:i:s');

    // ── 8. Insert Order Record ──────────────────────────────────────────────────
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders (
            user_id, order_number, total_amount, delivery_charge, 
            tax_amount, grand_total, delivery_address, pincode, notes, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
    ");
    $stmtOrder->execute([
        $user_id, $order_number, $total_amount, $delivery_charge, 
        $tax_amount, $grand_total, $delivery_address, $pincode, $notes ?: null, $created_at
    ]);
    $order_id = (int)$pdo->lastInsertId();

    // ── 9. Insert Order Items ───────────────────────────────────────────────────
    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)
    ");
    foreach ($validated_items as $vItem) {
        $stmtItem->execute([
            $order_id, 
            $vItem['product_id'], 
            $vItem['quantity'], 
            $vItem['unit_price']
        ]);
    }

    // ── 10. Deduct Stock Accurately for Ordered Products ────────────────────────
    $stmtDeduct = $pdo->prepare("
        UPDATE products 
        SET stock_quantity = ?,
            stock = ?
        WHERE id = ?
    ");

    foreach ($qty_per_product as $pid => $deductQty) {
        $p = $productMap[$pid];
        $current_stock = max(0, max((int)($p['stock_quantity'] ?? 0), (int)($p['stock'] ?? 0)));
        $new_stock = max(0, $current_stock - $deductQty);

        $stmtDeduct->execute([
            $new_stock,
            $new_stock,
            $pid
        ]);
    }

    // ── 11. Clear Cart ───────────────────────────────────────────────────────────
    $stmtClear = $pdo->prepare("
        DELETE ci FROM cart_items ci 
        JOIN carts c ON ci.cart_id = c.id 
        WHERE c.user_id = ?
    ");
    $stmtClear->execute([$user_id]);

    // ── 12. Commit Transaction ──────────────────────────────────────────────────
    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── 13. Trigger FCM Notification (Non-blocking outside transaction) ──────────
if (file_exists(__DIR__ . '/../../admin/includes/fcm_helper.php')) {
    require_once __DIR__ . '/../../admin/includes/fcm_helper.php';
    try {
        $tokenStmt = $pdo->prepare("SELECT fcm_token FROM users WHERE id = ? AND fcm_token IS NOT NULL AND fcm_token != ''");
        $tokenStmt->execute([$user_id]);
        
        if ($row = $tokenStmt->fetch(PDO::FETCH_ASSOC)) {
            $fcmToken = $row['fcm_token'];
            $title = "Order Confirmed!";
            
            $product_names = [];
            foreach ($validated_items as $item) {
                $product_names[] = $item['name'] . ' (x' . $item['quantity'] . ')';
            }
            $product_list_str = implode(', ', $product_names);
            if (strlen($product_list_str) > 50) {
                $product_list_str = substr($product_list_str, 0, 47) . '...';
            }
            
            $body = "Order #" . $order_number . " of Rs " . round($grand_total, 2) . " confirmed! Items: " . $product_list_str;
            $fcmData = [
                'type' => 'order_status',
                'order_id' => $order_id
            ];
            
            $imageUrl = null;
            if (!empty($validated_items[0]['images'])) {
                $imagesArray = json_decode($validated_items[0]['images'], true);
                if (!empty($imagesArray) && is_array($imagesArray)) {
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
                    $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $basePath = (strpos($domainName, 'localhost') !== false) ? '/auth-api/' : '/';
                    $imageUrl = $protocol . $domainName . $basePath . 'uploads/' . $imagesArray[0];
                }
            }
            
            sendFCMNotification($fcmToken, $title, $body, $imageUrl, $fcmData);
        }
    } catch (Exception $e) {
        error_log('Order FCM Error: ' . $e->getMessage());
    }
}

// ── 14. Return Success Response ──────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'message' => 'Order created successfully!',
    'data' => [
        'order_id' => (string)$order_id, 
        'order_number' => $order_number,
        'grand_total' => round($grand_total, 2),
        'total_amount' => round($total_amount, 2),
        'delivery_charge' => round($delivery_charge, 2),
        'items' => array_map(function($item) {
            return [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ];
        }, $validated_items)
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>