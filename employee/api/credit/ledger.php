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
    $stmt = $pdo->prepare("
        SELECT l.*, s.invoice_number, u.name as employee_name
        FROM employee_customer_ledger l
        LEFT JOIN employee_sales s ON l.sale_id = s.id
        LEFT JOIN employee_users u ON l.employee_id = u.id
        WHERE l.customer_id = ?
        ORDER BY l.created_at DESC, l.id DESC
    ");
    $stmt->execute([$customer_id]);
    $ledger = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Also fetch customer info for convenience
    $stmtCust = $pdo->prepare("SELECT name, phone FROM employee_customers WHERE id = ?");
    $stmtCust->execute([$customer_id]);
    $customer = $stmtCust->fetch(PDO::FETCH_ASSOC);
    
    json_response(true, 'Ledger fetched successfully', [
        'customer' => $customer,
        'ledger' => $ledger
    ]);
} catch (Exception $e) {
    json_response(false, 'An error occurred while fetching ledger: ' . $e->getMessage());
}
