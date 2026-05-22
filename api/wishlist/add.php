<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$user_id = (int)($_GET['user_id'] ?? 1);
$product_id = (int)($_POST['product_id'] ?? 0);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

// Check if already exists
$stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
    exit;
}

// Check product exists
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Add to wishlist
$stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
$stmt->execute([$user_id, $product_id]);

echo json_encode(['success' => true, 'message' => 'Added to wishlist ❤️']);
?>
