<?php
include '../includes/header.php';

if (!function_exists('productImages')) {
    function productImages($images)
    {
        if (empty($images)) return [];
        $images = trim($images);

        $json = json_decode($images, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return array_values(array_filter(array_map('trim', $json)));
        }

        return array_values(array_filter(array_map('trim', explode(',', $images))));
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT images FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $images = productImages($product['images'] ?? '');

        foreach ($images as $img) {
            $filePath = __DIR__ . '/../uploads/' . $img;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $conn->beginTransaction();

        try {
            $conn->prepare("DELETE FROM offers WHERE product_id = ?")->execute([$id]);
            $conn->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $conn->commit();

            echo "deleted";
        } catch (Exception $e) {
            $conn->rollBack();
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