<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Safely determine project base path
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $project_path = rtrim(preg_replace('/\/api\/.*$/i', '', $script_path), '/');
    $uploads_url = $protocol . "://" . $host . $project_path . "/admin/uploads/";

    foreach ($categories as &$category) {
        $category['id'] = (int)$category['id'];
        $category['is_active'] = (int)($category['is_active'] ?? 1);
        
        $img = trim($category['image'] ?? '');
        if (!empty($img)) {
            if (filter_var($img, FILTER_VALIDATE_URL) || strpos($img, 'http') === 0) {
                $category['image'] = $img;
            } else {
                $category['image'] = $uploads_url . ltrim($img, '/');
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $categories]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
