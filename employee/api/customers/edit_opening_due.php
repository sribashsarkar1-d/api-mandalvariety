<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
$opening_due = isset($data['opening_due']) ? (float)$data['opening_due'] : 0.00;
$employee_id = $_SESSION['user_id'] ?? 0;

if ($customer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

if ($opening_due < 0) {
    echo json_encode(['success' => false, 'message' => 'Opening Due cannot be negative']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Check if opening_due transaction exists
    $stmt = $pdo->prepare("SELECT id FROM employee_customer_ledger WHERE customer_id = ? AND transaction_type = 'opening_due' LIMIT 1");
    $stmt->execute([$customer_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($opening_due == 0) {
            // Delete it if set to 0
            $pdo->prepare("DELETE FROM employee_customer_ledger WHERE id = ?")->execute([$existing['id']]);
        } else {
            // Update amount
            $pdo->prepare("UPDATE employee_customer_ledger SET amount = ? WHERE id = ?")->execute([$opening_due, $existing['id']]);
        }
    } else {
        if ($opening_due > 0) {
            // Get customer created_at to backdate the ledger entry
            $stmtCust = $pdo->prepare("SELECT created_at FROM employee_customers WHERE id = ?");
            $stmtCust->execute([$customer_id]);
            $cust = $stmtCust->fetch();
            $created_at = $cust ? $cust['created_at'] : date('Y-m-d H:i:s');

            $stmtLedger = $pdo->prepare("
                INSERT INTO employee_customer_ledger
                (customer_id, employee_id, transaction_type, amount, previous_due, new_due, description, created_at)
                VALUES (?, ?, 'opening_due', ?, 0, ?, 'Opening Due from previous balance', ?)
            ");
            $stmtLedger->execute([$customer_id, $employee_id, $opening_due, $opening_due, $created_at]);
        }
    }

    // 2. Recalculate all ledger entries for this customer
    // Fetch all ordered chronologically (oldest first)
    // To ensure opening due comes first if they have same timestamp, we order by transaction_type='opening_due' DESC, then created_at ASC, id ASC
    $stmtAll = $pdo->prepare("
        SELECT * FROM employee_customer_ledger 
        WHERE customer_id = ? 
        ORDER BY (transaction_type = 'opening_due') DESC, created_at ASC, id ASC
    ");
    $stmtAll->execute([$customer_id]);
    $ledger_entries = $stmtAll->fetchAll();

    $running_due = 0.00;

    foreach ($ledger_entries as $entry) {
        $prev = $running_due;
        $amount = (float)$entry['amount'];
        $type = $entry['transaction_type'];

        if ($type === 'opening_due' || $type === 'sale_credit') {
            $running_due += $amount;
        } elseif ($type === 'payment') {
            $running_due -= $amount;
        }

        // Update the row
        $pdo->prepare("UPDATE employee_customer_ledger SET previous_due = ?, new_due = ? WHERE id = ?")
            ->execute([$prev, $running_due, $entry['id']]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Opening Due updated successfully.',
        'data' => [
            'customer_id' => $customer_id,
            'new_opening_due' => $opening_due,
            'current_due' => $running_due
        ]
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
