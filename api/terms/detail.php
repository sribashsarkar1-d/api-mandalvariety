<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = trim($_GET['slug'] ?? '');

if ($id <= 0 && $slug === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide either id or slug.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM terms_and_conditions WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM terms_and_conditions WHERE slug = ?");
        $stmt->execute([$slug]);
    }

    $term = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$term) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Terms & Conditions section not found.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $term['id'] = (int)$term['id'];
    $term['section_order'] = (int)$term['section_order'];
    $term['is_active'] = (int)$term['is_active'];

    echo json_encode([
        'success' => true,
        'data' => $term
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>