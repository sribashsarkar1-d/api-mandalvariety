<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=u391326945_mandalvariety;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    try {
        $conn = new PDO("mysql:host=localhost;dbname=mandalvariety;charset=utf8mb4", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    } catch (PDOException $e2) {
        die("DB Connection failed: " . $e2->getMessage());
    }
}

try {
    $conn->exec('ALTER TABLE products ADD COLUMN attributes LONGTEXT DEFAULT NULL');
    echo "Added attributes column.\n";
} catch (Exception $e) {
    echo "Attributes column issue: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("INSERT IGNORE INTO categories (name, slug, description) VALUES ('Shoes', 'shoes', 'Footwear and shoes'), ('Grocery', 'grocery', 'Daily groceries and snacks')");
    echo "Categories added.\n";
} catch (Exception $e) {
    echo "Category issue: " . $e->getMessage() . "\n";
}
?>
