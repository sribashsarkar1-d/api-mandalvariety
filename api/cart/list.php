<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'user_id is required']);
        exit;
    }

    // 1. Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => [],
                'summary' => [
                    'total_items' => 0,
                    'subtotal' => 0,
                    'total' => 0
                ],
                'has_stock_issues' => false
            ]
        ]);
        exit;
    }

    // 2. Get cart items joined with products
    $stmt = $pdo->prepare("
        SELECT 
            ci.id as cart_item_id, 
            ci.quantity, 
            ci.price_at_purchase,
            p.id as product_id, 
            p.name, 
            p.price as current_price, 
            p.discount_price,
            p.images, 
            p.stock_quantity,
            p.stock,
            p.is_active
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    $total_items = 0;
    $has_stock_issues = false;

    $baseUrl = 'https://mandal-variety.com/admin/uploads/';
    foreach ($items as &$item) {
        $item['quantity'] = (int)$item['quantity'];
        $item['cart_item_id'] = (int)$item['cart_item_id'];
        $item['product_id'] = (int)$item['product_id'];
        
        $stockQty = max(0, max((int)($item['stock_quantity'] ?? 0), (int)($item['stock'] ?? 0)));
        $item['stock_quantity'] = $stockQty;
        $item['stock'] = $stockQty;
        $item['available_stock'] = $stockQty;

        $isActive = ((int)($item['is_active'] ?? 1) === 1);
        if (isset($item['status'])) {
            $isActive = $isActive && ($item['status'] !== 'inactive');
        }
        $item['status'] = $item['status'] ?? 'published';
        $item['is_active'] = $isActive ? 1 : 0;
        $item['isInStock'] = $isActive && ($stockQty > 0);
        $item['is_in_stock'] = $item['isInStock'];
        
        $item['has_insufficient_stock'] = $item['isInStock'] && ($item['quantity'] > $stockQty);
        $item['is_available'] = $item['isInStock'] && ($item['quantity'] <= $stockQty);

        if (!$item['is_available']) {
            $has_stock_issues = true;
        }

        $effectivePrice = (float)$item['current_price'];
        if (isset($item['discount_price']) && (float)$item['discount_price'] > 0 && (float)$item['discount_price'] < $effectivePrice) {
            $effectivePrice = (float)$item['discount_price'];
        }
        $item['current_price'] = number_format($effectivePrice, 2, '.', '');

        $item['subtotal'] = $item['quantity'] * $effectivePrice;
        $subtotal += $item['subtotal'];
        $total_items += $item['quantity'];

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
            if (filter_var($img, FILTER_VALIDATE_URL) || strpos($img, 'http') === 0) {
                return $img;
            }
            return $baseUrl . ltrim($img, '/');
        }, $parsedImages);
    }
    unset($item);

    echo json_encode([
        'success' => true,
        'data' => [
            'cart_id' => (int)$cart['id'],
            'items' => $items,
            'summary' => [
                'total_items' => $total_items,
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2)
            ],
            'has_stock_issues' => $has_stock_issues
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>
