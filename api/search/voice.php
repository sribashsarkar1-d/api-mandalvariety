<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);
$data = json_decode(file_get_contents("php://input"), true);
$query = $data['query'] ?? '';

if (empty($query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Voice query is empty'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Redirect logic to global search by setting $_GET and requiring global.php
$_GET['q'] = $query;
require_once __DIR__ . '/global.php';
