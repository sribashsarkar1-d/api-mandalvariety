<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(false, 'Email and password are required');
}

try {
    $stmt = $pdo->prepare("SELECT id, name, password, role, status FROM employee_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            json_response(false, 'Your account is inactive. Please contact admin.');
        }
        
        // Update last login
        $pdo->prepare("UPDATE employee_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Add notification
        try {
            $notifTitle = "User Login";
            $notifMsg = $user['name'] . " logged in.";
            $pdo->prepare("INSERT INTO employee_notifications (title, message, type) VALUES (?, ?, 'login')")->execute([$notifTitle, $notifMsg]);
        } catch(PDOException $e) {
            // Ignore notification failure
        }
        
        $redirect = ($user['role'] === 'admin') ? BASE_URL . '/admin/dashboard.php' : BASE_URL . '/employee/dashboard.php';
        
        json_response(true, 'Login successful', ['redirect' => $redirect]);
    } else {
        json_response(false, 'Invalid email or password');
    }
} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
