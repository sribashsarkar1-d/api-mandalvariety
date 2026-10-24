<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u391326945_mandal');
define('DB_USER', 'u391326945_mandal');
define('DB_PASS', 'Sribash123');

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
    try {
        $conn = new PDO(
            "mysql:host=localhost;dbname=mondal-vr;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e2) {
        die("Database connection failed. Check config.php settings.");
    }
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

// Helper to get product image URL
if (!function_exists('getDeliveryProductImageUrl')) {
    function getDeliveryProductImageUrl($imagesRaw) {
        if (empty($imagesRaw)) {
            return '';
        }
        $imgName = '';
        if (is_string($imagesRaw)) {
            $decoded = @json_decode($imagesRaw, true);
            if (is_array($decoded) && !empty($decoded)) {
                $imgName = trim((string)$decoded[0]);
            } else {
                $parts = explode(',', $imagesRaw);
                $imgName = trim((string)$parts[0]);
            }
        } elseif (is_array($imagesRaw) && !empty($imagesRaw)) {
            $imgName = trim((string)$imagesRaw[0]);
        }

        $imgName = trim($imgName, "[]\"' ");
        if (empty($imgName)) {
            return '';
        }

        if (filter_var($imgName, FILTER_VALIDATE_URL) || strpos($imgName, 'http://') === 0 || strpos($imgName, 'https://') === 0) {
            return $imgName;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            return '/auth-api/admin/uploads/' . ltrim($imgName, '/');
        }

        // Live production URL
        return 'https://mandal-variety.com/admin/uploads/' . ltrim($imgName, '/');
    }
}
?>
