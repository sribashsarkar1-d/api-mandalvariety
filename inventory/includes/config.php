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
define('DB_USER', 'u391326945_mandalvr');
define('DB_PASS', 'Mandal@1234567890');

/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
*/
define('INVENTORY_BASE_URL', '/'); // Using root because it's a subdomain

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/
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
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars(isset($string) ? $string : '', ENT_QUOTES, 'UTF-8');
    }
}
?>
