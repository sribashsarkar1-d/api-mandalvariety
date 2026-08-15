<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_employee();

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customer_id <= 0) {
    die("Invalid customer.");
}

$stmtCust = $pdo->prepare("SELECT * FROM employee_customers WHERE id = ?");
$stmtCust->execute([$customer_id]);
$customer = $stmtCust->fetch();
if (!$customer) die("Customer not found.");

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

$current_due = $ledger ? (float)$ledger[0]['new_due'] : 0.00;

$page_title = 'Customer Ledger';
$show_back_btn = true;
require_once '../includes/header.php';
?>

<div class="custom-card p-3 mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="fw-bold mb-1"><?= htmlspecialchars($customer['name']) ?></h5>
        <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></div>
    </div>
    <div class="text-end">
        <?php if ($current_due > 0): ?>
            <div class="fw-bold text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> BAKI <?= format_currency($current_due) ?></div>
        <?php else: ?>
            <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> CLEAR</div>
        <?php endif; ?>
    </div>
</div>

<div class="mb-3 d-flex justify-content-end">
    <a href="receive-payment.php?id=<?= $customer['id'] ?>" class="btn btn-primary shadow-sm"><i class="bi bi-cash me-1"></i> Receive Payment</a>
</div>

<div class="custom-card p-0">
    <div class="p-3 border-bottom bg-light rounded-top">
        <h6 class="fw-bold mb-0">Transaction History</h6>
    </div>
    <div class="p-3">
        <?php if (empty($ledger)): ?>
            <div class="text-center py-4 text-muted">No transactions found.</div>
        <?php else: ?>
            <div class="ledger-timeline">
                <?php foreach ($ledger as $item): ?>
                    <div class="card mb-3 shadow-sm border-0 bg-light border-start border-4 <?= $item['transaction_type'] === 'sale_credit' ? 'border-danger' : ($item['transaction_type'] === 'payment' ? 'border-success' : 'border-secondary') ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-bold">
                                    <?php if ($item['transaction_type'] === 'sale_credit'): ?>
                                        <span class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> SALE CREDIT</span>
                                    <?php elseif ($item['transaction_type'] === 'payment'): ?>
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> PAYMENT</span>
                                    <?php else: ?>
                                        <span class="text-secondary"><i class="bi bi-info-circle-fill me-1"></i> <?= strtoupper($item['transaction_type']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted fw-bold">
                                    <?= date('d M Y', strtotime($item['created_at'])) ?>
                                </div>
                            </div>
                            
                            <?php if ($item['invoice_number']): ?>
                                <div class="text-dark fw-bold mb-3 small">Invoice #<?= htmlspecialchars($item['invoice_number']) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($item['transaction_type'] === 'sale_credit'): ?>
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Today's Bill:</span>
                                    <span class="fw-bold text-dark"><?= format_currency($item['amount']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <span>Previous Baki:</span>
                                    <span class="fw-bold text-dark"><?= format_currency($item['previous_due']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold text-muted small">New Outstanding:</span>
                                    <span class="fw-bold text-danger"><?= format_currency($item['new_due']) ?></span>
                                </div>
                            <?php elseif ($item['transaction_type'] === 'payment'): ?>
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Paid:</span>
                                    <span class="fw-bold text-success"><?= format_currency($item['amount']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <span>Previous Outstanding:</span>
                                    <span class="fw-bold text-dark"><?= format_currency($item['previous_due']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold text-muted small">Remaining:</span>
                                    <span class="fw-bold <?= $item['new_due'] > 0 ? 'text-danger' : 'text-success' ?>"><?= format_currency($item['new_due']) ?></span>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Amount:</span>
                                    <span class="fw-bold text-dark"><?= format_currency($item['amount']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold text-muted small">New Outstanding:</span>
                                    <span class="fw-bold text-danger"><?= format_currency($item['new_due']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
