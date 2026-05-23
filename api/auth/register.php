<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require '../config/smtp.php';

session_start(); // Start session to store temp data

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

if (empty($name) || empty($email) || empty($phone) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}

// CHECK IF EMAIL ALREADY REGISTERED
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_verified = 1");
$stmt->execute([$email]);
if ($stmt->rowCount()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// GENERATE OTP
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// **STORE TEMP DATA IN SESSION** (NOT DATABASE)
$_SESSION['register_temp'] = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'otp' => $otp,
    'otp_expires' => time() + 600 // 10 minutes
];

// SEND OTP EMAIL
$result = sendOTP($email, $otp);
if ($result['status']) {
    echo json_encode([
        'success' => true, 
        'message' => 'OTP sent! Enter OTP to complete registration.'
    ]);
} else {
    unset($_SESSION['register_temp']);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
}
?>
