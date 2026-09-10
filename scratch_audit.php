<?php
try {
    // Try to connect to mondal-vr or u391326945_mandal
    $dbName = 'mondal-vr';
    $conn = new PDO("mysql:host=localhost;dbname=$dbName;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $report = [];

    // 1. Current products table structure
    $stmt = $conn->query("DESCRIBE products");
    $report['A_schema'] = $stmt->fetchAll();

    // 2. Current products.category_id values and product counts
    $stmt = $conn->query("SELECT category_id, COUNT(*) as count FROM products GROUP BY category_id");
    $report['B_distribution'] = $stmt->fetchAll();

    // 3. Current foreign keys related to products.category_id
    $stmt = $conn->prepare("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_SCHEMA = ? AND TABLE_NAME = 'products'");
    $stmt->execute([$dbName]);
    $report['C_foreign_keys'] = $stmt->fetchAll();

    // 4. Current parent_categories and child_categories
    try {
        $stmt = $conn->query("SELECT * FROM parent_categories");
        $report['D1_parent_categories'] = $stmt->fetchAll();
    } catch(Exception $e) {
        $report['D1_parent_categories'] = "Table not found";
    }

    try {
        $stmt = $conn->query("SELECT * FROM child_categories");
        $report['D2_child_categories'] = $stmt->fetchAll();
    } catch(Exception $e) {
        $report['D2_child_categories'] = "Table not found";
    }
    
    // Check if subcategory_id exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM products LIKE 'subcategory_id'");
    $stmt->execute();
    $report['E_subcategory_id_exists'] = $stmt->fetch() ? "Yes" : "No";

    echo json_encode($report, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
