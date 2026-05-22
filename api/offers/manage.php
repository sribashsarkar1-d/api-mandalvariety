<?php
header('Content-Type: application/json');
require '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $product_id = $data['product_id'] ?? null;
    $category_id = $data['category_id'] ?? null;
    $offer_name = trim($data['offer_name'] ?? '');
    $offer_type = $data['offer_type'] ?? 'flat';
    $offer_value = $data['offer_value'] ?? 0;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $status = $data['status'] ?? 'active';
    $priority = $data['priority'] ?? 0;

    if (empty($offer_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Offer name is required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO offers (product_id, category_id, offer_name, offer_type, offer_value, start_date, end_date, status, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$product_id, $category_id, $offer_name, $offer_type, $offer_value, $start_date, $end_date, $status, $priority])) {
        echo json_encode(['success' => true, 'message' => 'Offer created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create offer']);
    }
} elseif ($method === 'PUT') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    // Update fields conditionally
    $updates = [];
    $params = [];
    foreach (['product_id', 'category_id', 'offer_name', 'offer_type', 'offer_value', 'start_date', 'end_date', 'status', 'priority'] as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    $params[] = $id;
    
    $stmt = $pdo->prepare("UPDATE offers SET " . implode(", ", $updates) . " WHERE id = ?");
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Offer updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update offer']);
    }
} elseif ($method === 'DELETE') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => 'Offer deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete offer']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
