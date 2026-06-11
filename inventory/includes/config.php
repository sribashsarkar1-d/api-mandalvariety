<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/
define('DB_HOST', 'localhost');
define('DB_NAME', 'u391326945_mandalvariety');
define('DB_USER', 'root'); // Using root locally for setup/execution if password fails, but let's use the one from admin config if needed. Wait, in admin config it's u391326945_mandalvr. For local, let's use root with no password just like admin maybe?
// Wait, local admin config has DB_USER=u391326945_mandalvr, DB_PASS=Mandal@1234567890. I will copy that exact config.
define('DB_USER_VAL', 'u391326945_mandalvr');
define('DB_PASS_VAL', 'Mandal@1234567890');

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/
try {
    // Attempt local root connection first
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        // Fallback to configured connection
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER_VAL, DB_PASS_VAL);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        die("Database connection failed.");
    }
}

// Base URL for the inventory module
define('INVENTORY_BASE_URL', '/auth-api/inventory/');

// Helper for escaping
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
