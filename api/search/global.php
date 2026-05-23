<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SearchService.php';

try {
    $service = new SearchService($pdo);

    $start_time = microtime(true);
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

    $products = [];
    $categories = [];
    $related = [];
    $did_you_mean = null;

    if (!empty($q)) {
        $products = $service->globalSearch($raw_q, $limit, $offset, $filters, $sort);
        $categories = $service->searchCategories($raw_q, 5);

        if (empty($products) && empty($categories)) {
            $did_you_mean = $service->getClosestMatch($q);
            if ($did_you_mean) {
                $related = $service->globalSearch($did_you_mean, 5, 0, [], 'relevance');
            } else {
                $related = $service->getRelatedProductsByCategoryFallback(5);
            }
        }
    } else {
        $products = $service->globalSearch('', $limit, $offset, $filters, $sort);
    }

    $total_results = count($products) + count($categories);
    
    try {
        $service->logSearch($raw_q, $q, $total_results, 'global', $_GET['user_id'] ?? null);
    } catch (Exception $logEx) {
        // Ignore log error
    }

    $time_ms = round((microtime(true) - $start_time) * 1000, 2);

    echo json_encode([
        'success' => true,
        'message' => empty($products) && empty($categories) ? 'No exact match found, showing related results' : 'Search results found',
        'query' => $raw_q,
        'normalized_query' => $q,
        'did_you_mean' => $did_you_mean,
        'data' => [
            'products' => $products,
            'categories' => $categories,
            'suggestions' => [],
            'related' => $related
        ],
        'meta' => [
            'total_products' => count($products),
            'total_categories' => count($categories),
            'search_time_ms' => $time_ms,
            'page' => $page,
            'limit' => $limit
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit;
}
