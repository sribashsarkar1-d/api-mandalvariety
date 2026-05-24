<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require '../config/smtp.php';

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

// CHECK IF EMAIL ALREADY REGISTERED AND VERIFIED
$stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    if ($existingUser['is_verified'] == 1) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered and verified']);
        exit;
    } else {
        // Delete the unverified user to start fresh
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$existingUser['id']]);
    }
}

// GENERATE OTP
$otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// INSERT INTO DATABASE AS UNVERIFIED
$insert = $pdo->prepare("
    INSERT INTO users (name, email, phone, password, is_verified, role, login_otp, otp_expiry) 
    VALUES (?, ?, ?, ?, 0, 'customer', ?, ?)
");

if (!$insert->execute([$name, $email, $phone, $hashed_password, $otp, $expiry])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error during registration']);
    exit;
}

// SEND OTP EMAIL
$result = sendOTP($email, $otp);
if ($result['status']) {
    echo json_encode([
        'success' => true, 
        'message' => 'OTP sent! Enter OTP to complete registration.'
    ]);
} else {
    // If email fails, delete the unverified record
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email: ' . $result['message']]);
}
?>
