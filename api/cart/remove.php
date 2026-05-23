<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
$user_id = (int)($_GET['user_id'] ?? 1);
$cart_item_id = (int)($_GET['id'] ?? $_POST['cart_item_id'] ?? 0);

if (!$cart_item_id) {
    echo json_encode(['success' => false, 'message' => 'Cart item ID required']);
    exit;
}

// Verify and delete
$stmt = $pdo->prepare("
    DELETE ci FROM cart_items ci 
    JOIN carts c ON ci.cart_id = c.id 
    WHERE ci.id = ? AND c.user_id = ?
");
$stmt->execute([$cart_item_id, $user_id]);
$deleted = $stmt->rowCount();

echo json_encode([
    'success' => true, 
    'message' => $deleted ? 'Item removed' : 'Item not found'
]);
?>
