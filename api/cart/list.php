<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
$user_id = (int)($_GET['user_id'] ?? 1);

// Get/Create cart
$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch();

if (!$cart) {
    $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $cart_id = $pdo->lastInsertId();
} else {
    $cart_id = $cart['id'];
}

// Get items with product details
$stmt = $pdo->prepare("
    SELECT ci.id, ci.product_id, ci.quantity, ci.price_at_purchase,
           p.name, p.price, p.images, p.sku
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = array_sum(array_map(fn($item) => $item['quantity'] * $item['price_at_purchase'], $items));

echo json_encode([
    'success' => true,
    'data' => [
        'cart_id' => $cart_id,
        'user_id' => $user_id,
        'items' => $items,
        'total_items' => count($items),
        'subtotal' => $total
    ]
], JSON_PRETTY_PRINT);
?>
