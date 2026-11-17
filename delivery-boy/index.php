<?php
require_once 'includes/config.php';
checkDeliveryLogin();

if (!isset($_SESSION['splash_shown'])) {
    $_SESSION['splash_shown'] = true;
    header("Location: splash.php");
    exit;
}

$delivery_id = (int)$_SESSION['delivery_id'];
$delivery_name = $_SESSION['delivery_name'] ?? 'Partner';

// Auto-add delivery_boy_status column if missing
try {
    $conn->query("SELECT delivery_boy_status FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_boy_status VARCHAR(50) DEFAULT 'assigned'");
    } catch (\PDOException $e2) {}
}

// Get current availability status
$stmt = $conn->prepare("SELECT is_available FROM delivery_boys WHERE id = ?");
$stmt->execute([$delivery_id]);
$boy = $stmt->fetch();
$is_available = $boy && (int)$boy['is_available'] === 1;

// -------------------------------------------------------------
// ASSIGNED ORDERS: Strictly show only orders assigned by Admin
// -------------------------------------------------------------
$stmtAssigned = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ?
      AND o.status NOT IN ('delivered', 'cancelled', 'returned')
    ORDER BY o.updated_at DESC, o.created_at DESC
");
$stmtAssigned->execute([$delivery_id]);
$assigned_orders = $stmtAssigned->fetchAll();

// -------------------------------------------------------------
// HISTORY ORDERS: Completed deliveries assigned to this delivery boy
// -------------------------------------------------------------
$stmtHistory = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ?
      AND (o.delivery_boy_status = 'delivered' OR o.status = 'delivered')
    ORDER BY o.updated_at DESC, o.id DESC
    LIMIT 50
");
$stmtHistory->execute([$delivery_id]);
$history_orders = $stmtHistory->fetchAll();

$count_assigned = count($assigned_orders);
$count_history = count($history_orders);
?>

<?php include 'includes/header.php'; ?>

