<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'u391326945_mandal');
define('DB_PASS', 'Sribash123');
// Note: Assumes 'employee_pos' is the database name. If not, the user can change it.
define('DB_NAME', 'u391326945_mandal');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect to database. " . $e->getMessage());
}
?>
