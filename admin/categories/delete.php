<?php
include '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT image FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        if (!empty($category['image'])) {
            $filePath = __DIR__ . '/../uploads/' . $category['image'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        try {
            // Optional: Handle products that have this category id by setting category_id to NULL or handling it
            // Assuming ON DELETE CASCADE is NOT set, you might want to reset products category_id.
            $conn->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$id]);
            
            $conn->prepare("DELETE FROM offers WHERE category_id = ?")->execute([$id]);
            $conn->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            
            echo "deleted";
        } catch (Exception $e) {
            http_response_code(500);
            echo "error";
        }
    } else {
        http_response_code(404);
        echo "not found";
    }
} else {
    http_response_code(400);
    echo "invalid";
}
