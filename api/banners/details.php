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
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing id parameter']);
        exit;
    }

    // Select active banner that is currently within its schedule
    $sql = "SELECT id, title, subtitle, description, image, button_text, button_link, sort_order 
            FROM home_banners 
            WHERE id = ? AND is_active = 1 
            AND (start_date IS NULL OR start_date <= NOW()) 
            AND (end_date IS NULL OR end_date >= NOW())";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Banner not found, inactive, or expired']);
        exit;
    }

    // Prepare uploads URL for images
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    if (strpos($host, 'api.mandal-variety.com') !== false) {
        $uploads_url = "https://mandal-variety.com/admin/uploads/";
    } else {
        $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $project_path = rtrim(preg_replace('/\/api\/.*$/i', '', $script_path), '/');
        $uploads_url = $protocol . "://" . $host . $project_path . "/admin/uploads/";
    }

    if (!empty($banner['image'])) {
        $banner['image'] = (filter_var($banner['image'], FILTER_VALIDATE_URL) || strpos($banner['image'], 'http') === 0) 
            ? $banner['image'] 
            : $uploads_url . ltrim($banner['image'], '/');
    } else {
        $banner['image'] = null;
    }

    echo json_encode(['success' => true, 'data' => $banner]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
