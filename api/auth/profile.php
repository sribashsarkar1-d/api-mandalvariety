<?php
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'] ?? null;
$auth_token = null;

// Extract Bearer Token
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

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please provide a valid Bearer token or ?user_id=1.']);
    exit;
}

if ($method === 'GET') {
    // Get Profile
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, address, city, state, country, profile_image, pincode, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $user]);

} elseif ($method === 'PUT' || $method === 'POST') {
    // Update Profile
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }

    $updates = [];
    $params = [];
    $allowed_fields = ['name', 'phone', 'address', 'city', 'state', 'country', 'pincode', 'profile_image'];

    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update provided']);
        exit;
    }

    $params[] = $user_id;
    $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }

} elseif ($method === 'DELETE') {
    // Delete Profile
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        // Clear session if active
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'User account deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete user account']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
