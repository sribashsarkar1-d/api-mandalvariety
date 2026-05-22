<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$raw_q = $_GET['q'] ?? '';
$q = $service->normalize($raw_q);

if (strlen($q) < 2) {
    responseJson([
        'success' => true,
        'data' => [
            'products' => [],
            'categories' => [],
            'keywords' => []
        ]
    ]);
}

$products = $service->globalSearch($raw_q, 5, 0, [], 'relevance');
$categories = $service->searchCategories($raw_q, 3);

$p_names = array_column($products, 'name');
$c_names = array_column($categories, 'name');

$keywords = [];
if (!empty($p_names)) {
    $keywords[] = strtolower($p_names[0]);
}

responseJson([
    'success' => true,
    'data' => [
        'products' => $p_names,
        'categories' => $c_names,
        'keywords' => $keywords
    ]
]);
