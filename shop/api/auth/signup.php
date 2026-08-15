<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$otp = sanitize_input($_POST['otp'] ?? '');

if (empty($otp)) {
    json_response(false, 'OTP is required');
}

if (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_data'])) {
    json_response(false, 'Session expired or invalid request. Please sign up again.');
}

if ($otp !== $_SESSION['signup_otp']) {
    json_response(false, 'Invalid OTP');
}

$data = $_SESSION['signup_data'];
$name = $data['name'];
$email = $data['email'];
$password = $data['password'];
$role = $data['role'];

try {
    // Check if email exists (again, just in case)
    $stmt = $pdo->prepare("SELECT id FROM employee_users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        json_response(false, 'Email is already registered');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO employee_users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashed_password, $role]);
    
    // Add notification
    try {
        $notifTitle = "New Registration";
        $notifMsg = "New $role registered: $name";
        $pdo->prepare("INSERT INTO employee_notifications (title, message, type) VALUES (?, ?, 'register')")->execute([$notifTitle, $notifMsg]);
    } catch(PDOException $e) {
        // Ignore notification failure
    }
    
    // Clear session
    unset($_SESSION['signup_data']);
    unset($_SESSION['signup_otp']);
    
    json_response(true, 'Account created successfully. You can now login.');
} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
