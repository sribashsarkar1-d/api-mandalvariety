<?php
header('Content-Type: application/json');
require '../config/database.php';

$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;

if (!$id && !$slug) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID or Slug is required']);
    exit;
}

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM policies WHERE id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM policies WHERE slug = ?");
    $stmt->execute([$slug]);
}

$policy = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$policy) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Policy not found']);
    exit;
}

echo json_encode(['success' => true, 'data' => $policy]);
?>
