<?php
require_once __DIR__ . '/config.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminImage = BASE_URL . 'uploads/profile/avtar.jpg';
$productListUrl = BASE_URL . 'products/list.php';
$orderListUrl = BASE_URL . 'orders/list.php';
$customerListUrl = BASE_URL . 'customer/list.php';
$logoutUrl = BASE_URL . 'logout.php';
$profileUrl = '' . BASE_URL . 'profile.php';

if (!isset($_SESSION['admin_notification_seen_at'])) {
    $_SESSION['admin_notification_seen_at'] = date('Y-m-d H:i:s');
}
$seenAt = $_SESSION['admin_notification_seen_at'];

$newOrdersCount = 0;
$newCustomersCount = 0;
$totalUnreadCount = 0;
$latestOrders = [];
$latestCustomers = [];

try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE created_at > :seen_at
    ");
    $stmt->execute([':seen_at' => $seenAt]);
    $newOrdersCount = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    $newOrdersCount = 0;
}

try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM users
        WHERE created_at > :seen_at
          AND (role = 'customer' OR role IS NULL OR role = '')
    ");
    $stmt->execute([':seen_at' => $seenAt]);
    $newCustomersCount = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    $newCustomersCount = 0;
}

$totalUnreadCount = $newOrdersCount + $newCustomersCount;

