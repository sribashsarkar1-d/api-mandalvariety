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

$data = json_decode(file_get_contents("php://input"), true);
$name = isset($data['name']) ? sanitize_input($data['name']) : '';
$phone = isset($data['phone']) ? sanitize_input($data['phone']) : '';
$address = isset($data['address']) ? sanitize_input($data['address']) : '';
$opening_due = isset($data['opening_due']) ? (float)$data['opening_due'] : 0.00;
$employee_id = $_SESSION['user_id'] ?? 0;

if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Name and Phone are required.']);
    exit;
}

if ($opening_due < 0) {
    echo json_encode(['success' => false, 'message' => 'Opening Due cannot be negative.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO employee_customers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->execute([$name, $phone, $address]);
    $newId = $pdo->lastInsertId();

    if ($opening_due > 0) {
        $stmtLedger = $pdo->prepare("
            INSERT INTO employee_customer_ledger
            (customer_id, employee_id, transaction_type, amount, previous_due, new_due, description)
            VALUES (?, ?, 'opening_due', ?, 0, ?, 'Opening Due from previous balance')
        ");
        $stmtLedger->execute([$newId, $employee_id, $opening_due, $opening_due]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $newId,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'opening_due' => $opening_due
        ]
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
