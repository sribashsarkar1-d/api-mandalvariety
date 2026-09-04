<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../includes/functions.php';

if (!is_logged_in()) {
    json_response(false, 'Unauthorized');
}

if (get_user_role() !== 'admin') {
    json_response(false, 'Only admins can perform this action.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_response(false, 'Invalid payload');
}

$employee_id = $_SESSION['user_id'];
$customer_id = !empty($input['customer_id']) ? (int)$input['customer_id'] : 0;
$amount = !empty($input['amount']) ? (float)$input['amount'] : 0.00;
$notes = sanitize_input($input['notes'] ?? '');

if ($customer_id <= 0 || $amount <= 0) {
    json_response(false, 'Invalid customer or amount');
}

try {
    $pdo->beginTransaction();
    
    // 1. Fetch latest due with lock
    $stmt = $pdo->prepare("
        SELECT new_due 
        FROM employee_customer_ledger 
        WHERE customer_id = ? 
        ORDER BY created_at DESC, id DESC 
        LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([$customer_id]);
    $result = $stmt->fetch();
    
    $previous_due = $result ? (float)$result['new_due'] : 0.00;
    
    // 2. Calculate new due and Insert Ledger
    $new_due = $previous_due + $amount;
    
    $stmtLedger = $pdo->prepare("
        INSERT INTO employee_customer_ledger
        (customer_id, employee_id, transaction_type, amount, previous_due, new_due, description)
        VALUES (?, ?, 'manual_due', ?, ?, ?, ?)
    ");
    
    $desc = "Manual Due Added: " . $notes;
    $stmtLedger->execute([
        $customer_id, 
        $employee_id, 
        $amount, 
        $previous_due, 
        $new_due, 
        $desc
    ]);
    
    $pdo->commit();
    
    json_response(true, 'Due added successfully', [
        'customer_id' => $customer_id,
        'previous_due' => $previous_due,
        'added_amount' => $amount,
        'new_due' => $new_due
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(false, $e->getMessage());
}
