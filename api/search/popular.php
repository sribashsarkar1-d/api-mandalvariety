<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$popular = $service->getPopularSearches();
$popular_keywords = array_column($popular, 'normalized_query');

echo json_encode([
    'success' => true,
    'data' => [
        'popular_keywords' => $popular_keywords
    ]
], JSON_UNESCAPED_UNICODE);
exit;
