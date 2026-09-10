<?php
require '../includes/config.php';

echo "<pre>";
echo "Starting Category Migration...\n";

try {
    // 1. Create tables
    $conn->exec("
        CREATE TABLE IF NOT EXISTS `parent_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `slug` VARCHAR(180) NOT NULL UNIQUE,
            `description` TEXT NULL,
            `image` VARCHAR(255) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Checked/Created parent_categories table.\n";

    $conn->exec("
        CREATE TABLE IF NOT EXISTS `child_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `parent_category_id` INT NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `slug` VARCHAR(180) NOT NULL UNIQUE,
            `description` TEXT NULL,
            `image` VARCHAR(255) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_child_parent` 
                FOREIGN KEY (`parent_category_id`) 
                REFERENCES `parent_categories`(`id`) 
                ON DELETE RESTRICT 
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Checked/Created child_categories table.\n";

    // 2. Fetch existing categories
    $stmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
    $oldCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($oldCategories) . " categories in the old table.\n";

    // Known subcategories (Add mappings here if needed)
    // For automatic mapping, we define arrays. E.g. $childMap['skincare'] = 'beauty-personal-care';
    $childMap = [
        'skincare' => 'beauty-personal-care',
        'face-care' => 'beauty-personal-care',
        'lip-care' => 'beauty-personal-care',
        'bath-body' => 'beauty-personal-care',
        'deodorant' => 'beauty-personal-care',
        'mens-grooming' => 'beauty-personal-care',
        'feminine-care' => 'beauty-personal-care',
        'atta-rice-dal' => 'grocery',
        'cooking-oil-ghee' => 'grocery',
        'masala-spices' => 'grocery',
        'biscuits-snacks' => 'grocery',
        'beverages' => 'grocery',
        'dairy-eggs' => 'grocery',
    ];

    $parentStmt = $conn->prepare("INSERT INTO parent_categories (name, slug, description, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $childStmt = $conn->prepare("INSERT INTO child_categories (parent_category_id, name, slug, description, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $checkParentStmt = $conn->prepare("SELECT id FROM parent_categories WHERE slug = ?");
    $checkChildStmt = $conn->prepare("SELECT id FROM child_categories WHERE slug = ?");

    // First pass: Insert parent categories
    foreach ($oldCategories as $cat) {
        $slug = strtolower(trim($cat['slug']));
        
        // Check if it's explicitly a child based on our map
        $isChild = false;
        foreach ($childMap as $childSlugPattern => $parentSlugPattern) {
            if (strpos($slug, $childSlugPattern) !== false) {
                $isChild = true;
                break;
            }
        }

        if (!$isChild) {
            $checkParentStmt->execute([$slug]);
            if (!$checkParentStmt->fetch()) {
                $parentStmt->execute([
                    $cat['name'],
                    $cat['slug'],
                    $cat['description'],
                    $cat['image'],
                    $cat['is_active'],
                    $cat['created_at']
                ]);
                echo "Inserted Parent: {$cat['name']}\n";
            } else {
                echo "Skipped Parent (Already exists): {$cat['name']}\n";
            }
        }
    }

    // Second pass: Insert child categories
    foreach ($oldCategories as $cat) {
        $slug = strtolower(trim($cat['slug']));
        
        $targetParentSlug = null;
        foreach ($childMap as $childSlugPattern => $parentSlugPattern) {
            if (strpos($slug, $childSlugPattern) !== false) {
                $targetParentSlug = $parentSlugPattern;
                break;
            }
        }

        if ($targetParentSlug) {
            $checkChildStmt->execute([$slug]);
            if (!$checkChildStmt->fetch()) {
                // Find parent id
                $checkParentStmt->execute([$targetParentSlug]);
                $parent = $checkParentStmt->fetch();
                
                if ($parent) {
                    $childStmt->execute([
                        $parent['id'],
                        $cat['name'],
                        $cat['slug'],
                        $cat['description'],
                        $cat['image'],
                        $cat['is_active'],
                        $cat['created_at']
                    ]);
                    echo "Inserted Child: {$cat['name']} under parent ID {$parent['id']}\n";
                } else {
                    echo "Warning: Could not find parent '$targetParentSlug' for child '{$cat['name']}'. Migrating as parent instead.\n";
                    $checkParentStmt->execute([$slug]);
                    if (!$checkParentStmt->fetch()) {
                        $parentStmt->execute([
                            $cat['name'],
                            $cat['slug'],
                            $cat['description'],
                            $cat['image'],
                            $cat['is_active'],
                            $cat['created_at']
                        ]);
                        echo "Inserted Parent (fallback): {$cat['name']}\n";
                    }
                }
            } else {
                echo "Skipped Child (Already exists): {$cat['name']}\n";
            }
        }
    }

    echo "\nMigration completed successfully.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
