<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$user_id = (int)($_GET['user_id'] ?? 1);

// Get user orders
$stmt = $pdo->prepare("
    SELECT id, order_number, grand_total, status, payment_status, 
           created_at, delivery_address 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 20
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => [
        'user_id' => $user_id,
        'orders' => $orders,
        'total_orders' => count($orders)
    ]
], JSON_PRETTY_PRINT);
?>
