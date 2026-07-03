<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$user_id = (int)($_GET['user_id'] ?? 1);
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

// Get order details + items
$stmt = $pdo->prepare("
    SELECT o.*, u.name as delivery_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Explicitly map order_status for Flutter
if ($order) {
    $order['order_status'] = $order['status'] ?? 'pending';
}

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.images 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$baseUrl = 'https://mandal-variety.com/admin/uploads/';
foreach ($items as &$item) {
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
}
unset($item);

$order['items'] = $items;
echo json_encode(['success' => true, 'data' => $order], JSON_PRETTY_PRINT);
?>
