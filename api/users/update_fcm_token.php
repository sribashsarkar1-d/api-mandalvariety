<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

$user_id = $data['user_id'] ?? null;
$fcm_token = $data['fcm_token'] ?? null;

// Support JWT/Bearer Token if user_id is not directly passed
if (!$user_id) {
    $headers = getallheaders();
    if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        $auth_token = $matches[1];
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM user_tokens WHERE auth_token = ? AND (expires_at IS NULL OR expires_at > NOW())");
            $stmt->execute([$auth_token]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user_id = $row['user_id'];
            }
        } catch (Exception $e) {}
    }
}

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please provide user_id or a valid Bearer token.']);
    exit;
}

if (!$fcm_token) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'FCM token is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
    if ($stmt->execute([$fcm_token, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'FCM token updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update FCM token']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
