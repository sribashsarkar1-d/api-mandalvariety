<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require '../config/l-smtp.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');

if(empty($email)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT id, email, is_verified
    FROM users
    WHERE email = ?
");

$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Email not found'
    ]);

    exit;
}

if($user['is_verified'] != 1) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Account not verified'
    ]);

    exit;
}

$otp = rand(100000, 999999);

$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

$update = $pdo->prepare("
    UPDATE users
    SET login_otp = ?, otp_expiry = ?
    WHERE id = ?
");

$update->execute([
    $otp,
    $expiry,
    $user['id']
]);

$mail = sendOTP($email, $otp);

if($mail['status']) {

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully'
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $mail['message']
    ]);
}
?>