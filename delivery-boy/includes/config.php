<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the main admin config for DB connection and settings
require_once __DIR__ . '/../../admin/includes/config.php';

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
