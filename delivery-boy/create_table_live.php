<?php
require_once __DIR__ . '/includes/config.php';

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

try {
    $conn->exec($sql);
    echo "<h3>Success!</h3>";
    echo "<p>The 'delivery_boys' table was successfully created on your live server.</p>";
    echo "<a href='signup.php'>Go to Signup Page</a>";
} catch (PDOException $e) {
    echo "<h3>Error</h3>";
    echo "<p>Failed to create table: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
