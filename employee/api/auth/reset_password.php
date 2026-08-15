<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$email = sanitize_input($_POST['email'] ?? '');
$otp = sanitize_input($_POST['otp'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($otp) || empty($password)) {
    json_response(false, 'All fields are required');
}

try {
    // Check if email exists and OTP is valid
    $stmt = $pdo->prepare("SELECT id, reset_otp, reset_otp_expiry FROM employee_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(false, 'Invalid request.');
    }
    
    if ($user['reset_otp'] !== $otp) {
        json_response(false, 'Invalid OTP.');
    }
    
    if (strtotime($user['reset_otp_expiry']) < time()) {
        json_response(false, 'OTP has expired. Please request a new one.');
    }
    
    // Hash new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update DB
    $stmt = $pdo->prepare("UPDATE employee_users SET password = ?, reset_otp = NULL, reset_otp_expiry = NULL WHERE id = ?");
    $stmt->execute([$hashed_password, $user['id']]);
    
    json_response(true, 'Password has been reset successfully. You can now log in.');

} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
