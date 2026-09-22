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
    $sql = "SELECT id, image, image_url, title, status, created_at, updated_at 
            FROM appbackimage 
            WHERE status = 1 
            ORDER BY id DESC LIMIT 1";
            
    $stmt = $pdo->query($sql);
    $bgImage = $stmt->fetch(PDO::FETCH_ASSOC);

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

    if ($bgImage) {
        if (!empty($bgImage['image'])) {
            if (filter_var($bgImage['image'], FILTER_VALIDATE_URL) || strpos($bgImage['image'], 'http') === 0) {
                $bgImage['image_full_url'] = $bgImage['image'];
            } else {
                $image_path = ltrim($bgImage['image'], '/');
                if (strpos($image_path, 'appbackimage/') !== 0) {
                    $image_path = 'appbackimage/' . $image_path;
                }
                $bgImage['image_full_url'] = $uploads_url . $image_path;
            }
        } else {
            $bgImage['image_full_url'] = null;
        }
    }

    echo json_encode(['success' => true, 'data' => $bgImage]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
