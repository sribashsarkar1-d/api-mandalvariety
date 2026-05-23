<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$raw_q = $_GET['q'] ?? '';
$q = $service->normalize($raw_q);

$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$filters = [
    'category_id' => $_GET['category_id'] ?? null,
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
    'in_stock' => $_GET['in_stock'] ?? null,
];
$sort = $_GET['sort'] ?? 'relevance';

$products = $service->globalSearch($raw_q, $limit, $offset, $filters, $sort);
$service->logSearch($raw_q, $q, count($products), 'products', $_GET['user_id'] ?? null);

responseJson([
    'success' => true,
    'data' => [
        'products' => $products
    ]
]);
