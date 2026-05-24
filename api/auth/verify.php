<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$user_otp = trim($input['otp'] ?? '');

if (empty($email) || empty($user_otp)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and OTP required']);
    exit;
}

// CHECK OTP AND EXPIRY FOR UNVERIFIED USER
$stmt = $pdo->prepare("
    SELECT id 
    FROM users 
    WHERE email = ? 
    AND login_otp = ? 
    AND is_verified = 0 
    AND otp_expiry > NOW()
");
$stmt->execute([$email, $user_otp]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP, or email not found']);
    exit;
}

// OTP MATCHED → MARK AS VERIFIED AND CLEAR OTP
$update = $pdo->prepare("
    UPDATE users 
    SET is_verified = 1, login_otp = NULL, otp_expiry = NULL 
    WHERE id = ?
");

if ($update->execute([$user['id']])) {
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! You can now login.',
        'user_id' => $user['id']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Verification failed']);
}
?>
