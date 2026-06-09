<?php
$conn = new PDO("mysql:host=localhost;dbname=mondal-vr;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $conn->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL");
    $conn->exec("ALTER TABLE users ADD COLUMN reset_token_expires_at DATETIME NULL");
    echo "Columns added successfully.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Column already exists
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
