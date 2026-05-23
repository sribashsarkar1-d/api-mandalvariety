<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $user_id = $data['user_id'] ?? null;
    $full_name = trim($data['full_name'] ?? '');
    $date_of_birth = $data['date_of_birth'] ?? null;
    $document_type = $data['document_type'] ?? null;
    $method_type = $data['method'] ?? 'document';

    if (empty($user_id) || empty($full_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID and full name are required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO age_verifications (user_id, full_name, date_of_birth, document_type, method, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    if ($stmt->execute([$user_id, $full_name, $date_of_birth, $document_type, $method_type])) {
        echo json_encode(['success' => true, 'message' => 'Verification submitted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to submit verification']);
    }
} elseif ($method === 'PUT') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    
    $status = $data['status'] ?? null;
    $review_notes = $data['review_notes'] ?? null;
    $verified_age = $data['verified_age'] ?? null;

    if (!$status) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status is required to update']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE age_verifications SET status = ?, review_notes = COALESCE(?, review_notes), verified_age = COALESCE(?, verified_age), reviewed_at = CURRENT_TIMESTAMP WHERE id = ?");
    if ($stmt->execute([$status, $review_notes, $verified_age, $id])) {
        echo json_encode(['success' => true, 'message' => 'Verification updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update verification']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
