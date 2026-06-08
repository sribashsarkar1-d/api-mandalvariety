<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u391326945_mandalvariety');
define('DB_USER', 'u391326945_mandalvr');
define('DB_PASS', 'Mandal@1234567890');

// Database Connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Check config.php settings.");
}

// Security & Helper Functions
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Helper function to check if delivery boy is logged in
if (!function_exists('checkDeliveryLogin')) {
    function checkDeliveryLogin() {
        if (!isset($_SESSION['delivery_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}
?>
