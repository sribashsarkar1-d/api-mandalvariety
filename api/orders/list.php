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

    // Explicitly add order_status to guarantee Flutter parses it
    foreach ($orders as &$order) {
        $order['order_status'] = $order['status'] ?? 'pending';
    }

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
