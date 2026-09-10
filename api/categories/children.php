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
    $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;

    if ($parent_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing parent_id parameter']);
        exit;
    }

    // Verify parent exists
    $stmtParent = $pdo->prepare("SELECT id FROM parent_categories WHERE id = ? AND is_active = 1");
    $stmtParent->execute([$parent_id]);
    if (!$stmtParent->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Parent category not found or is inactive']);
        exit;
    }

    // Fetch active children for this parent
    $stmtChildren = $pdo->prepare("SELECT id, parent_category_id, name, slug, description, image, sort_order FROM child_categories WHERE parent_category_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC");
    $stmtChildren->execute([$parent_id]);
    $children = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

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

    foreach ($children as &$child) {
        if (!empty($child['image'])) {
            $child['image'] = (filter_var($child['image'], FILTER_VALIDATE_URL) || strpos($child['image'], 'http') === 0) 
                ? $child['image'] 
                : $uploads_url . ltrim($child['image'], '/');
        } else {
            $child['image'] = null;
        }
    }

    echo json_encode(['success' => true, 'data' => $children]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
