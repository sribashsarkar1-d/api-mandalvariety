<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$email = sanitize_input($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Valid email is required');
}

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id, name FROM employee_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Return success even if email doesn't exist to prevent email enumeration
        json_response(true, 'If your email is registered, you will receive an OTP shortly.');
    }
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Update DB
    $stmt = $pdo->prepare("UPDATE employee_users SET reset_otp = ?, reset_otp_expiry = ? WHERE id = ?");
    $stmt->execute([$otp, $expiry, $user['id']]);
    
    // Send email
    $subject = "Password Reset OTP";
    $body = "Hello {$user['name']},<br><br>Your OTP to reset your password is: <b>$otp</b>.<br>This OTP is valid for 15 minutes.<br><br>If you did not request a password reset, please ignore this email.";
    
    if (sendMail($email, $subject, $body)) {
        json_response(true, 'If your email is registered, you will receive an OTP shortly.');
    } else {
        json_response(false, 'Failed to send OTP email. Please try again later.');
    }

} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
