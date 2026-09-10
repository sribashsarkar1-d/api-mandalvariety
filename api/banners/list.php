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
    // Select active banners that are currently within their schedule
    $sql = "SELECT id, title, subtitle, description, image, button_text, button_link, sort_order 
            FROM home_banners 
            WHERE is_active = 1 
            AND (start_date IS NULL OR start_date <= NOW()) 
            AND (end_date IS NULL OR end_date >= NOW()) 
            ORDER BY sort_order ASC, created_at DESC";
            
    $stmt = $pdo->query($sql);
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    foreach ($banners as &$banner) {
        if (!empty($banner['image'])) {
            $banner['image'] = (filter_var($banner['image'], FILTER_VALIDATE_URL) || strpos($banner['image'], 'http') === 0) 
                ? $banner['image'] 
                : $uploads_url . ltrim($banner['image'], '/');
        } else {
            $banner['image'] = null;
        }
    }

    echo json_encode(['success' => true, 'data' => $banners]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
