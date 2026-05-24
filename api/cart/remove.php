<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $cart_item_id = (int)($_GET['cart_item_id'] ?? 0);

    if (!$user_id || !$cart_item_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    // Verify ownership and delete
    $stmt = $pdo->prepare("
        DELETE ci FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        WHERE ci.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_item_id, $user_id]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'Item removed']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>
