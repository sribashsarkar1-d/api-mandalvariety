<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cart_item_id = (int)($input['cart_item_id'] ?? 0);
    $product_id = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 0);

    if (!$user_id || (!$cart_item_id && !$product_id) || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    // 1. Verify user owns this cart item (search by cart_item_id or product_id)
    if ($cart_item_id > 0) {
        $stmt = $pdo->prepare("
            SELECT ci.id, ci.product_id, ci.quantity as current_quantity, p.name, p.stock_quantity, p.stock, p.is_active, p.status 
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE ci.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$cart_item_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT ci.id, ci.product_id, ci.quantity as current_quantity, p.name, p.stock_quantity, p.stock, p.is_active, p.status 
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE ci.product_id = ? AND c.user_id = ?
        ");
        $stmt->execute([$product_id, $user_id]);
    }
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    $actualCartItemId = (int)$item['id'];
    $available_stock = max(0, (int)($item['stock_quantity'] ?? 0));
    $isActive = ((int)($item['is_active'] ?? 1) === 1) && (($item['status'] ?? 'published') !== 'inactive');

    // 2. Check active and stock status
    if (!$isActive || $available_stock <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "{$item['name']} is out of stock.",
            'available_stock' => 0
        ]);
        exit;
    }

    if ($quantity > $available_stock) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "Only {$available_stock} units available in stock.",
            'available_stock' => $available_stock
        ]);
        exit;
    }

    // 3. Update quantity
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $actualCartItemId]);

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated',
        'data' => [
            'cart_item_id' => $actualCartItemId,
            'quantity' => $quantity,
            'available_stock' => $available_stock
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>
