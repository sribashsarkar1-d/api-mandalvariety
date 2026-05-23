<?php
$conn = new PDO("mysql:host=localhost;dbname=mondal-vr;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    $conn->beginTransaction();

    // Add new columns to products table if they don't exist
    $columnsToAdd = [
        "ADD COLUMN `short_description` text DEFAULT NULL",
        "ADD COLUMN `subcategory_id` int(11) DEFAULT NULL",
        "ADD COLUMN `brand` varchar(255) DEFAULT NULL",
        "ADD COLUMN `barcode` varchar(100) DEFAULT NULL",
        "ADD COLUMN `hsn_or_tax_code` varchar(100) DEFAULT NULL",
        "ADD COLUMN `cost_price` decimal(10,2) DEFAULT NULL",
        "ADD COLUMN `tax_percent` decimal(5,2) DEFAULT 0.00",
        "ADD COLUMN `min_stock_alert` int(11) DEFAULT 5",
        "ADD COLUMN `tags` text DEFAULT NULL",
        "ADD COLUMN `featured_product` tinyint(1) DEFAULT 0",
        "ADD COLUMN `new_arrival` tinyint(1) DEFAULT 0",
        "ADD COLUMN `seo_meta_title` varchar(255) DEFAULT NULL",
        "ADD COLUMN `seo_meta_description` text DEFAULT NULL",
        "ADD COLUMN `seo_keywords` text DEFAULT NULL",
        "ADD COLUMN `sort_order` int(11) DEFAULT 0",
        "ADD COLUMN `status` enum('draft','published','inactive') DEFAULT 'published'",
        "ADD COLUMN `meta_data` longtext DEFAULT NULL" // JSON
    ];

    foreach ($columnsToAdd as $colDef) {
        try {
            $conn->exec("ALTER TABLE `products` " . $colDef);
            echo "Executed: " . $colDef . "\n";
        } catch (PDOException $e) {
            // Ignore if column already exists (Error 1060)
            if ($e->getCode() == '42S21' || strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "Column already exists, skipping.\n";
            } else {
                throw $e;
            }
        }
    }

    // Create product_variants table
    $createVariants = "CREATE TABLE IF NOT EXISTS `product_variants` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `variant_name` varchar(255) NOT NULL,
      `variant_sku` varchar(100) DEFAULT NULL,
      `variant_barcode` varchar(100) DEFAULT NULL,
      `price` decimal(10,2) NOT NULL,
      `discount_price` decimal(10,2) DEFAULT NULL,
      `stock_quantity` int(11) DEFAULT 0,
      `image` varchar(255) DEFAULT NULL,
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $conn->exec($createVariants);
    echo "Product variants table created/verified.\n";

    $conn->commit();
    echo "Database upgrade completed successfully.\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
