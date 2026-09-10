<?php
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=u391326945_mandal;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "SCHEMA:\n";
    $stmt = $conn->query("SHOW CREATE TABLE categories");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Create Table'] . "\n\n";

    echo "DATA:\n";
    $stmt = $conn->query("SELECT * FROM categories LIMIT 10");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
