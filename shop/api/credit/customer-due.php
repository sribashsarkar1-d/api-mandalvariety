<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../includes/functions.php';

if (!is_logged_in()) {
    json_response(false, 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(false, 'Invalid request method');
}

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if ($customer_id <= 0) {
    json_response(false, 'Invalid customer ID');
}

try {
    // Fetch latest due securely from ledger
    $stmt = $pdo->prepare("
        SELECT new_due 
        FROM employee_customer_ledger 
        WHERE customer_id = ? 
        ORDER BY created_at DESC, id DESC 
        LIMIT 1
    ");
    $stmt->execute([$customer_id]);
    $result = $stmt->fetch();
    
    $current_due = $result ? (float)$result['new_due'] : 0.00;
    
    json_response(true, 'Due fetched successfully', [
        'customer_id' => $customer_id,
        'current_due' => $current_due
    ]);
} catch (Exception $e) {
    json_response(false, 'An error occurred while fetching due');
}
