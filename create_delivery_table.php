<?php
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=mondal-vr;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $sql = "
    CREATE TABLE IF NOT EXISTS delivery_boys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        phone VARCHAR(30) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        vehicle_type VARCHAR(50),
        vehicle_number VARCHAR(50),
        is_active TINYINT(1) DEFAULT 1,
        is_available TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );
    ";

    $conn->exec($sql);
    echo "Table delivery_boys created successfully in mondal-vr.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
