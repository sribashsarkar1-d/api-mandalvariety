<?php
require 'config/database.php';
$sql = "CREATE TABLE IF NOT EXISTS employee_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-bell',
    type ENUM('sale', 'login', 'register', 'system') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";
try {
    $pdo->exec($sql);
    echo "Table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
