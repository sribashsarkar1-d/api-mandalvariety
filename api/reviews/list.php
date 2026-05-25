<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$product_id = $_GET['product_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;

$query = "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.status = 'approved'";
$params = [];

if ($product_id) {
    $query .= " AND r.product_id = ?";
    $params[] = $product_id;
}

if ($user_id) {
    $query .= " AND r.user_id = ?";
    $params[] = $user_id;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reviewCount = count($reviews);
$totalRating = 0;

foreach ($reviews as $review) {
    $totalRating += (float)($review['rating'] ?? 0);
}

$averageRating = $reviewCount > 0 ? round($totalRating / $reviewCount, 1) : 0.0;

echo json_encode([
    'success' => true, 
    'averageRating' => $averageRating,
    'reviewCount' => $reviewCount,
    'data' => $reviews
]);
?>
