<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'All Sales';

// Fetch all sales
$stmt = $pdo->query("
    SELECT s.*, c.name as customer_name, u.name as employee_name
    FROM employee_sales s
    LEFT JOIN employee_customers c ON s.customer_id = c.id
    LEFT JOIN employee_users u ON s.employee_id = u.id
    ORDER BY s.created_at DESC
    LIMIT 100
");
$sales = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" id="salesSearch" placeholder="Search by Invoice, Customer or Cashier">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($sales)): ?>
            <div class="text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No sales found</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($sales as $sale): ?>
                    <a href="../employee/invoice.php?id=<?= $sale['id'] ?>" class="list-group-item list-group-item-action p-3 mb-2 rounded border custom-card">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($sale['invoice_number']) ?></h6>
                            <span class="badge <?= $sale['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning' ?> rounded-pill">
                                <?= htmlspecialchars(ucfirst($sale['payment_status'])) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span><?= format_date($sale['created_at']) ?></span>
                            <span class="text-capitalize"><i class="bi bi-cash me-1"></i><?= htmlspecialchars($sale['payment_method']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="small text-muted">
                                <div><i class="bi bi-person me-1"></i><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></div>
                                <div><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($sale['employee_name']) ?></div>
                            </div>
                            <h5 class="fw-bold mb-0"><?= format_currency($sale['grand_total']) ?></h5>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('salesSearch').addEventListener('input', function() {
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

<?php 
require_once '../includes/footer.php'; 
?>