<style>
    /* Top Header Section */
    .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 18px;
    }

    .header-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-wrap {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background-color: var(--light-green);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-wrap svg {
        width: 100%;
        height: 100%;
    }

    .user-greeting {
        font-size: 17.5px;
        font-weight: 800;
        color: var(--primary-text);
        line-height: 1.2;
        letter-spacing: -0.2px;
    }

    .user-subtitle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: var(--secondary-text);
        margin-top: 2px;
    }

    .user-subtitle i {
        font-size: 12px;
    }

    /* Mandal Variety Branding */
    .brand-logo-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        text-align: left;
    }

    .brand-names {
        display: flex;
        flex-direction: column;
        line-height: 1.05;
    }

    .brand-main {
        font-size: 15px;
        font-weight: 800;
        color: var(--dark-green);
        letter-spacing: -0.3px;
    }

    .brand-sub {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--primary-green);
        letter-spacing: -0.3px;
    }

    /* Online / Offline Status Card */
    .online-status-card {
        background-color: var(--light-green);
        border-radius: var(--card-radius);
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        transition: background-color 0.3s ease;
    }

    .online-status-card.is-offline {
        background-color: #F1F5F9;
    }

    .status-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .live-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(0, 155, 85, 0.2);
        flex-shrink: 0;
        transition: background-color 0.3s, box-shadow 0.3s;
    }

    .online-status-card.is-offline .live-dot {
        background-color: #94A3B8;
        box-shadow: none;
    }

    .status-heading {
        font-size: 15.5px;
        font-weight: 800;
        color: var(--primary-text);
        line-height: 1.2;
    }

    .status-desc {
        font-size: 12px;
        color: var(--secondary-text);
        font-weight: 500;
        margin-top: 3px;
    }

    /* iOS Style Toggle Switch */
    .ios-switch {
        position: relative;
        display: inline-block;
        width: 52px;
        height: 30px;
        margin: 0;
        flex-shrink: 0;
    }

    .ios-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #CBD5E1;
        transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 30px;
    }

    .switch-slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .ios-switch input:checked + .switch-slider {
        background-color: var(--primary-green);
    }

    .ios-switch input:checked + .switch-slider:before {
        transform: translateX(22px);
    }

    /* Pill Tabs (Assigned / History) */
    .pill-tabs-wrap {
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
    }

    .tab-pill-btn {
        flex: 1;
        padding: 12px 18px;
        border-radius: var(--pill-radius);
        font-size: 14.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid var(--border-color);
        background: #FFFFFF;
        color: var(--secondary-text);
    }

    .tab-pill-btn.active {
        background: var(--primary-green);
        color: #FFFFFF;
        border-color: var(--primary-green);
        box-shadow: 0 4px 12px rgba(0, 155, 85, 0.22);
    }

    .tab-pill-btn i {
        font-size: 14px;
    }

    /* Search & Action Bar */
    .search-actions-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .search-box-pill {
        flex: 1;
        position: relative;
    }

    .search-box-pill .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 14px;
    }

    .search-box-pill .search-input {
        width: 100%;
        height: 44px;
        border-radius: var(--pill-radius);
        border: 1px solid var(--border-color);
        background: #FFFFFF;
        padding-left: 42px;
        padding-right: 16px;
        font-size: 13.5px;
        color: var(--primary-text);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-box-pill .search-input:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(0, 155, 85, 0.12);
    }

    .action-circle-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 15px;
        flex-shrink: 0;
        transition: all 0.2s;
    }

    .action-circle-btn:active {
        transform: scale(0.95);
        background: var(--light-green);
    }

    /* Order Card Component */
    .order-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 18px;
        margin-bottom: 16px;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .order-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
        gap: 12px;
    }

    .order-card-left {
        display: flex;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }

    .order-bag-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: var(--light-green);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .order-main-details {
        flex: 1;
        min-width: 0;
    }

    .order-number {
        font-size: 16px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.2px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .order-info-line {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--primary-text);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .order-info-line i {
        font-size: 12px;
        color: var(--secondary-text);
        width: 14px;
        text-align: center;
        flex-shrink: 0;
    }

    .order-address-line {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 12.5px;
        color: var(--secondary-text);
        line-height: 1.35;
        font-weight: 400;
    }

    .order-address-line i {
        font-size: 12px;
        color: var(--secondary-text);
        width: 14px;
        text-align: center;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .order-card-divider {
        height: 1px;
        background-color: var(--divider-color);
        margin: 14px 0;
    }

    .order-card-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-price-wrap {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .order-price-wrap .currency {
        font-size: 16px;
        font-weight: 800;
        color: var(--primary-text);
    }

    .order-price-wrap .amount {
        font-size: 21px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
    }

    .btn-view-details {
        background: var(--primary-green);
        color: #FFFFFF;
        border: none;
        border-radius: var(--pill-radius);
        padding: 9px 20px;
        font-size: 13.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background-color 0.2s, transform 0.1s;
    }

    .btn-view-details:hover {
        background: var(--dark-green);
        color: #FFFFFF;
    }

    .btn-view-details:active {
        transform: scale(0.97);
    }

    /* Empty States */
    .empty-card-state {
        text-align: center;
        padding: 45px 20px;
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px dashed var(--border-color);
        margin: 20px 0;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--light-green);
        color: var(--primary-green);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 14px;
    }

    .empty-state-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--primary-text);
        margin-bottom: 4px;
    }

    .empty-state-desc {
        font-size: 13px;
        color: var(--secondary-text);
        max-width: 260px;
        margin: 0 auto;
        line-height: 1.4;
    }
</style>

