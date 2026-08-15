<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../includes/functions.php';

if (!is_logged_in()) {
    json_response(false, 'Unauthorized');
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
$payment_amount = !empty($input['payment_amount']) ? (float)$input['payment_amount'] : 0.00;
$payment_method = sanitize_input($input['payment_method'] ?? 'cash');
$transaction_id = sanitize_input($input['transaction_id'] ?? '');
$notes = sanitize_input($input['notes'] ?? '');

if ($customer_id <= 0 || $payment_amount <= 0) {
    json_response(false, 'Invalid customer or payment amount');
}

$valid_payments = ['cash', 'upi', 'card'];
if (!in_array($payment_method, $valid_payments)) {
    $payment_method = 'cash';
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
    
    // 2. Validate payment amount against due (Do not allow silent overpayment without advance feature)
    if ($payment_amount > $previous_due) {
        throw new Exception("Payment amount (₹{$payment_amount}) exceeds current due (₹{$previous_due}).");
    }
    
    // 3. Insert payment
    $stmtPay = $pdo->prepare("
        INSERT INTO employee_credit_payments 
        (customer_id, employee_id, amount, payment_method, transaction_id, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtPay->execute([$customer_id, $employee_id, $payment_amount, $payment_method, $transaction_id, $notes]);
    $credit_payment_id = $pdo->lastInsertId();
    
    // 4. Calculate new due and Insert Ledger
    $new_due = $previous_due - $payment_amount;
    
    $stmtLedger = $pdo->prepare("
        INSERT INTO employee_customer_ledger
        (customer_id, employee_id, credit_payment_id, transaction_type, amount, previous_due, new_due, description)
        VALUES (?, ?, ?, 'payment', ?, ?, ?, ?)
    ");
    
    $desc = "Payment received via " . strtoupper($payment_method);
    $stmtLedger->execute([
        $customer_id, 
        $employee_id, 
        $credit_payment_id, 
        $payment_amount, 
        $previous_due, 
        $new_due, 
        $desc
    ]);
    
    $pdo->commit();
    
    json_response(true, 'Payment received successfully', [
        'customer_id' => $customer_id,
        'previous_due' => $previous_due,
        'paid_amount' => $payment_amount,
        'new_due' => $new_due
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(false, $e->getMessage());
}
