<?php
require_once 'includes/config.php';

try {
    // Create inventory_users table
    $sql1 = "CREATE TABLE IF NOT EXISTS inventory_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql1);
    echo "Table inventory_users created successfully.<br>";

    // Create inventory_purchases table
    $sql2 = "CREATE TABLE IF NOT EXISTS inventory_purchases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        purchase_price DECIMAL(10, 2) NOT NULL,
        purchase_date DATE NOT NULL,
        expiry_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql2);
    echo "Table inventory_purchases created successfully.<br>";

    echo "<b>All Database updates complete. You can now use the inventory system!</b>";

} catch(PDOException $e) {
    echo "DB Update failed: " . $e->getMessage();
}
?>
