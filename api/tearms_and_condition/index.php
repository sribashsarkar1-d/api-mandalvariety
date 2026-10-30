<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $slug = trim($_GET['slug'] ?? '');

    // If specific item requested by ID or slug
    if ($id > 0 || $slug !== '') {
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
                'message' => 'Terms and Conditions section not found.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $term['id'] = (int)$term['id'];
        $term['section_order'] = (int)$term['section_order'];
        $term['is_active'] = (int)$term['is_active'];

        echo json_encode([
            'success' => true,
            'message' => 'Terms and Conditions fetched successfully',
            'data' => $term
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // Otherwise, fetch all active & published terms for Flutter App
    $where = " WHERE 1 ";
    $params = [];

    // By default, only return active and published terms for Flutter app
    if (!isset($_GET['all']) || $_GET['all'] != '1') {
        $where .= " AND status = 'published' AND is_active = 1 ";
    }

    $sql = "SELECT id, title, slug, content, section_order, status, is_active, created_at, updated_at 
            FROM terms_and_conditions 
            {$where} 
            ORDER BY section_order ASC, id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedTextParts = [];
    $formattedHtmlParts = [];

    foreach ($terms as &$t) {
        $t['id'] = (int)$t['id'];
        $t['section_order'] = (int)$t['section_order'];
        $t['is_active'] = (int)$t['is_active'];

        // Build continuous document representations
        $formattedTextParts[] = ($t['section_order'] > 0 ? "{$t['section_order']}. " : "") . "{$t['title']}\n" . $t['content'];
        $formattedHtmlParts[] = "<h3>" . ($t['section_order'] > 0 ? "{$t['section_order']}. " : "") . htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8') . "</h3><p>" . nl2br(htmlspecialchars($t['content'], ENT_QUOTES, 'UTF-8')) . "</p>";
    }
    unset($t);

    echo json_encode([
        'success' => true,
        'message' => 'Terms and conditions fetched successfully',
        'count' => count($terms),
        'data' => $terms,
        'formatted_text' => implode("\n\n", $formattedTextParts),
        'formatted_html' => implode("", $formattedHtmlParts)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
