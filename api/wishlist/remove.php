<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$user_id = (int)($_GET['user_id'] ?? 1);
$product_id = (int)($_GET['product_id'] ?? 0);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

// Remove from wishlist
$stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$deleted = $stmt->rowCount();

echo json_encode([
    'success' => true,
    'message' => $deleted ? 'Removed from wishlist 🗑️' : 'Not in wishlist'
]);
?>
