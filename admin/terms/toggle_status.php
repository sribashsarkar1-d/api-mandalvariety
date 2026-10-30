<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;

if ($id <= 0 || $isActive === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE terms_and_conditions SET is_active = ? WHERE id = ?");
    $stmt->execute([$isActive ? 1 : 0, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'is_active' => $isActive ? 1 : 0
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
