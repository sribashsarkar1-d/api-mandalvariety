<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=u391326945_mandalvariety;charset=utf8mb4", "root", "");
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
