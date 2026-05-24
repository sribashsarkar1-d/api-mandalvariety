<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cart_item_id = (int)($input['cart_item_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 0);

    if (!$user_id || !$cart_item_id || $quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    // 1. Verify user owns this cart item
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, p.stock_quantity 
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_item_id, $user_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    // 2. Check stock
    if ($quantity > $item['stock_quantity']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Only {$item['stock_quantity']} available"]);
        exit;
    }

    // 3. Update quantity
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_item_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>
