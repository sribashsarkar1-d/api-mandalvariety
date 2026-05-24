<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = (int)($input['user_id'] ?? $_GET['user_id'] ?? 0);
    $product_id = (int)($input['product_id'] ?? $_POST['product_id'] ?? 0);

    if (!$user_id || !$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID and Product ID required']);
        exit;
    }

    // Check product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Check if already exists
    $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
        exit;
    }

    // Add to wishlist
    $stmt = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
    $wishlist_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true, 
        'message' => 'Added to wishlist ❤️',
        'data' => [
            'wishlist_id' => $wishlist_id,
            'user_id' => $user_id,
            'product_id' => $product_id
        ]
    ]);

} catch (Exception $e) {
    http_response_code(200); // Changed to 200 so Flutter displays the message
    echo json_encode([
        'success' => false, 
        'message' => 'DB Error: ' . $e->getMessage()
    ]);
}
?>
