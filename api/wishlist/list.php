<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$user_id = (int)($_GET['user_id'] ?? 1);

// Get wishlist items with product details
$stmt = $pdo->prepare("
    SELECT w.id, w.product_id, w.created_at,
           p.name, p.price, p.images, p.sku
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ? AND p.is_active = 1
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => [
        'user_id' => $user_id,
        'items' => $items,
        'total_items' => count($items)
    ]
], JSON_PRETTY_PRINT);
?>
