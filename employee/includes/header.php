<?php
// includes/header.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/auth.php';

$page_title = isset($page_title) ? $page_title : SITE_NAME;
$current_page = basename($_SERVER['PHP_SELF']);
$role = get_user_role();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <header class="app-header no-print">
        <div class="d-flex align-items-center">
            <?php if (isset($show_back_btn) && $show_back_btn): ?>
                <i class="bi bi-arrow-left me-3" id="back-btn"></i>
            <?php else: ?>
                <i class="bi bi-list me-3 hamburger d-lg-none" data-bs-toggle="offcanvas" href="#mobileSidebar"></i>
            <?php endif; ?>
            <h1 class="title"><?= htmlspecialchars($page_title) ?></h1>
        </div>
        <div class="d-flex align-items-center">
            <?php if (isset($header_action_html)): ?>
                <?= $header_action_html ?>
            <?php else: ?>
                <i class="bi bi-bell"></i>
            <?php endif; ?>
        </div>
    </header>

    <!-- Mobile Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-start no-print" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary fw-bold"><?= SITE_NAME ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group list-group-flush">
                <?php if ($role === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="list-group-item list-group-item-action <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="<?= BASE_URL ?>/admin/products.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'product') !== false ? 'active' : '' ?>"><i class="bi bi-box me-2"></i> Products</a>
                    <a href="<?= BASE_URL ?>/admin/categories.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'categor') !== false ? 'active' : '' ?>"><i class="bi bi-tags me-2"></i> Categories</a>
                    <a href="<?= BASE_URL ?>/admin/stock.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'stock') !== false ? 'active' : '' ?>"><i class="bi bi-boxes me-2"></i> Stock</a>
                    <a href="<?= BASE_URL ?>/admin/sales.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'sale') !== false ? 'active' : '' ?>"><i class="bi bi-receipt me-2"></i> Sales</a>
                    <a href="<?= BASE_URL ?>/admin/reports.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'report') !== false ? 'active' : '' ?>"><i class="bi bi-graph-up me-2"></i> Reports</a>
                    <a href="<?= BASE_URL ?>/admin/settings.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'setting') !== false ? 'active' : '' ?>"><i class="bi bi-gear me-2"></i> Settings</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/employee/dashboard.php" class="list-group-item list-group-item-action <?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="bi bi-house me-2"></i> Home</a>
                    <a href="<?= BASE_URL ?>/employee/pos.php" class="list-group-item list-group-item-action <?= $current_page == 'pos.php' ? 'active' : '' ?>"><i class="bi bi-cart me-2"></i> POS</a>
                    <a href="<?= BASE_URL ?>/employee/sales.php" class="list-group-item list-group-item-action <?= strpos($current_page, 'sale') !== false ? 'active' : '' ?>"><i class="bi bi-receipt me-2"></i> My Sales</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/logout.php" class="list-group-item list-group-item-action text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </div>
        </div>
    </div>
<?php endif; ?>

    <main class="main-content">
        <div class="container-fluid p-3">
