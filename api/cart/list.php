<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'user_id is required']);
        exit;
    }

    // 1. Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => [],
                'summary' => [
                    'total_items' => 0,
                    'subtotal' => 0,
                    'total' => 0
                ]
            ]
        ]);
        exit;
    }

    // 2. Get cart items joined with products
    $stmt = $pdo->prepare("
        SELECT 
            ci.id as cart_item_id, 
            ci.quantity, 
            ci.price_at_purchase,
            p.id as product_id, 
            p.name, 
            p.price as current_price, 
            p.images, 
            p.stock_quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    $total_items = 0;

    foreach ($items as &$item) {
        $item['subtotal'] = $item['quantity'] * $item['current_price'];
        $subtotal += $item['subtotal'];
        $total_items += $item['quantity'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'cart_id' => $cart['id'],
            'items' => $items,
            'summary' => [
                'total_items' => $total_items,
                'subtotal' => $subtotal,
                'total' => $subtotal // can add tax/shipping here later
            ]
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
