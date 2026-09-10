<?php
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=mondal-vr;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "--- categories (legacy) ---\n";
    try {
        $stmt = $conn->query("SELECT * FROM categories LIMIT 5");
        print_r($stmt->fetchAll());
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    echo "\n--- parent_categories ---\n";
    try {
        $stmt = $conn->query("SELECT * FROM parent_categories LIMIT 5");
        print_r($stmt->fetchAll());
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    echo "\n--- child_categories ---\n";
    try {
        $stmt = $conn->query("SELECT * FROM child_categories LIMIT 5");
        print_r($stmt->fetchAll());
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    echo "\n--- products categories data ---\n";
    try {
        $stmt = $conn->query("SELECT id, name, category_id, subcategory_id FROM products LIMIT 10");
        print_r($stmt->fetchAll());
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    echo "\n--- Foreign Keys for products table ---\n";
    try {
        $stmt = $conn->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = 'mondal-vr' AND TABLE_NAME = 'products'");
        print_r($stmt->fetchAll());
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
