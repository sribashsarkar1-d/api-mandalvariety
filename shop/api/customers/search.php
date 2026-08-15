<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$search = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT id, name, phone FROM employee_customers WHERE name LIKE ? OR phone LIKE ? ORDER BY name ASC LIMIT 20");
        $param = "%{$search}%";
        $stmt->execute([$param, $param]);
    } else {
        $stmt = $pdo->query("SELECT id, name, phone FROM employee_customers ORDER BY name ASC LIMIT 20");
    }
    
    $customers = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
