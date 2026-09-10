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
    // 1. Fetch active parent categories
    $stmtParents = $pdo->query("SELECT id, name, slug, description, image, sort_order FROM parent_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $parents = $stmtParents->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch active child categories
    $stmtChildren = $pdo->query("SELECT id, parent_category_id, name, slug, description, image, sort_order FROM child_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    $allChildren = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

    // Group children by parent_id
    $childrenByParent = [];
    foreach ($allChildren as $child) {
        $childrenByParent[$child['parent_category_id']][] = $child;
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

    // Assemble the hierarchy
    foreach ($parents as &$parent) {
        // Format parent image
        if (!empty($parent['image'])) {
            $parent['image'] = (filter_var($parent['image'], FILTER_VALIDATE_URL) || strpos($parent['image'], 'http') === 0) 
                ? $parent['image'] 
                : $uploads_url . ltrim($parent['image'], '/');
        } else {
            $parent['image'] = null;
        }

        // Attach children
        $children = $childrenByParent[$parent['id']] ?? [];
        
        // Format children images
        foreach ($children as &$child) {
            if (!empty($child['image'])) {
                $child['image'] = (filter_var($child['image'], FILTER_VALIDATE_URL) || strpos($child['image'], 'http') === 0) 
                    ? $child['image'] 
                    : $uploads_url . ltrim($child['image'], '/');
            } else {
                $child['image'] = null;
            }
        }
        
        $parent['children'] = $children;
    }

    echo json_encode(['success' => true, 'data' => $parents]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>
