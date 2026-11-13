<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/store_hours_helper.php';

try {
    $status = getStoreOperatingStatus($pdo);
    echo json_encode([
        'success' => true,
        'message' => 'Store operating status fetched successfully',
        'data' => $status
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch store operating status',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
