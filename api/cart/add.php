<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $input = json_decode(file_get_contents('php://input'), true);

    $product_id = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);

    if (!$user_id || !$product_id || $quantity < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid product_id or quantity. Use: {"product_id":1,"quantity":2}'
        ]);
        exit;
    }

    // QUERY 1: PRODUCTS - check existence, active status and stock
    $stmt = $pdo->prepare("SELECT id, name, price, discount_price, stock_quantity, stock, is_active, status FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found.'
        ]);
        exit;
    }

    $isActive = ((int)($product['is_active'] ?? 1) === 1) && (($product['status'] ?? 'published') !== 'inactive');
    if (!$isActive) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "{$product['name']} is currently unavailable for purchase.",
            'available_stock' => 0
        ]);
        exit;
    }

    $available_stock = max(0, (int)($product['stock_quantity'] ?? 0));
    if ($available_stock <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "{$product['name']} is out of stock.",
            'available_stock' => 0
        ]);
        exit;
    }

    // QUERY 2: CARTS
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = (int)$cart['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = (int)$pdo->lastInsertId();
    }

    // QUERY 3: CART_ITEMS (Check Existing)
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $current_qty = $existing ? (int)$existing['quantity'] : 0;
    $new_qty = $current_qty + $quantity;

    // Validate total requested quantity against available stock
    if ($new_qty > $available_stock) {
        http_response_code(400);
        $message = $current_qty > 0 
            ? "Cannot add more. You have {$current_qty} in cart and only {$available_stock} available."
            : "Cannot add {$quantity} units. Only {$available_stock} available in stock.";
        echo json_encode([
            'success' => false,
            'message' => $message,
            'available_stock' => $available_stock
        ]);
        exit;
    }

    // Effective unit price
    $effectivePrice = (float)$product['price'];
    if (isset($product['discount_price']) && (float)$product['discount_price'] > 0 && (float)$product['discount_price'] < $effectivePrice) {
        $effectivePrice = (float)$product['discount_price'];
    }

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, price_at_purchase = ? WHERE id = ?");
        $stmt->execute([$new_qty, $effectivePrice, $existing['id']]);
        $cart_item_id = (int)$existing['id'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);

        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cart_id, $product_id, $quantity, $effectivePrice]);
        $cart_item_id = (int)$pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart!',
        'data' => [
            'cart_item_id' => $cart_item_id,
            'cart_id' => $cart_id,
            'product_id' => $product_id,
            'product' => $product,
            'quantity' => $new_qty,
            'available_stock' => $available_stock
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>
