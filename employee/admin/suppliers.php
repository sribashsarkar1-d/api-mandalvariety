<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Suppliers';

// Fetch suppliers
$stmt = $pdo->query("SELECT * FROM employee_suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row g-2">
    <?php if (empty($suppliers)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No suppliers found</p>
        </div>
    <?php else: ?>
        <?php foreach ($suppliers as $s): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="custom-card p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($s['name']) ?></h6>
                    <div class="text-muted small"><i class="bi bi-building me-1"></i><?= htmlspecialchars($s['company_name'] ?? 'N/A') ?></div>
                    <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($s['phone'] ?? 'N/A') ?></div>
                </div>
                <div>
                    <span class="badge <?= $s['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($s['status']) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
