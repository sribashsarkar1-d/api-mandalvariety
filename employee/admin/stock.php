<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Stock Management';

$query = "
    SELECT 
        p.id, p.name, p.unit, p.minimum_stock,
        COALESCE(s.quantity, 0) as stock, s.stock_status
    FROM employee_products p
    LEFT JOIN employee_product_stock s ON p.id = s.product_id
    WHERE p.status != 'deleted'
    ORDER BY s.quantity ASC
";

$stmt = $pdo->query($query);
$stocks = $stmt->fetchAll();

$header_action_html = '<a href="purchase-add.php" class="btn btn-sm btn-light text-primary fw-bold"><i class="bi bi-plus-lg"></i> Purchase</a>';

require_once '../includes/header.php';
?>

<div class="row g-2">
    <?php foreach ($stocks as $p): ?>
    <?php
        $badgeClass = 'bg-success';
        if ($p['stock_status'] === 'out_of_stock' || $p['stock'] <= 0) $badgeClass = 'bg-danger';
        elseif ($p['stock_status'] === 'low_stock' || $p['stock'] <= $p['minimum_stock']) $badgeClass = 'bg-warning text-dark';
    ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="custom-card p-3 mb-2 d-flex justify-content-between align-items-center border-start border-4 <?= str_replace('bg-', 'border-', $badgeClass) ?>">
            <div>
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                <div class="small text-muted">Min: <?= (float)$p['minimum_stock'] ?> <?= htmlspecialchars($p['unit']) ?></div>
            </div>
            <div class="text-end">
                <span class="badge <?= $badgeClass ?> mb-1 rounded-pill">
                    <?= (float)$p['stock'] ?> <?= htmlspecialchars($p['unit']) ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
