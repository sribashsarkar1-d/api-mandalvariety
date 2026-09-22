<?php
require_once __DIR__ . '/api/config/database.php';
try {
    $sql = "CREATE TABLE IF NOT EXISTS appbackimage (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255) NOT NULL,
        image_url VARCHAR(500) DEFAULT NULL,
        title VARCHAR(150) DEFAULT NULL,
        status TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table appbackimage created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
