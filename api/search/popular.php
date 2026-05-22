<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

$service = new SearchService($pdo);

$popular = $service->getPopularSearches();
$popular_keywords = array_column($popular, 'normalized_query');

responseJson([
    'success' => true,
    'data' => [
        'popular_keywords' => $popular_keywords
    ]
]);
