<?php
// includes/sidebar.php
if (!is_logged_in()) return;
$role = get_user_role();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="desktop-sidebar no-print">
    <div class="desktop-sidebar-brand">
        <?= SITE_NAME ?>
    </div>
    
    <div class="desktop-sidebar-menu">
        <?php if ($role === 'admin'): ?>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/products.php" class="<?= strpos($current_page, 'product') !== false ? 'active' : '' ?>">
                <i class="bi bi-box"></i> Products
            </a>
            <a href="<?= BASE_URL ?>/admin/categories.php" class="<?= strpos($current_page, 'categor') !== false ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="<?= BASE_URL ?>/admin/stock.php" class="<?= strpos($current_page, 'stock') !== false ? 'active' : '' ?>">
                <i class="bi bi-boxes"></i> Stock
            </a>
            <a href="<?= BASE_URL ?>/admin/purchases.php" class="<?= strpos($current_page, 'purchase') !== false ? 'active' : '' ?>">
                <i class="bi bi-cart-plus"></i> Purchases
            </a>
            <a href="<?= BASE_URL ?>/admin/suppliers.php" class="<?= strpos($current_page, 'supplier') !== false ? 'active' : '' ?>">
                <i class="bi bi-truck"></i> Suppliers
            </a>
            <a href="<?= BASE_URL ?>/admin/customers.php" class="<?= strpos($current_page, 'customer') !== false ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Customers
            </a>
            <a href="<?= BASE_URL ?>/admin/sales.php" class="<?= strpos($current_page, 'sale') !== false ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Sales
            </a>
            <a href="<?= BASE_URL ?>/admin/expenses.php" class="<?= strpos($current_page, 'expense') !== false ? 'active' : '' ?>">
                <i class="bi bi-cash-coin"></i> Expenses
            </a>
            <a href="<?= BASE_URL ?>/admin/reports.php" class="<?= strpos($current_page, 'report') !== false ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i> Reports
            </a>
            <a href="<?= BASE_URL ?>/admin/employees.php" class="<?= strpos($current_page, 'employee') !== false ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Employees
            </a>
            <a href="<?= BASE_URL ?>/admin/settings.php" class="<?= strpos($current_page, 'setting') !== false ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/employee/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-house"></i> Home
            </a>
            <a href="<?= BASE_URL ?>/employee/pos.php" class="<?= $current_page == 'pos.php' ? 'active' : '' ?>">
                <i class="bi bi-cart"></i> POS
            </a>
            <a href="<?= BASE_URL ?>/employee/sales.php" class="<?= strpos($current_page, 'sale') !== false ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> My Sales
            </a>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>/logout.php" class="text-danger mt-4">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</aside>
