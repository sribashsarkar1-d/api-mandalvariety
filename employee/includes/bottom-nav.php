<?php
// includes/bottom-nav.php
if (!is_logged_in()) return;
$role = get_user_role();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="bottom-nav d-lg-none no-print">
    <?php if ($role === 'admin'): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="bottom-nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/products.php" class="bottom-nav-item <?= strpos($current_page, 'product') !== false ? 'active' : '' ?>">
            <i class="bi bi-box"></i>
            <span>Products</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/sales.php" class="bottom-nav-item <?= strpos($current_page, 'sale') !== false ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Sales</span>
        </a>
        <a href="#" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list"></i>
            <span>More</span>
        </a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/employee/dashboard.php" class="bottom-nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house"></i>
            <span>Home</span>
        </a>
        <a href="<?= BASE_URL ?>/employee/pos.php" class="bottom-nav-item <?= $current_page == 'pos.php' ? 'active' : '' ?>">
            <i class="bi bi-cart"></i>
            <span>POS</span>
        </a>
        <a href="<?= BASE_URL ?>/employee/sales.php" class="bottom-nav-item <?= strpos($current_page, 'sale') !== false ? 'active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Sales</span>
        </a>
        <a href="#" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list"></i>
            <span>More</span>
        </a>
    <?php endif; ?>
</nav>
