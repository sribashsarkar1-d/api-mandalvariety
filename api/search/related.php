<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$related = $service->getRelatedProductsByCategoryFallback(10);

echo json_encode([
    'success' => true,
    'data' => [
        'related' => $related
    ]
], JSON_UNESCAPED_UNICODE);
exit;
