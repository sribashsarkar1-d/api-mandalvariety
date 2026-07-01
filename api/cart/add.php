<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $input = json_decode(file_get_contents('php://input'), true);

    $product_id = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);

    if (!$user_id || !$product_id || $quantity < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid product_id or quantity. Use: {"product_id":1,"quantity":2}'
        ]);
        exit;
    }

    // QUERY 1: PRODUCTS
    try {
        $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Error in Query 1 (Products): " . $e->getMessage());
    }

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => "Product #$product_id not found or out of stock"
        ]);
        exit;
    }

    // ACQUIRE LOCK TO PREVENT CONCURRENT REQUESTS (Race Condition)
    $pdo->query("SELECT GET_LOCK('cart_user_{$user_id}', 5)")->fetchAll();

    // QUERY 2: CARTS
    try {
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $pdo->query("SELECT RELEASE_LOCK('cart_user_{$user_id}')")->fetchAll();
        throw new Exception("Error in Query 2 (Carts): " . $e->getMessage());
    }

    if ($cart) {
        $cart_id = $cart['id'];
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
        } catch (Exception $e) {
            $pdo->query("SELECT RELEASE_LOCK('cart_user_{$user_id}')")->fetchAll();
            throw new Exception("Error in Query 3 (Insert Carts): " . $e->getMessage());
        }
    }

    // QUERY 4: CART_ITEMS (Check Existing)
    try {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $pdo->query("SELECT RELEASE_LOCK('cart_user_{$user_id}')")->fetchAll();
        throw new Exception("Error in Query 4 (Select Cart Items): " . $e->getMessage());
    }

    if ($existing) {
        $new_qty = $existing['quantity'] + $quantity;
        try {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_qty, $existing['id']]);
            $cart_item_id = $existing['id'];
        } catch (Exception $e) {
            $pdo->query("SELECT RELEASE_LOCK('cart_user_{$user_id}')")->fetchAll();
            throw new Exception("Error in Query 5 (Update Cart Items): " . $e->getMessage());
        }
    } else {
        try {
            // Check if there are any duplicate rows and delete them just in case
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cart_id, $product_id]);

            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cart_id, $product_id, $quantity, $product['price']]);
            $cart_item_id = $pdo->lastInsertId();
        } catch (Exception $e) {
            $pdo->query("SELECT RELEASE_LOCK('cart_user_{$user_id}')")->fetchAll();
            throw new Exception("Error in Query 6 (Insert Cart Items): " . $e->getMessage());
        }
    }

    $pdo->query("SELECT RELEASE_LOCK('cart_user_{$user_id}')")->fetchAll();

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart!',
        'data' => [
            'cart_item_id' => $cart_item_id,
            'cart_id' => $cart_id,
            'product' => $product,
            'quantity' => isset($new_qty) ? $new_qty : $quantity // 🔥 RETURN THE ACTUAL TOTAL QUANTITY IN THE CART
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
