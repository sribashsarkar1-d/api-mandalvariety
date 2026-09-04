<?php
require 'c:/xampp/htdocs/auth-api/api/config/database.php';
$stmt = $pdo->query("SHOW COLUMNS FROM orders");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
