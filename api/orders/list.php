<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 1);

    // Get user orders
    $stmt = $pdo->prepare("
        SELECT * 
        FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all items for these orders
    $orderIds = array_column($orders, 'id');
    $itemsByOrder = [];
    
    if (!empty($orderIds)) {
        $in = str_repeat('?,', count($orderIds) - 1) . '?';
        $itemStmt = $pdo->prepare("
            SELECT oi.*, p.name, p.images 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id IN ($in)
        ");
        $itemStmt->execute($orderIds);
        $all_items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        $baseUrl = 'https://mandal-variety.com/admin/uploads/';
        foreach ($all_items as &$item) {
            $parsedImages = [];
            if (!empty($item['images'])) {
                $json = json_decode($item['images'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $parsedImages = array_values(array_filter(array_map('trim', $json)));
                } else {
                    $parsedImages = array_values(array_filter(array_map('trim', explode(',', $item['images']))));
                }
            }
            $item['images'] = array_map(function($img) use ($baseUrl) {
                return $baseUrl . $img;
            }, $parsedImages);
            
            $itemsByOrder[$item['order_id']][] = $item;
        }
        unset($item);
    }

    // Explicitly add order_status to guarantee Flutter parses it
    foreach ($orders as &$order) {
        $order['order_status'] = $order['status'] ?? 'pending';
        $order['items'] = $itemsByOrder[$order['id']] ?? [];
    }
    unset($order);

    echo json_encode([
        'success' => true,
        'data' => [
            'user_id' => $user_id,
            'orders' => $orders,
            'total_orders' => count($orders)
        ]
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => 'DB Error: ' . $e->getMessage()
    ]);
}
?>
