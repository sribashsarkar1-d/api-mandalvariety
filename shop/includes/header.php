<?php
// includes/header.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/auth.php';

$page_title = isset($page_title) ? $page_title : SITE_NAME;
$current_page = basename($_SERVER['PHP_SELF']);
$role = get_user_role();

// Fetch unread notifications count
$unread_notifications_count = 0;
if (is_logged_in() && isset($pdo)) {
    $notif_stmt = $pdo->query("SELECT COUNT(*) FROM employee_notifications WHERE is_read = 0");
    $unread_notifications_count = $notif_stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($page_title) ?> - <?= SITE_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    
    <?php if (isset($extra_css)): echo $extra_css; endif; ?>
</head>
<body>

<?php
// Only show sidebar if user is logged in
if (is_logged_in()) {
    require_once __DIR__ . '/sidebar.php';
}
?>
<div class="main-wrapper">
    
<?php if (is_logged_in()): ?>
    <main class="main-content">
        <!-- Desktop Header -->
        <header class="app-header no-print d-none d-lg-flex">
            <div class="d-flex align-items-center w-100">
                <?php if (isset($show_back_btn) && $show_back_btn): ?>
                    <a href="javascript:history.back()" class="text-dark me-3"><i class="bi bi-arrow-left fs-4"></i></a>
                    <h1 class="title mb-0"><?= htmlspecialchars($page_title) ?></h1>
                <?php else: ?>
                    <div class="header-search">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Search anything...">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="header-actions">
                <?php if (isset($header_action_html)): ?>
                    <div class="me-2"><?= $header_action_html ?></div>
                <?php endif; ?>
                <div class="notification-icon">
                    <a href="<?= BASE_URL ?>/<?= $role ?>/notifications.php" class="text-dark">
                        <i class="bi bi-bell"></i>
                        <?php if ($unread_notifications_count > 0): ?>
                            <span class="notification-badge bg-danger"><?= $unread_notifications_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="user-profile dropdown" data-bs-toggle="dropdown">
                    <?php 
                    $user_name = $_SESSION['user_name'] ?? 'User';
                    $user_initials = strtoupper(substr($user_name, 0, 1));
                    ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=random&color=fff" alt="Profile">
                    <div class="user-profile-info d-none d-md-flex">
                        <span class="user-profile-name"><?= htmlspecialchars($user_name) ?></span>
                        <span class="user-profile-role text-capitalize"><?= htmlspecialchars($role) ?> <i class="bi bi-chevron-down ms-1 small"></i></span>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/employee/profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </header>

        <!-- Mobile App Gradient Header -->
        <header class="app-header no-print d-lg-none">
            <div class="mobile-header-top">
                <div class="d-flex align-items-center">
                    <?php if (isset($show_back_btn) && $show_back_btn): ?>
                        <i class="bi bi-arrow-left hamburger me-3" onclick="history.back()"></i>
                    <?php else: ?>
                        <i class="bi bi-list hamburger me-3" data-bs-toggle="offcanvas" href="#mobileSidebar"></i>
                    <?php endif; ?>
                    <span class="mobile-header-brand"><?= SITE_NAME ?></span>
                </div>
                <div class="d-flex align-items-center">
                    <?php if (isset($header_action_html)): ?>
                        <div class="me-3"><?= $header_action_html ?></div>
                    <?php endif; ?>
                    <div class="notification-icon">
                        <a href="<?= BASE_URL ?>/<?= $role ?>/notifications.php" class="text-white">
                            <i class="bi bi-bell text-white"></i>
                            <?php if ($unread_notifications_count > 0): ?>
                                <span class="notification-badge bg-danger"><?= $unread_notifications_count ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if ($current_page == 'dashboard.php'): ?>
            <div class="mobile-header-user">
                <div class="mobile-header-greeting">
                    <p>Welcome back,</p>
                    <h4><?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h4>
                </div>
                <a href="<?= BASE_URL ?>/<?= $role ?>/profile.php" class="text-decoration-none">
                    <div class="mobile-user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 2)) ?>
                    </div>
                </a>
            </div>
            <?php else: ?>
            <div class="mobile-header-user">
                <div class="mobile-header-greeting">
                    <h4><?= htmlspecialchars($page_title) ?></h4>
                </div>
            </div>
            <?php endif; ?>
        </header>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start no-print shadow" tabindex="-1" id="mobileSidebar" style="width: 300px; border-right: none; border-top-right-radius: 20px; border-bottom-right-radius: 20px;">
            <div class="offcanvas-header border-0 pb-0" style="background: linear-gradient(135deg, var(--secondary-purple), var(--primary-blue)); color: white; border-top-right-radius: 20px; padding: 2rem 1.5rem 1.5rem 1.5rem;">
                <div class="d-flex align-items-center w-100">
                    <div class="mobile-user-avatar bg-white text-primary fw-bold fs-5 me-3 shadow-sm" style="width: 50px; height: 50px; border: 2px solid rgba(255,255,255,0.8);">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 2)) ?>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold fs-5"><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
                        <small style="color: rgba(255,255,255,0.8);" class="text-capitalize"><?= htmlspecialchars($role) ?></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
            </div>
            <div class="offcanvas-body d-flex flex-column px-3 pt-4 pb-4">
                <small class="text-muted fw-bold mb-3 px-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Menu</small>
                <div class="list-group list-group-flush mb-4">
                    <?php if ($role === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= $current_page == 'dashboard.php' ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-grid-1x2-fill me-3 fs-5 <?= $current_page == 'dashboard.php' ? 'text-white' : 'text-primary opacity-75' ?>"></i> Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>/admin/products.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'product') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-box-seam-fill me-3 fs-5 <?= strpos($current_page, 'product') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Products
                        </a>
                        <a href="<?= BASE_URL ?>/admin/categories.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'categor') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-tags-fill me-3 fs-5 <?= strpos($current_page, 'categor') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Categories
                        </a>
                        <a href="<?= BASE_URL ?>/admin/customers.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'customer') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-people-fill me-3 fs-5 <?= strpos($current_page, 'customer') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Customers
                        </a>
                        <a href="<?= BASE_URL ?>/admin/sales.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'sale') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-receipt me-3 fs-5 <?= strpos($current_page, 'sale') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Sales
                        </a>
                        <a href="<?= BASE_URL ?>/admin/reports.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'report') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-graph-up-arrow me-3 fs-5 <?= strpos($current_page, 'report') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Reports
                        </a>
                        <a href="<?= BASE_URL ?>/admin/settings.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'setting') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-gear-fill me-3 fs-5 <?= strpos($current_page, 'setting') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Settings
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/employee/dashboard.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= $current_page == 'dashboard.php' ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-grid-1x2-fill me-3 fs-5 <?= $current_page == 'dashboard.php' ? 'text-white' : 'text-primary opacity-75' ?>"></i> Home
                        </a>
                        <a href="<?= BASE_URL ?>/employee/pos.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= $current_page == 'pos.php' ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-cart-fill me-3 fs-5 <?= $current_page == 'pos.php' ? 'text-white' : 'text-primary opacity-75' ?>"></i> POS
                        </a>
                        <a href="<?= BASE_URL ?>/employee/sales.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'sale') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-receipt me-3 fs-5 <?= strpos($current_page, 'sale') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> My Sales
                        </a>
                        <a href="<?= BASE_URL ?>/employee/customers.php" class="list-group-item list-group-item-action border-0 rounded-4 mb-2 py-3 px-3 d-flex align-items-center <?= strpos($current_page, 'customer') !== false ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-muted fw-medium' ?>">
                            <i class="bi bi-people-fill me-3 fs-5 <?= strpos($current_page, 'customer') !== false ? 'text-white' : 'text-primary opacity-75' ?>"></i> Customers
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="mt-auto w-100">
                    <a href="<?= BASE_URL ?>/logout.php" class="btn w-100 py-3 rounded-4 fw-bold d-flex align-items-center justify-content-center" style="background-color: #fff0f0; color: var(--danger);">
                        <i class="bi bi-box-arrow-right me-2 fs-5"></i> Logout
                    </a>
                </div>
            </div>
        </div>
<?php else: ?>
    <main class="main-content" style="margin-left: 0;">
<?php endif; ?>

        <!-- Added mobile overlap container class if on dashboard for the card overlapping effect -->
        <div class="container-fluid py-4 <?= ($current_page == 'dashboard.php' && is_logged_in()) ? 'mobile-overlap-container' : '' ?>">