<div class="page-content">

    <!-- 1. Header with Avatar & Mandal Variety Logo -->
    <header class="dash-header">
        <div class="header-user-info">
            <div class="avatar-wrap">
                <!-- Delivery Boy Avatar SVG -->
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="24" cy="24" r="24" fill="#E8F7EF"/>
                    <!-- Head & Cap -->
                    <circle cx="24" cy="18" r="7.5" fill="#FED7AA"/>
                    <!-- Cap -->
                    <path d="M16.5 16C16.5 12 19.8 9.5 24 9.5C28.2 9.5 31.5 12 31.5 16L34 17C34 17 31 18.5 26 18.5C21 18.5 14 17 14 17L16.5 16Z" fill="#008044"/>
                    <!-- Visor -->
                    <path d="M14 17C18 18 23 18.2 27 17.5L28 18.5C23 19.5 17 19 13.5 18L14 17Z" fill="#005A30"/>
                    <!-- Shirt -->
                    <path d="M12.5 38C13 30.5 18 26 24 26C30 26 35 30.5 35.5 38H12.5Z" fill="#009B55"/>
                    <path d="M21 26L24 29L27 26H21Z" fill="#FFFFFF"/>
                </svg>
            </div>
            <div class="user-titles">
                <h1 class="user-greeting">Hello, <?= e($delivery_name) ?></h1>
                <div class="user-subtitle">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Delivery Partner</span>
                </div>
            </div>
        </div>

        <div class="brand-logo-wrap">
            <svg width="32" height="32" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 24C8 13 13 6 18 5C22 4 23 8 21 15C19 22 23 27 28 20C32 14 35 8 36 7" stroke="#009B55" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="35" cy="8" r="2.8" fill="#009B55"/>
            </svg>
            <div class="brand-names">
                <span class="brand-main">Mandal</span>
                <span class="brand-sub">Variety</span>
            </div>
        </div>
    </header>

    <!-- 2. Online / Offline Status Card -->
    <div class="online-status-card <?= $is_available ? '' : 'is-offline' ?>" id="statusCard">
        <div class="status-left">
            <span class="live-dot" id="liveDot"></span>
            <div>
                <div class="status-heading" id="statusHeading"><?= $is_available ? 'You are Online' : 'You are Offline' ?></div>
                <div class="status-desc" id="statusDesc"><?= $is_available ? 'Ready to receive assigned orders' : 'Turn on to receive assigned orders' ?></div>
            </div>
        </div>
        <label class="ios-switch" title="Toggle Online/Offline Status">
            <input type="checkbox" id="statusToggleInput" <?= $is_available ? 'checked' : '' ?> onchange="toggleOnlineStatus(this)">
            <span class="switch-slider"></span>
        </label>
    </div>

    <!-- 3. Tabs: Assigned & History -->
    <div class="pill-tabs-wrap">
        <button type="button" class="tab-pill-btn active" id="tabBtnAssigned" onclick="switchDashboardTab('assigned')">
            <i class="fa-solid fa-bars-staggered"></i>
            <span>Assigned</span>
        </button>
        <button type="button" class="tab-pill-btn" id="tabBtnHistory" onclick="switchDashboardTab('history')">
            <i class="fa-regular fa-clock"></i>
            <span>History</span>
        </button>
    </div>

    <!-- 4. Search and Action Buttons -->
    <div class="search-actions-bar">
        <div class="search-box-pill">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="orderSearchInput" class="search-input" placeholder="Search by Order ID..." oninput="filterOrdersLive(this.value)">
        </div>
        <button type="button" class="action-circle-btn" title="Refresh Orders" onclick="window.location.reload()">
            <i class="fa-solid fa-arrows-rotate"></i>
        </button>
    </div>

    <!-- 5. Assigned Orders Container -->
    <div id="sectionAssignedOrders">
        <?php if (!empty($assigned_orders)): ?>
            <?php foreach ($assigned_orders as $order): ?>
                <?php
                    $order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
                    $cust_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
                    $total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                    $address = $order['delivery_address'] ?? $order['shipping_address'] ?? $order['address'] ?? 'Customer Address';
                    $pincode = $order['pincode'] ?? $order['shipping_pincode'] ?? '';
                    $address_short = trim(explode("\n", $address)[0]);
                    if ($pincode && strpos($address_short, $pincode) === false) {
                        $address_short .= ', ' . $pincode;
                    }
                    $status_badge_text = 'Assigned';
                    if (!empty($order['delivery_boy_status'])) {
                        $db_st = $order['delivery_boy_status'];
                        if ($db_st === 'picked_up') $status_badge_text = 'Picked Up';
                        elseif ($db_st === 'out_for_delivery') $status_badge_text = 'Out for Delivery';
                        elseif ($db_st === 'arrived') $status_badge_text = 'Arrived';
                    }
                ?>
                <div class="order-card order-item-card" data-order-number="<?= strtolower(e($order_no)) ?>" data-customer-name="<?= strtolower(e($cust_name)) ?>">
                    <div class="order-card-top">
                        <div class="order-card-left">
                            <div class="order-bag-icon">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                            <div class="order-main-details">
                                <h3 class="order-number">#<?= e($order_no) ?></h3>
                                <div class="order-info-line">
                                    <i class="fa-solid fa-user"></i>
                                    <span><?= e($cust_name) ?></span>
                                </div>
                                <div class="order-address-line">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>2.4 km &bull; <?= e($address_short) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="badge-status">
                            <span class="status-dot"></span>
                            <span><?= e($status_badge_text) ?></span>
                        </div>
                    </div>

                    <div class="order-card-divider"></div>

                    <div class="order-card-bottom">
                        <div class="order-price-wrap">
                            <span class="currency">₹</span>
                            <span class="amount"><?= number_format($total, 0) ?></span>
                        </div>
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-view-details">
                            <span>View Details</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-card-state" id="emptyAssignedBox">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h4 class="empty-state-title">No Assigned Orders</h4>
                <p class="empty-state-desc">You are all caught up! New orders assigned by admin will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- 6. History Orders Container -->
    <div id="sectionHistoryOrders" style="display: none;">
        <?php if (!empty($history_orders)): ?>
            <?php foreach ($history_orders as $order): ?>
                <?php
                    $order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
                    $cust_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
                    $total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                    $address = $order['delivery_address'] ?? $order['shipping_address'] ?? $order['address'] ?? 'Customer Address';
                    $pincode = $order['pincode'] ?? $order['shipping_pincode'] ?? '';
                    $address_short = trim(explode("\n", $address)[0]);
                    if ($pincode && strpos($address_short, $pincode) === false) {
                        $address_short .= ', ' . $pincode;
                    }
                ?>
                <div class="order-card order-item-card" data-order-number="<?= strtolower(e($order_no)) ?>" data-customer-name="<?= strtolower(e($cust_name)) ?>">
                    <div class="order-card-top">
                        <div class="order-card-left">
                            <div class="order-bag-icon" style="background:#DCFCE7; color:#15803D;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div class="order-main-details">
                                <h3 class="order-number">#<?= e($order_no) ?></h3>
                                <div class="order-info-line">
                                    <i class="fa-solid fa-user"></i>
                                    <span><?= e($cust_name) ?></span>
                                </div>
                                <div class="order-address-line">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span><?= e($address_short) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="badge-status" style="background:#DCFCE7; color:#15803D;">
                            <span class="status-dot" style="background-color:#15803D;"></span>
                            <span>Delivered</span>
                        </div>
                    </div>

                    <div class="order-card-divider"></div>

                    <div class="order-card-bottom">
                        <div class="order-price-wrap">
                            <span class="currency">₹</span>
                            <span class="amount"><?= number_format($total, 0) ?></span>
                        </div>
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-view-details" style="background:#475569;">
                            <span>View Summary</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-card-state" id="emptyHistoryBox">
                <div class="empty-state-icon" style="background:#F1F5F9; color:#94A3B8;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h4 class="empty-state-title">No Delivery History</h4>
                <p class="empty-state-desc">Your completed deliveries will be listed here for quick review.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
