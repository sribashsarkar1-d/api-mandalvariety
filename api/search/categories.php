<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$raw_q = $_GET['q'] ?? '';
$q = $service->normalize($raw_q);
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));

$categories = $service->searchCategories($raw_q, $limit);
$service->logSearch($raw_q, $q, count($categories), 'categories', $_GET['user_id'] ?? null);

responseJson([
    'success' => true,
    'data' => [
        'categories' => $categories
    ]
]);
