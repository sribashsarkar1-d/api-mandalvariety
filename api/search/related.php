<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$related = $service->getRelatedProductsByCategoryFallback(10);

responseJson([
    'success' => true,
    'data' => [
        'related' => $related
    ]
]);
