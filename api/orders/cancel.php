<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$user_id = (int)($_GET['user_id'] ?? 1);
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID required in URL: /orders/cancel/1']);
    exit;
}

global $pdo;
$stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => "Order $order_id not found"]);
    exit;
}

if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Can only cancel pending orders']);
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$order_id]);

echo json_encode([
    'success' => true,
    'message' => "Order #$order_id cancelled successfully ✅"
]);
?>