try {
    $stmt = $conn->query("
        SELECT 
            o.id,
            o.user_id,
            o.status,
            o.created_at,
            o.grand_total,
            u.name AS customer_name,
            u.email AS customer_email,
            u.phone AS customer_phone,
            u.address AS customer_address,
            u.pincode AS customer_pincode
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        ORDER BY o.id DESC
        LIMIT 10
    ");
    $latestOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latestOrders = [];
}

try {
    $stmt = $conn->query("
        SELECT 
            id,
            name,
            email,
            phone,
            address,
            pincode,
            is_active,
            is_verified,
            created_at
        FROM users
        WHERE role = 'customer' OR role IS NULL OR role = ''
        ORDER BY id DESC
        LIMIT 10
    ");
    $latestCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latestCustomers = [];
}
?>

<style>
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 22px;
        background: #ffffff;
        border-bottom: 1px solid #e9edf3;
        position: sticky;
        top: 0;
        z-index: 1040;
    }

    .topbar-left,
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .topbar-left {
        min-width: 0;
    }

    .menu-btn,
    .icon-btn {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 12px;
        background: #f1f5f9;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .menu-btn:hover,
    .icon-btn:hover {
        background: #e2e8f0;
    }

    .page-title {
        font-weight: 800;
        color: #0f172a;
        white-space: nowrap;
    }

    .topbar-search {
        flex: 1;
        max-width: 420px;
        min-width: 180px;
        position: relative;
    }

    .topbar-search input {
        width: 100%;
        height: 44px;
        border: 1px solid #dbe2ea;
        border-radius: 14px;
        padding: 0 42px 0 14px;
        background: #f8fafc;
        outline: none;
        transition: all 0.2s ease;
    }

    .topbar-search input:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.10);
    }

    .topbar-search i {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }

    .user-btn {
        border: 0;
        background: #f8fafc;
        border-radius: 14px;
        height: 46px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-weight: 600;
    }

    .user-img {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }

    .notification-badge {
        position: absolute;
        top: -4px;
        right: -2px;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
    }

    .notification-menu {
        width: 360px;
        padding: 0;
        border: 0;
        border-radius: 16px;
        overflow: hidden;
    }

    .notification-header {
        padding: 14px 16px;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
        font-weight: 800;
        color: #0f172a;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
    }

    .notification-item:last-child {
        border-bottom: 0;
    }

    .notification-item:hover {
        background: #f8fbff;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notification-icon.order {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .notification-icon.customer {
        background: #dcfce7;
        color: #15803d;
    }

    .notification-title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
        font-size: 0.95rem;
    }

    .notification-text {
        color: #64748b;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .notification-empty {
        padding: 18px 16px;
        color: #64748b;
        text-align: center;
        font-size: 0.9rem;
    }

    .detail-list .row-item {
        padding: 10px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .detail-list .row-item:last-child {
        border-bottom: 0;
    }

    .detail-label {
        font-size: 0.78rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .detail-value {
        color: #111827;
        font-weight: 600;
        word-break: break-word;
    }

    .dark-mode .topbar {
        background: #0f172a;
        border-bottom-color: #1e293b;
    }

    .dark-mode .page-title,
    .dark-mode .user-btn,
    .dark-mode .menu-btn,
    .dark-mode .icon-btn {
        color: #f8fafc;
    }

    .dark-mode .menu-btn,
    .dark-mode .icon-btn,
    .dark-mode .user-btn,
    .dark-mode .topbar-search input {
        background: #1e293b;
        border-color: #334155;
    }

    .dark-mode .topbar-search input {
        color: #f8fafc;
    }

    .dark-mode .topbar-search i {
        color: #94a3b8;
    }

    @media (max-width: 991.98px) {
        .topbar {
            flex-wrap: wrap;
        }

        .topbar-search {
            order: 3;
            width: 100%;
            max-width: 100%;
        }

        .notification-menu {
            width: 320px;
        }
    }

    @media (max-width: 575.98px) {
        .topbar {
            padding: 12px;
        }

        .page-title {
            font-size: 1rem;
        }

        .user-btn span {
            display: none;
        }

        .notification-menu {
            width: 300px;
        }
    }
</style>

<div class="topbar">
    <div class="topbar-left">
        <button class="menu-btn" type="button" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>

        <h5 class="page-title mb-0">Admin Panel</h5>
    </div>

    <!-- <div class="topbar-search">
        <input
            type="text"
            id="searchInput"
            placeholder="Search products..."
            autocomplete="off"
        >
        <i class="fa fa-search"></i>
    </div> -->

    <div class="topbar-right">
        <div class="dropdown">
            <button
                class="icon-btn position-relative"
                id="notificationBell"
                type="button"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
            >
                <i class="fa-regular fa-bell"></i>

                <?php if ($totalUnreadCount > 0): ?>
                    <span class="notification-badge" id="notificationBadge"><?= (int)$totalUnreadCount ?></span>
                <?php endif; ?>
            </button>

            <div class="dropdown-menu dropdown-menu-end shadow notification-menu">
                <div class="notification-header d-flex justify-content-between align-items-center">
                    <span>Notifications</span>
                    <small class="text-muted"><?= (int)$totalUnreadCount ?> unread</small>
                </div>

                <?php if ($newOrdersCount > 0): ?>
                    <div
                        class="notification-item"
                        data-bs-toggle="modal"
                        data-bs-target="#newOrdersModal"
                    >
                        <div class="notification-icon order">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <div>
                            <div class="notification-title">New order received</div>
                            <div class="notification-text">
                                <?= (int)$newOrdersCount ?> new order(s) have been placed in your store.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($newCustomersCount > 0): ?>
                    <div
                        class="notification-item"
                        data-bs-toggle="modal"
                        data-bs-target="#newCustomersModal"
                    >
                        <div class="notification-icon customer">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <div>
                            <div class="notification-title">New customer joined</div>
                            <div class="notification-text">
                                <?= (int)$newCustomersCount ?> new customer(s) joined your business.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($totalUnreadCount === 0): ?>
                    <div class="notification-empty">
                        No new notifications.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <button type="button" onclick="toggleDark()" class="icon-btn" aria-label="Toggle dark mode">
            <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>

        <div class="dropdown">
            <button class="user-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= e($adminImage) ?>" class="user-img" alt="Admin User">
                <span><?= e($adminName) ?></span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <a class="dropdown-item" href="<?= e($profileUrl) ?>">
                        <i class="fa fa-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= e($logoutUrl) ?>">
                        <i class="fa fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="newOrdersModal" tabindex="-1" aria-labelledby="newOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="newOrdersModalLabel">New Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php if (!empty($latestOrders)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Address</th>
                                    <th>Pincode</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestOrders as $order): ?>
                                    <tr>
                                        <td>#<?= (int)$order['id'] ?></td>
                                        <td><?= e($order['customer_name'] ?: 'N/A') ?></td>
                                        <td><?= e($order['customer_email'] ?: 'N/A') ?></td>
                                        <td><?= e($order['customer_phone'] ?: 'N/A') ?></td>
                                        <td>₹<?= number_format((float)($order['grand_total'] ?? 0), 2) ?></td>
                                        <td><?= e(ucfirst($order['status'] ?: 'N/A')) ?></td>
                                        <td><?= e($order['customer_address'] ?: 'N/A') ?></td>
                                        <td><?= e($order['customer_pincode'] ?: 'N/A') ?></td>
                                        <td><?= !empty($order['created_at']) ? e(date('d M Y h:i A', strtotime($order['created_at']))) : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">No order details available.</div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <a href="<?= e($orderListUrl) ?>" class="btn btn-primary">Go to Orders</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newCustomersModal" tabindex="-1" aria-labelledby="newCustomersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="newCustomersModalLabel">New Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php if (!empty($latestCustomers)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Pincode</th>
                                    <th>Status</th>
                                    <th>Verified</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestCustomers as $customer): ?>
                                    <tr>
                                        <td>#<?= (int)$customer['id'] ?></td>
                                        <td><?= e($customer['name'] ?: 'N/A') ?></td>
                                        <td><?= e($customer['email'] ?: 'N/A') ?></td>
                                        <td><?= e($customer['phone'] ?: 'N/A') ?></td>
                                        <td><?= e($customer['address'] ?: 'N/A') ?></td>
                                        <td><?= e($customer['pincode'] ?: 'N/A') ?></td>
                                        <td>
                                            <?php if ((int)$customer['is_active'] === 1): ?>
                                                <span class="badge text-bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)$customer['is_verified'] === 1): ?>
                                                <span class="badge text-bg-primary">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border">Not Verified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($customer['created_at']) ? e(date('d M Y h:i A', strtotime($customer['created_at']))) : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">No customer details available.</div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <a href="<?= e($customerListUrl) ?>" class="btn btn-primary">Go to Customers</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
    }
}

let currentTheme = 'light';

function applyTheme(theme) {
    const body = document.body;
    const icon = document.getElementById('themeIcon');

    if (!body) return;

    body.classList.toggle('dark-mode', theme === 'dark');
    currentTheme = theme;

    if (icon) {
        icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
}

function toggleDark() {
    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
}

window.addEventListener('DOMContentLoaded', function () {
    applyTheme('light');

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const value = this.value.trim();
                window.location.href = '<?= e($productListUrl) ?>' + (value ? '?search=' + encodeURIComponent(value) : '');
            }
        });
    }

    const notificationBell = document.getElementById('notificationBell');
    const notificationBadge = document.getElementById('notificationBadge');

    if (notificationBell) {
        notificationBell.addEventListener('click', function () {
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }

            fetch(window.location.pathname + '?mark_notifications_seen=1', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(function () {});
        });
    }
});
</script>

<?php
if (isset($_GET['mark_notifications_seen']) && $_GET['mark_notifications_seen'] == '1') {
    $_SESSION['admin_notification_seen_at'] = date('Y-m-d H:i:s');
    exit;
}
?>