<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'My Profile';

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM employee_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require_once '../includes/header.php';
?>

<div class="custom-card p-4 text-center mb-4">
    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2.5rem;">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
    <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h5>
    <span class="badge bg-primary mb-2 text-capitalize"><?= htmlspecialchars($user['role']) ?></span>
    <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?></p>
    <p class="text-muted small"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></p>
</div>

<div class="custom-card p-0 overflow-hidden">
    <div class="list-group list-group-flush">
        
        <a href="../logout.php" class="list-group-item list-group-item-action d-flex align-items-center p-3 text-danger">
            <i class="bi bi-box-arrow-right me-3 fs-5"></i>
            <div class="flex-grow-1 fw-bold">Logout</div>
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
