<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin(); // admins can view customers

$page_title = 'Customers';

// Fetch customers with their current due
$stmt = $pdo->query("
    SELECT c.*, 
    (
        SELECT new_due 
        FROM employee_customer_ledger l 
        WHERE l.customer_id = c.id 
        ORDER BY l.created_at DESC, l.id DESC 
        LIMIT 1
    ) as current_due
    FROM employee_customers c 
    ORDER BY c.name ASC
");
$customers = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="mb-4">
    <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden">
        <span class="input-group-text bg-white border-end-0 border-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="customerSearchInput" class="form-control border-start-0 border-0 bg-white shadow-none" placeholder="Search by name or mobile number...">
    </div>
</div>

<div class="row g-2">
    <?php if (empty($customers)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No customers found</p>
        </div>
    <?php else: ?>
        <?php foreach ($customers as $c): ?>
        <div class="col-12 col-md-6 col-lg-4 customer-card-wrapper" data-name="<?= strtolower(htmlspecialchars($c['name'])) ?>" data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>">
            <div class="custom-card p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($c['name']) ?></h6>
                        <div class="text-muted small">📞 <?= htmlspecialchars($c['phone'] ?? 'N/A') ?></div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                    <div>
                        <div class="text-muted small">Current Baki</div>
                        <?php 
                        $due = (float)($c['current_due'] ?? 0);
                        if ($due > 0): ?>
                            <div class="fw-bold text-danger fs-5"><i class="bi bi-exclamation-circle-fill me-1"></i> <?= format_currency($due) ?></div>
                        <?php else: ?>
                            <div class="fw-bold text-success fs-5"><i class="bi bi-check-circle-fill me-1"></i> CLEAR</div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end text-muted small">
                        <div>Purchase: <?= format_currency($c['total_purchase']) ?></div>
                        <div><?= $c['total_bills'] ?> Bills</div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="../employee/pos.php?customer_id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1"><i class="bi bi-cart-plus me-1"></i>New Bill</a>
                    <a href="../employee/customer-ledger.php?id=<?= $c['id'] ?>" class="btn btn-outline-secondary btn-sm flex-grow-1"><i class="bi bi-journal-text me-1"></i>Ledger</a>
                    <a href="../employee/receive-payment.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-cash-coin me-1"></i>Pay</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.customer-card-wrapper');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const phone = card.getAttribute('data-phone');
                if (name.includes(term) || phone.includes(term)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
<?php $extra_js = ob_get_clean(); ?>

<?php require_once '../includes/footer.php'; ?>
