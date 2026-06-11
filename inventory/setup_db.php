<?php
// Local Setup script to create tables in `mondal-vr` since `u391326945_mandalvariety` is not available locally.
try {
    $conn = new PDO("mysql:host=localhost;dbname=mondal-vr;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create inventory_users table
    $sql1 = "CREATE TABLE IF NOT EXISTS inventory_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql1);
    echo "Table inventory_users created successfully.\n";

    // Insert a default admin user (password: 123456)
    $password_hashed = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT IGNORE INTO inventory_users (username, password) VALUES ('admin', ?)");
    $stmt->execute([$password_hashed]);
    echo "Default user 'admin' (pass: 123456) ensured.\n";

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
    echo "Table inventory_purchases created successfully.\n";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
