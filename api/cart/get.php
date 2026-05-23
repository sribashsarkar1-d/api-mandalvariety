<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
$user_id = (int)($_GET['user_id'] ?? 1);

// Same as list.php but for single user
require_once 'list.php';
?>
