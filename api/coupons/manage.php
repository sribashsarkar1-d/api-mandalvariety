<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $code = trim($data['code'] ?? '');
    $discount = $data['discount'] ?? 0;
    $expiry = $data['expiry'] ?? null;

    if (empty($code)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Code is required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO coupons (code, discount, expiry) VALUES (?, ?, ?)");
    if ($stmt->execute([$code, $discount, $expiry])) {
        echo json_encode(['success' => true, 'message' => 'Coupon created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create coupon']);
    }
} elseif ($method === 'PUT') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    $code = trim($data['code'] ?? '');
    $discount = $data['discount'] ?? null;
    $expiry = $data['expiry'] ?? null;

    $stmt = $pdo->prepare("UPDATE coupons SET code = COALESCE(NULLIF(?, ''), code), discount = COALESCE(?, discount), expiry = COALESCE(?, expiry) WHERE id = ?");
    if ($stmt->execute([$code, $discount, $expiry, $id])) {
        echo json_encode(['success' => true, 'message' => 'Coupon updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update coupon']);
    }
} elseif ($method === 'DELETE') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete coupon']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
