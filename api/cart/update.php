<?php
header('Content-Type: application/json');
require_once '../config/database.php';
$user_id = (int)($_GET['user_id'] ?? 1);
$cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if (!$cart_item_id || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item or quantity']);
    exit;
}

// Verify cart_item belongs to user
$stmt = $pdo->prepare("
    SELECT ci.id FROM cart_items ci 
    JOIN carts c ON ci.cart_id = c.id 
    WHERE ci.id = ? AND c.user_id = ?
");
$stmt->execute([$cart_item_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

// Update quantity
if ($quantity === 0) {
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$cart_item_id]);
    $message = 'Item removed';
} else {
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_item_id]);
    $message = 'Quantity updated';
}

echo json_encode(['success' => true, 'message' => $message]);
?>
