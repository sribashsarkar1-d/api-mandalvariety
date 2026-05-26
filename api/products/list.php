<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php'; 

try {
    $category_id = $_GET['category_id'] ?? null;
    $where = $category_id ? "WHERE p.category_id = ? AND p.is_active = 1" : "WHERE p.is_active = 1";
    $sql = "SELECT p.id, 
                   p.name, 
                   p.slug,
                   p.price, 
                   p.discount_price,
                   p.stock_quantity,
                   p.images, 
                   c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id $where";
    
    $stmt = $pdo->prepare($sql);
    if ($category_id) $stmt->execute([$category_id]);
    else $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Correctly determine the project's base path from the script's location
    // e.g. from /auth-api/api/products/list.php to /auth-api
    $project_path = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')));
    $project_path = rtrim(str_replace('\\', '/', $project_path), '/');
    $uploads_url = $protocol . "://" . $host . $project_path . "/admin/uploads/";

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
    // It's better to return a proper error status code and a more descriptive message for debugging.
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
