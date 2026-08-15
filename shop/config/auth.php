<?php
// config/auth.php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

function require_admin() {
    require_login();
    if (get_user_role() !== 'admin') {
        // Redirect unauthorized users to employee dashboard
        header("Location: " . BASE_URL . "/employee/dashboard.php");
        exit();
    }
}

function require_employee() {
    require_login();
    // Admin can also access employee stuff or you can restrict it
    if (get_user_role() !== 'employee' && get_user_role() !== 'admin') {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}
?>
