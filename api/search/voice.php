<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);
$data = json_decode(file_get_contents("php://input"), true);
$query = $data['query'] ?? '';

if (empty($query)) {
    apiError('Voice query is empty', 400);
}

// Redirect logic to global search by setting $_GET and requiring global.php
$_GET['q'] = $query;
require_once __DIR__ . '/global.php';
