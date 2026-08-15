<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Purchases';

// Fetch all purchases
$stmt = $pdo->query("
    SELECT p.*, s.name as supplier_name, u.name as employee_name
    FROM employee_purchases p
    LEFT JOIN employee_suppliers s ON p.supplier_id = s.id
    LEFT JOIN employee_users u ON p.employee_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 100
");
$purchases = $stmt->fetchAll();

$header_action_html = '<a href="purchase-add.php" class="btn btn-sm btn-light text-primary fw-bold"><i class="bi bi-plus-lg"></i> Add</a>';

require_once '../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" id="purchaseSearch" placeholder="Search by Purchase # or Supplier">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($purchases)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-check text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No purchases found</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($purchases as $p): ?>
                    <div class="list-group-item p-3 mb-2 rounded border custom-card">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($p['purchase_number']) ?></h6>
                            <span class="badge <?= $p['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning' ?> rounded-pill">
                                <?= htmlspecialchars(ucfirst($p['payment_status'])) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span><?= format_date($p['created_at']) ?></span>
                            <span class="text-capitalize"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['employee_name']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="small text-muted">
                                <div><i class="bi bi-truck me-1"></i><?= htmlspecialchars($p['supplier_name'] ?? 'N/A') ?></div>
                            </div>
                            <h5 class="fw-bold mb-0"><?= format_currency($p['grand_total']) ?></h5>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('purchaseSearch').addEventListener('input', function() {
    let term = this.value.toLowerCase();
    let cards = document.querySelectorAll('.list-group-item');
    cards.forEach(card => {
        let text = card.textContent.toLowerCase();
        if (text.includes(term)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
