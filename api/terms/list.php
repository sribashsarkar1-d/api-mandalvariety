<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $where = " WHERE 1 ";
    $params = [];

    // By default, return published & active terms unless admin or explicit filter
    if (isset($_GET['all']) && $_GET['all'] == '1') {
        // Return all
    } else {
        $where .= " AND status = 'published' AND is_active = 1 ";
    }

    if (isset($_GET['status']) && in_array($_GET['status'], ['published', 'draft', 'archived'])) {
        $where .= " AND status = ? ";
        $params[] = $_GET['status'];
    }

    $sql = "SELECT id, title, slug, content, section_order, status, is_active, created_at, updated_at 
            FROM terms_and_conditions 
            {$where} 
            ORDER BY section_order ASC, id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cast types
    foreach ($terms as &$t) {
        $t['id'] = (int)$t['id'];
        $t['section_order'] = (int)$t['section_order'];
        $t['is_active'] = (int)$t['is_active'];
    }
    unset($t);

    echo json_encode([
        'success' => true,
        'count' => count($terms),
        'data' => $terms
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>