<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Employees';

// Fetch employees
$stmt = $pdo->query("SELECT id, name, email, phone, role, status, created_at FROM employee_users ORDER BY name ASC");
$employees = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row g-2">
    <?php if (empty($employees)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No employees found</p>
        </div>
    <?php else: ?>
        <?php foreach ($employees as $emp): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="custom-card p-3 d-flex align-items-center">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($emp['name']) ?></h6>
                    <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($emp['phone'] ?? 'N/A') ?></div>
                    <div class="text-muted small"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($emp['email'] ?? 'N/A') ?></div>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary mb-1 d-block"><?= ucfirst(htmlspecialchars($emp['role'])) ?></span>
                    <span class="badge <?= $emp['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst(htmlspecialchars($emp['status'])) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
