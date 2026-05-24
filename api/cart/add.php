    <?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';  // Fixed path

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

    // **1. VALIDATE PRODUCT EXISTS**
    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND stock_quantity > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

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

    // **3. CHECK IF ALREADY IN CART**
    $stmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

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
        
        $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_qty, $existing['id']]);
        $cart_item_id = $existing['id'];
    } else {
        // **ADD NEW ITEM**
        $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
        $cart_item_id = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Added to carts!',
        'data' => [
            'cart_item_id' => $cart_item_id,
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
