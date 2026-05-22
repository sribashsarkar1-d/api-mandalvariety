<?php
header('Content-Type: application/json');

require '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

$id = $_GET['id'] ?? null;

$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    // Create
    $name = trim($data['name'] ?? '');
    $price = $data['price'] ?? 0;
    $category_id = $data['category_id'] ?? 1;
    $sku = $data['sku'] ?? 'SKU-' . time();
    $slug = strtolower(str_replace(' ', '-', $name)) . '-' . time();
    $stock_quantity = $data['stock_quantity'] ?? 0;

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, slug, price, sku, category_id, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $slug, $price, $sku, $category_id, $stock_quantity])) {
        echo json_encode(['success' => true, 'message' => 'Product saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create product']);
    }
} elseif ($method === 'PUT') {
    // Update
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    $name = trim($data['name'] ?? '');
    $price = $data['price'] ?? null;
    $stock_quantity = $data['stock_quantity'] ?? null;

    $updateFields = [];
    $params = [];

    if (!empty($name)) {
        $updateFields[] = "name = ?";
        $params[] = $name;
    }
    if ($price !== null) {
        $updateFields[] = "price = ?";
        $params[] = $price;
    }
    if ($stock_quantity !== null) {
        $updateFields[] = "stock_quantity = ?";
        $params[] = $stock_quantity;
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }

    $params[] = $id;

    $sql = "UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
