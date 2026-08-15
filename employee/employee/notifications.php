<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_login();
$role = get_user_role();
if ($role !== 'admin') {
    // Basic protection if we want to differentiate later, but right now it's identical
}

$page_title = 'Notifications';
$show_back_btn = true;

// Mark all as read when visiting
$pdo->exec("UPDATE employee_notifications SET is_read = 1 WHERE is_read = 0");

// Fetch notifications
$stmt = $pdo->query("SELECT * FROM employee_notifications ORDER BY created_at DESC LIMIT 50");
$notifications = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row g-3">
    <div class="col-12">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No notifications yet</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush shadow-sm rounded-4" style="background: var(--card-bg);">
                <?php foreach ($notifications as $n): ?>
                    <?php
                        $iconClass = 'bi-info-circle';
                        $iconBg = 'bg-primary';
                        if ($n['type'] == 'sale') {
                            $iconClass = 'bi-receipt';
                            $iconBg = 'bg-success';
                        } elseif ($n['type'] == 'login') {
                            $iconClass = 'bi-box-arrow-in-right';
                            $iconBg = 'bg-info';
                        } elseif ($n['type'] == 'register') {
                            $iconClass = 'bi-person-plus';
                            $iconBg = 'bg-warning';
                        }
                    ?>
                    <div class="list-group-item list-group-item-action d-flex align-items-start p-3 border-bottom-0 border-top" style="border-color: rgba(0,0,0,0.03)!important;">
                        <div class="d-flex align-items-center justify-content-center text-white rounded-circle me-3 <?= $iconBg ?>" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="bi <?= htmlspecialchars($n['icon'] ?: $iconClass) ?> fs-5"></i>
                        </div>
                        <div class="w-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($n['title']) ?></h6>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= date('d M, h:i A', strtotime($n['created_at'])) ?></small>
                            </div>
                            <p class="mb-0 text-muted small"><?= htmlspecialchars($n['message']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
