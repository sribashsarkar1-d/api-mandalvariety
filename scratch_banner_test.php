<?php
require_once 'c:/xampp/htdocs/auth-api/api/config/database.php';
$stmt = $pdo->query('SELECT * FROM home_banners');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
