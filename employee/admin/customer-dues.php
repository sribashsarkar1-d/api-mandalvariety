<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Customer Dues Report';
$show_back_btn = true;

// Fetch all customers and their latest due
$stmt = $pdo->query("
    SELECT c.*, 
    (
        SELECT new_due 
        FROM employee_customer_ledger l 
        WHERE l.customer_id = c.id 
        ORDER BY l.created_at DESC, l.id DESC 
        LIMIT 1
    ) as current_due,
    (
        SELECT created_at 
        FROM employee_customer_ledger l 
        WHERE l.customer_id = c.id 
        ORDER BY l.created_at DESC, l.id DESC 
        LIMIT 1
    ) as last_transaction_date
    FROM employee_customers c 
    HAVING current_due > 0
    ORDER BY current_due DESC
");
$customers_with_due = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_baki = 0;
foreach ($customers_with_due as $c) {
    $total_baki += $c['current_due'];
}

require_once '../includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="custom-card p-3 bg-danger text-white h-100 d-flex flex-column justify-content-center">
            <div class="text-white-50 fw-bold mb-1">TOTAL OUTSTANDING BAKI</div>
            <h2 class="mb-0 fw-bold"><?= format_currency($total_baki) ?></h2>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="custom-card p-3 bg-primary text-white h-100 d-flex flex-column justify-content-center">
            <div class="text-white-50 fw-bold mb-1">CUSTOMERS WITH DUE</div>
            <h2 class="mb-0 fw-bold"><?= count($customers_with_due) ?></h2>
        </div>
    </div>
</div>

<div class="custom-card p-0">
    <div class="p-3 border-bottom bg-light rounded-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="fw-bold mb-0">Customers List</h6>
        <div class="input-group input-group-sm" style="max-width: 250px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="customerSearchInput" class="form-control border-start-0 ps-0" placeholder="Search customer...">
        </div>
    </div>
    <div class="p-3">
        <?php if (empty($customers_with_due)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">No outstanding dues.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($customers_with_due as $c): ?>
                    <div class="list-group-item px-0 py-3 border-bottom border-light customer-list-item" data-name="<?= strtolower(htmlspecialchars($c['name'])) ?>" data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($c['name']) ?></h6>
                                <div class="text-muted small">📞 <?= htmlspecialchars($c['phone'] ?? 'N/A') ?></div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted small">Current Baki:</div>
                                <div class="fw-bold text-danger fs-5"><i class="bi bi-exclamation-circle-fill me-1"></i> <?= format_currency($c['current_due']) ?></div>
                            </div>
                            <div class="col-6 col-md-4 text-md-end">
                                <div class="text-muted small">Last Transaction:</div>
                                <div class="fw-bold text-dark"><?= $c['last_transaction_date'] ? date('d M Y', strtotime($c['last_transaction_date'])) : 'N/A' ?></div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a href="../employee/pos.php?customer_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-cart-plus me-1"></i>New Bill</a>
                            <a href="../employee/customer-ledger.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-journal-text me-1"></i>Ledger</a>
                            <a href="../employee/receive-payment.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-cash-coin me-1"></i>Receive Payment</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.customer-list-item');
            
            items.forEach(item => {
                const name = item.getAttribute('data-name');
                const phone = item.getAttribute('data-phone');
                if (name.includes(term) || phone.includes(term)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>
<?php $extra_js = ob_get_clean(); ?>

<?php require_once '../includes/footer.php'; ?>
