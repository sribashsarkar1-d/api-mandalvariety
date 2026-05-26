<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php'; 

try {
    $category_id = $_GET['category_id'] ?? null;
    $where = $category_id ? "WHERE p.category_id = ? AND p.is_active = 1" : "WHERE p.is_active = 1";
    $sql = "SELECT p.id, p.name, p.price, p.images, c.name as category_name 
            FROM products p LEFT JOIN categories c ON p.category_id = c.id $where";
    
    $stmt = $pdo->prepare($sql);
    if ($category_id) $stmt->execute([$category_id]);
    else $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // $_SERVER['SCRIPT_NAME'] is something like /api/products/list.php
    $base_dir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')); // gives /api
    $base_dir = rtrim(str_replace('\\', '/', $base_dir), '/');
    $uploads_url = $protocol . "://" . $host . $base_dir . "/uploads/";

    foreach ($products as &$product) {
        $images = json_decode($product['images'] ?? '[]', true);
        if (!is_array($images)) $images = [];
        
        $full_images = [];
        foreach ($images as $img) {
            $img = trim($img);
            if (empty($img)) continue;
            // If it's already a full URL, keep it. Otherwise prepend uploads_url
            if (filter_var($img, FILTER_VALIDATE_URL) || strpos($img, 'http') === 0) {
                $full_images[] = $img;
            } else {
                $full_images[] = $uploads_url . ltrim($img, '/');
            }
        }
        $product['images'] = $full_images;
    }

    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error']);
}
?>