// Online / Offline Status Toggle via AJAX
function toggleOnlineStatus(switchElem) {
    const isChecked = switchElem.checked;
    const newStatus = isChecked ? 1 : 0;

    // Optimistic UI updates
    updateStatusCardUI(isChecked);

    const formData = new FormData();
    formData.append('status', newStatus);
    formData.append('ajax', 1);

    fetch('toggle_status.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMvToast(data.message || (isChecked ? 'You are now Online' : 'You are now Offline'), true);
        } else {
            // Revert switch on error
            switchElem.checked = !isChecked;
            updateStatusCardUI(!isChecked);
            showMvToast(data.message || 'Failed to update status', false);
        }
    })
    .catch(() => {
        // Fallback or network error
        switchElem.checked = !isChecked;
        updateStatusCardUI(!isChecked);
        showMvToast('Network connection error', false);
    });
}

function updateStatusCardUI(isOnline) {
    const card = document.getElementById('statusCard');
    const heading = document.getElementById('statusHeading');
    const desc = document.getElementById('statusDesc');

    if (isOnline) {
        card.classList.remove('is-offline');
        heading.textContent = 'You are Online';
        desc.textContent = 'Ready to receive assigned orders';
    } else {
        card.classList.add('is-offline');
        heading.textContent = 'You are Offline';
        desc.textContent = 'Turn on to receive assigned orders';
    }
}

// Switch between Assigned and History tabs
function switchDashboardTab(tabName) {
    const btnAssigned = document.getElementById('tabBtnAssigned');
    const btnHistory = document.getElementById('tabBtnHistory');
    const secAssigned = document.getElementById('sectionAssignedOrders');
    const secHistory = document.getElementById('sectionHistoryOrders');

    if (tabName === 'history') {
        btnAssigned.classList.remove('active');
        btnHistory.classList.add('active');
        secAssigned.style.display = 'none';
        secHistory.style.display = 'block';
    } else {
        btnHistory.classList.remove('active');
        btnAssigned.classList.add('active');
        secHistory.style.display = 'none';
        secAssigned.style.display = 'block';
    }

    // Re-apply search filter
    const searchVal = document.getElementById('orderSearchInput').value;
    if (searchVal) {
        filterOrdersLive(searchVal);
    }
}

// Live search filter across visible cards
function filterOrdersLive(query) {
    const term = (query || '').trim().toLowerCase();
    const activeSection = document.getElementById('sectionAssignedOrders').style.display !== 'none'
        ? document.getElementById('sectionAssignedOrders')
        : document.getElementById('sectionHistoryOrders');

    const cards = activeSection.querySelectorAll('.order-item-card');
    cards.forEach(card => {
        const orderNo = card.getAttribute('data-order-number') || '';
        const custName = card.getAttribute('data-customer-name') || '';
        if (orderNo.includes(term) || custName.includes(term)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
