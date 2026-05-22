<?php
header('Content-Type: application/json');

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$product_id = $data['product_id'] ?? null;
$user_id = $_GET['user_id'] ?? $data['user_id'] ?? null;
$rating = $data['rating'] ?? 5;
$title = trim($data['title'] ?? '');
$comment = trim($data['comment'] ?? '');

if (!$product_id || !$user_id || !$rating) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID, User ID, and Rating are required']);
    exit;
}

// Check if review already exists
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')");
if ($stmt->execute([$product_id, $user_id, $rating, $title, $comment])) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully and is pending approval']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
}
?>
