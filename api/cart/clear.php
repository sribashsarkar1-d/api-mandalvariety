<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
$user_id = (int)($_GET['user_id'] ?? 1);

// Delete all cart items
$stmt = $pdo->prepare("
    DELETE ci FROM cart_items ci 
    JOIN carts c ON ci.cart_id = c.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);

echo json_encode(['success' => true, 'message' => 'Cart cleared']);
?>
