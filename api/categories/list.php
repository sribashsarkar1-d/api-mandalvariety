<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("SELECT id, name, slug, description, image, is_active FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (strpos($host, 'api.mandal-variety.com') !== false) {
    $uploads_url = "https://mandal-variety.com/admin/uploads/";
} else {
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $project_path = rtrim(preg_replace('/\/api\/.*$/i', '', $script_path), '/');
    $uploads_url = $protocol . "://" . $host . $project_path . "/admin/uploads/";
}

foreach ($categories as &$category) {
    if (!empty($category['image'])) {
        if (filter_var($category['image'], FILTER_VALIDATE_URL) || strpos($category['image'], 'http') === 0) {
            // Keep as is
        } else {
            $image_path = ltrim($category['image'], '/');
            if (strpos($image_path, 'categories/') !== 0) {
                $image_path = 'categories/' . $image_path;
            }
            $category['image'] = $uploads_url . $image_path;
        }
    } else {
        $category['image'] = null;
    }
}

echo json_encode(['success' => true, 'data' => $categories]);
?>
