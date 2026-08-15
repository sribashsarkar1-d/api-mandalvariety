<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';
require_once '../../includes/mailer.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$name = sanitize_input($_POST['name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = sanitize_input($_POST['role'] ?? 'employee');

if (empty($name) || empty($email) || empty($password)) {
    json_response(false, 'All fields are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Invalid email format');
}

if (!in_array($role, ['admin', 'employee'])) {
    $role = 'employee';
}

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM employee_users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        json_response(false, 'Email is already registered');
    }
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    
    // Store in session
    $_SESSION['signup_data'] = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => $role
    ];
    $_SESSION['signup_otp'] = $otp;
    
    // Send email
    $subject = "Your Signup OTP";
    $body = "Hello $name,<br><br>Your OTP for signup is: <b>$otp</b>.<br><br>Please do not share it with anyone.";
    
    if (sendMail($email, $subject, $body)) {
        json_response(true, 'OTP sent to your email. Please verify to continue.');
    } else {
        json_response(false, 'Failed to send OTP email. Please try again.');
    }

} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
