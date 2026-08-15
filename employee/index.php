<?php
require_once 'config/constants.php';
require_once 'config/auth.php';

if (is_logged_in()) {
    if (get_user_role() === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: employee/dashboard.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
