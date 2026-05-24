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

    // **1. VALIDATE PRODUCT EXISTS & GET PRICE**
    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND stock_quantity > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => "Product #$product_id not found or out of stock"
        ]);
        exit;
    }

    // **2. CHECK STOCK**
    if ($product['stock_quantity'] < $quantity) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "Only {$product['stock_quantity']} available. Requested: $quantity"
        ]);
        exit;
    }

    // **3. FIND OR CREATE CART FOR USER**
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = $cart['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    }

    // **4. CHECK IF ITEM ALREADY IN CART_ITEMS**
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // **UPDATE QUANTITY**
        $new_qty = $existing['quantity'] + $quantity;
        if ($new_qty > $product['stock_quantity']) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => "Max stock exceeded. Only {$product['stock_quantity']} available"
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_qty, $existing['id']]);
        $cart_item_id = $existing['id'];
    } else {
        // **ADD NEW ITEM**
        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cart_id, $product_id, $quantity, $product['price']]);
        $cart_item_id = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart!',
        'data' => [
            'cart_item_id' => $cart_item_id,
            'cart_id' => $cart_id,
            'product' => $product,
            'quantity' => $quantity
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
