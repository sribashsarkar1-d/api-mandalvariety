<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

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
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO employee_users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashed_password, $role]);
    
    json_response(true, 'Account created successfully. You can now login.');
} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
