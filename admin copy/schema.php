<?php
$conn = new PDO("mysql:host=localhost;dbname=mondal-vr;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $conn->query('DESCRIBE categories');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
