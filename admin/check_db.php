<?php
try {
    $conn = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "");
    $stmt = $conn->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($dbs);
} catch (Exception $e) {
    echo $e->getMessage();
}
