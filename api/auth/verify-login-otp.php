<?php

header('Content-Type: application/json');

session_start();

require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$otp   = trim($data['otp'] ?? '');

if(empty($email) || empty($otp)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Email and OTP required'
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE email = ?
    AND login_otp = ?
    AND otp_expiry > NOW()
    AND is_verified = 1
");

$stmt->execute([
    $email,
    $otp
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired OTP'
    ]);

    exit;
}

$clear = $pdo->prepare("
    UPDATE users
    SET login_otp = NULL,
        otp_expiry = NULL
    WHERE id = ?
");

$clear->execute([$user['id']]);

unset($user['password']);
unset($user['login_otp']);
unset($user['otp_expiry']);

$_SESSION['user_id'] = $user['id'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => $user
]);
?>