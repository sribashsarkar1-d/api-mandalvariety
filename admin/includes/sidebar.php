<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$requestUri  = $_SERVER['REQUEST_URI'] ?? '';
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

// Detect if we are in root (admin/) or subfolder
$basePath = ($currentDir === 'admin') ? '' : '../';

function isMenuActive($keywords = [])
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $page = basename($_SERVER['PHP_SELF']);

    foreach ($keywords as $keyword) {
        if ($page === $keyword || strpos($uri, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin User';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@example.com';
?>

<style>
    :root {
        --sidebar-bg: #071733;
        --sidebar-bg-2: #0a1d3f;
        --sidebar-border: rgba(255,255,255,0.06);
        --sidebar-text: rgba(226, 232, 240, 0.82);
        --sidebar-text-soft: rgba(148, 163, 184, 0.88);
        --sidebar-text-strong: #ffffff;
        --sidebar-active: rgba(148, 163, 184, 0.20);
        --sidebar-hover: rgba(148, 163, 184, 0.12);
        --sidebar-icon-bg: linear-gradient(135deg, #9fb39a 0%, #6f876c 100%);
        --sidebar-shadow: 0 20px 50px rgba(2, 6, 23, 0.45);
    }

    .sidebar {
        width: 260px;
        min-width: 260px;
        height: 100vh;
        position: sticky;
        top: 0;
        display: flex;
        flex-direction: column;
        background: linear-gradient(180deg, var(--sidebar-bg) 0%, #06142e 100%);
        color: var(--sidebar-text);
        border-right: 1px solid var(--sidebar-border);
        box-shadow: var(--sidebar-shadow);
        transition: all 0.25s ease;
        z-index: 1030;
        overflow: hidden;
    }

    .sidebar.collapsed {
        width: 88px;
        min-width: 88px;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 16px 14px;
        border-bottom: 1px solid var(--sidebar-border);
    }

    .sidebar-logo {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--sidebar-icon-bg);
        color: #fff;
        font-weight: 800;
        font-size: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 10px 24px rgba(111, 135, 108, 0.28);
    }

    .sidebar-brand {
        min-width: 0;
    }

    .sidebar-brand h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--sidebar-text-strong);
        line-height: 1.2;
    }

    .sidebar-brand small {
        display: block;
        color: var(--sidebar-text-soft);
        font-size: .82rem;
        margin-top: 2px;
    }

    .sidebar-menu-wrap {
        flex: 1;
        overflow-y: auto;
        padding: 10px 10px 16px;
    }

    .sidebar-menu-wrap::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-menu-wrap::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.22);
        border-radius: 20px;
    }

    .sidebar-group-title {
        padding: 14px 12px 8px;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: rgba(148, 163, 184, 0.72);
        font-weight: 700;
    }

    .sidebar-menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar-menu li {
        margin-bottom: 6px;
    }

    .sidebar-menu li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: 12px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .sidebar-menu li a i {
        width: 20px;
        text-align: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .sidebar-menu li a span {
        white-space: nowrap;
    }

    .sidebar-menu li a:hover {
        background: var(--sidebar-hover);
        color: var(--sidebar-text-strong);
        transform: translateX(2px);
    }

    .sidebar-menu li a.active {
        background: rgba(120, 138, 162, 0.34);
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.04);
    }

    .sidebar-menu li a.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 4px;
        border-radius: 0 8px 8px 0;
        background: #c5d2c3;
    }

    .sidebar-footer {
        padding: 14px 12px 16px;
        border-top: 1px solid var(--sidebar-border);
        background: rgba(0, 0, 0, 0.08);
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 10px;
        border-radius: 14px;
        background: rgba(255,255,255,0.04);
    }

    .sidebar-user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: var(--sidebar-icon-bg);
        color: #fff;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-user-info {
        min-width: 0;
        flex: 1;
    }

    .sidebar-user-info .name {
        font-size: .92rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    .sidebar-user-info .email {
        font-size: .78rem;
        color: rgba(203, 213, 225, 0.70);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-user-logout {
        color: rgba(203, 213, 225, 0.82);
        text-decoration: none;
        font-size: .95rem;
    }

    .sidebar-user-logout:hover {
        color: #fff;
    }

    @media (min-width: 992px) {
        .sidebar.collapsed .sidebar-brand,
        .sidebar.collapsed .sidebar-group-title,
        .sidebar.collapsed .sidebar-menu li a span,
        .sidebar.collapsed .sidebar-user-info {
            display: none;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-menu li a {
            justify-content: center;
            padding: 12px;
        }

        .sidebar.collapsed .sidebar-user {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-user-logout {
            display: none;
        }
    }

    @media (max-width: 991.98px) {
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            transform: translateX(-100%);
            width: 260px !important;
            min-width: 260px !important;
            z-index: 1050;
        }

        .sidebar.collapsed {
            transform: translateX(0);
            width: 260px !important;
            min-width: 260px !important;
        }
    }
</style> 
<div class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">MV</div>
        <div class="sidebar-brand">
            <h4>Mandal Variety</h4>
            <small>Admin Panel</small>
        </div>
    </div>

    <div class="sidebar-menu-wrap">
        <div class="sidebar-group-title">Main</div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= $basePath ?>dashboard.php" class="<?= isMenuActive(['dashboard.php']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-dashboard"></use></svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>analytics/index.php" class="<?= isMenuActive(['analytics']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-analytics"></use></svg>
                    <span>Analytics</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-group-title">Commerce</div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= $basePath ?>categories/list.php" class="<?= isMenuActive(['categories', 'category']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-categories"></use></svg>
                    <span>Categories</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>products/list.php" class="<?= isMenuActive(['products']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-products"></use></svg>
                    <span>Products</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>orders/list.php" class="<?= isMenuActive(['orders']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-orders"></use></svg>
                    <span>Orders</span>
                </a>
            </li>

            <!-- <li>
                <a href="<?= $basePath ?>checkout/list.php" class="<?= isMenuActive(['checkout']) ? 'active' : '' ?>">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Checkout</span>
                </a>
            </li> -->

            <li>
                <a href="<?= $basePath ?>cart/list.php" class="<?= isMenuActive(['cart']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-cart"></use></svg>
                    <span>Cart</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>wishlist/list.php" class="<?= isMenuActive(['wishlist']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-wishlist"></use></svg>
                    <span>Wishlist</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-group-title">Customers</div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= $basePath ?>customers/list.php" class="<?= isMenuActive(['customer', 'customers']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-customers"></use></svg>
                    <span>Customers</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>review/list.php" class="<?= isMenuActive(['review', 'reviews']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-review"></use></svg>
                    <span>Review</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-group-title">Logistics</div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= $basePath ?>delivery-boys/list.php" class="<?= isMenuActive(['delivery-boys', 'delivery_boys']) ? 'active' : '' ?>">
                    <i class="fa-solid fa-motorcycle" style="width:20px; text-align:center; flex-shrink:0;"></i>
                    <span>Delivery Partners</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-group-title">System</div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= $basePath ?>settings/index.php" class="<?= isMenuActive(['settings']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-settings"></use></svg>
                    <span>Settings</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>policies/list.php" class="<?= isMenuActive(['policies', 'policy']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-policies"></use></svg>
                    <span>Policies</span>
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>age-verify/index.php" class="<?= isMenuActive(['age-verify', 'age_verify']) ? 'active' : '' ?>">
                    <svg class="icon"><use href="#icon-age-verify"></use></svg>
                    <span>Age Verify</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?= strtoupper(substr($adminName, 0, 1)) ?>
            </div>

            <div class="sidebar-user-info">
                <div class="name"><?= e($adminName) ?></div>
                <div class="email"><?= e($adminEmail) ?></div>
            </div>

            <a href="<?= $basePath ?>logout.php" class="sidebar-user-logout" title="Logout">
                <svg class="icon"><use href="#icon-logout"></use></svg>
            </a>
        </div>
    </div>
</div>