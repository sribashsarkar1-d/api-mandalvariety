<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_id = (int)($input['user_id'] ?? $_GET['user_id'] ?? 1);

$delivery_address = $input['delivery_address'] ?? $input['address'] ?? 'No Address Provided';
$pincode = $input['pincode'] ?? $input['pin_code'] ?? '000000';

// 🔥 FIXED: Use ci.id (cart_items.id) instead of just 'id'
$stmt = $pdo->prepare("
    SELECT ci.id, ci.product_id, ci.quantity, ci.price_at_purchase 
    FROM cart_items ci 
    JOIN carts c ON ci.cart_id = c.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate totals
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['quantity'] * $item['price_at_purchase'];
}
$delivery_charge = 50.00;
$tax_amount = $total_amount * 0.18; // 18% GST
$grand_total = $total_amount + $delivery_charge + $tax_amount;

// Generate order number
$order_number = 'NEX' . date('Ymd') . rand(1000, 9999);

// Create order
$stmt = $pdo->prepare("
    INSERT INTO orders (
        user_id, order_number, total_amount, delivery_charge, 
        tax_amount, grand_total, delivery_address, pincode, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([
    $user_id, $order_number, $total_amount, $delivery_charge, 
    $tax_amount, $grand_total, $delivery_address, $pincode
]);
$order_id = $pdo->lastInsertId();

// Create order items
$stmt = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price) 
    VALUES (?, ?, ?, ?)
");
foreach ($cart_items as $item) {
    $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price_at_purchase']]);
}

// Clear cart
$stmt = $pdo->prepare("
    DELETE ci FROM cart_items ci 
    JOIN carts c ON ci.cart_id = c.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);

echo json_encode([
    'success' => true,
    'message' => 'Order created successfully! 🎉',
    'data' => [
        'order_id' => $order_id, 
        'order_number' => $order_number,
        'grand_total' => round($grand_total, 2)
    ]
], JSON_PRETTY_PRINT);
?>
