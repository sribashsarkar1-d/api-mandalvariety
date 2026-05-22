<?php
header('Content-Type: application/json');
require '../config/database.php';

session_start();

$input = json_decode(file_get_contents('php://input'), true);
$user_otp = trim($input['otp'] ?? '');

if (empty($user_otp)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'OTP required']);
    exit;
}

// **GET TEMP DATA FROM SESSION**
if (!isset($_SESSION['register_temp']) || empty($_SESSION['register_temp'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No registration in progress']);
    exit;
}

$temp = $_SESSION['register_temp'];

// **CHECK OTP MATCH & EXPIRY**
if (time() > $temp['otp_expires']) {
    unset($_SESSION['register_temp']);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'OTP expired']);
    exit;
}

if ($temp['otp'] !== $user_otp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
    exit;
}

// **OTP MATCHED → SAVE TO DATABASE**
$stmt = $pdo->prepare("
    INSERT INTO users (name, email, phone, password, is_verified, role) 
    VALUES (?, ?, ?, ?, 1, 'customer')
");
if ($stmt->execute([
    $temp['name'], 
    $temp['email'], 
    $temp['phone'], 
    $temp['password']
])) {
    // **CLEAR SESSION** - Registration complete
    unset($_SESSION['register_temp']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! You can now login.',
        'user_id' => $pdo->lastInsertId()
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
?>
