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

    // Dynamically calculate the base URL (e.g. http://localhost/auth-api/ or https://api.domain.com/)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    $apiPos = strpos($requestUri, '/api/');
    $basePath = $apiPos !== false ? substr($requestUri, 0, $apiPos) : '';
    $baseUrl = rtrim($protocol . '://' . $host . $basePath, '/') . '/';

    foreach ($products as &$product) {
        $images = json_decode($product['images'], true);
        if (is_array($images)) {
            $full_images = array_map(function($img) use ($baseUrl) {
                if (strpos($img, 'http') === 0) return $img; // Already full URL
                return $baseUrl . ltrim($img, '/');
            }, $images);
            $product['images'] = $full_images;
        } else {
            // Fallback if not json
            if (!empty($product['images']) && strpos($product['images'], 'http') !== 0) {
                $product['images'] = [$baseUrl . ltrim($product['images'], '/')];
            } else {
                $product['images'] = [];
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error']);
}
?>
