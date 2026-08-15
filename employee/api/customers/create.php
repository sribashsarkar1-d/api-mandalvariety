<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$name = isset($data['name']) ? sanitize_input($data['name']) : '';
$phone = isset($data['phone']) ? sanitize_input($data['phone']) : '';

if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name and Phone are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO employee_customers (name, phone) VALUES (?, ?)");
    $stmt->execute([$name, $phone]);
    $newId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $newId,
            'name' => $name,
            'phone' => $phone
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
