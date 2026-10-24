<?php
require_once 'includes/config.php';
checkDeliveryLogin();

if (!isset($_SESSION['splash_shown'])) {
    $_SESSION['splash_shown'] = true;
    header("Location: splash.php");
    exit;
}

$delivery_id = (int)$_SESSION['delivery_id'];
$delivery_name = $_SESSION['delivery_name'];

// Auto-add delivery_boy_status column if missing
try {
    $conn->query("SELECT delivery_boy_status FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_boy_status VARCHAR(50) DEFAULT 'assigned'");
    } catch (\PDOException $e2) {}
}

// Toggle availability status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability'])) {
    $new_status = isset($_POST['is_available']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE delivery_boys SET is_available = ? WHERE id = ?");
    $stmt->execute([$new_status, $delivery_id]);
    header("Location: index.php");
    exit;
}

// Get current availability
$stmt = $conn->prepare("SELECT is_available FROM delivery_boys WHERE id = ?");
$stmt->execute([$delivery_id]);
$boy = $stmt->fetch();
$is_available = $boy && (int)$boy['is_available'] === 1;

// -------------------------------------------------------------
// SECTION 1: NEW DELIVERIES (Available for acceptance)
// Either assigned to this delivery boy but not yet accepted, OR unassigned pending/confirmed/preparing orders
// -------------------------------------------------------------
$stmtNew = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE (
        (o.assigned_delivery_id IS NULL AND o.status IN ('pending', 'confirmed', 'preparing', 'shipped', 'out_for_delivery'))
        OR 
        (o.assigned_delivery_id = ? AND (o.delivery_boy_status = 'assigned' OR o.delivery_boy_status IS NULL OR o.delivery_boy_status = ''))
    )
    AND o.status NOT IN ('delivered', 'cancelled', 'returned')
    ORDER BY o.created_at DESC
");
$stmtNew->execute([$delivery_id]);
$new_orders = $stmtNew->fetchAll();

// -------------------------------------------------------------
// SECTION 2: ACCEPTED / ASSIGNED DELIVERIES (Active orders assigned to this delivery boy)
// -------------------------------------------------------------
$stmtAccepted = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ?
      AND (
          o.delivery_boy_status IN ('accepted', 'picked_up', 'out_for_delivery', 'arrived', 'payment_pending', 'proof_pending')
          OR (o.delivery_boy_status IS NULL AND o.status IN ('shipped', 'out_for_delivery'))
      )
      AND o.status NOT IN ('delivered', 'cancelled', 'returned')
    ORDER BY o.updated_at DESC, o.created_at DESC
");
$stmtAccepted->execute([$delivery_id]);
$accepted_orders = $stmtAccepted->fetchAll();

// -------------------------------------------------------------
// SECTION 3: DELIVERED DELIVERIES (Completed deliveries)
// -------------------------------------------------------------
$stmtDelivered = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ?
      AND (o.delivery_boy_status = 'delivered' OR o.status = 'delivered')
    ORDER BY o.updated_at DESC, o.id DESC
    LIMIT 50
");
$stmtDelivered->execute([$delivery_id]);
$delivered_orders = $stmtDelivered->fetchAll();

// Statistics counts
$count_new = count($new_orders);
$count_accepted = count($accepted_orders);
$count_delivered = count($delivered_orders);

?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f6fbfa;
    }

    /* Abstract background banner */
    .dashboard-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 250px;
        background-image: url('data:image/svg+xml;utf8,<svg width="400" height="200" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg"><path d="M 50 150 Q 80 100 120 130 Q 170 80 230 110 Q 280 60 340 100 Q 380 70 420 120 L 420 200 L 0 200 L 0 150" fill="%23e8f7f0" opacity="0.75"/></svg>');
        background-size: cover;
        background-position: center top;
        z-index: 0;
    }

    .dashboard-container {
        padding: 24px 20px;
        position: relative;
        z-index: 1;
    }

    /* Top Greeting & Header */
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .avatar-circle {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #cfebe0;
        border: 2.5px solid #ffffff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .avatar-circle svg {
        width: 100%;
        height: 100%;
    }

    .greeting-text {
        display: flex;
        flex-direction: column;
    }

    .driver-name {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
        line-height: 1.2;
    }

    .driver-role {
        font-size: 13px;
        color: #4b5563;
        font-weight: 500;
        margin-top: 2px;
    }

    .header-right {
        position: relative;
    }

    .bell-btn {
        width: 44px;
        height: 44px;
        background: #ffffff;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4b5563;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.03);
        position: relative;
        text-decoration: none;
    }
    .bell-btn i {
        font-size: 20px;
    }

    .notification-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 800;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
    }

    /* Green Online/Offline Card */
    .toggle-card {
        background: var(--mandal-green);
        border-radius: 20px;
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        box-shadow: 0 10px 25px rgba(7, 161, 88, 0.22);
        transition: all 0.3s ease;
    }
    .toggle-card.offline {
        background: #64748b;
        box-shadow: 0 10px 25px rgba(100, 116, 139, 0.22);
    }

    .toggle-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 0 10px rgba(255,255,255,0.8);
    }
    .toggle-card.offline .status-dot {
        background: #cbd5e1;
        box-shadow: none;
    }

    .toggle-text {
        color: white;
        font-size: 20px;
        font-weight: 800;
        line-height: 1;
    }
    .toggle-sub {
        color: rgba(255,255,255,0.85);
        font-size: 12px;
        font-weight: 500;
        margin-top: 4px;
    }

    /* Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 58px;
        height: 32px;
        margin: 0;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(255, 255, 255, 0.35);
        transition: .3s;
        border-radius: 32px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.25);
    }
    input:checked + .slider {
        background-color: rgba(255,255,255,0.45);
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }

    /* Delivery Statistics */
    .stats-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 22px;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 16px 8px;
        flex: 1;
        text-align: center;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-number {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
        margin-bottom: 6px;
    }
    .stat-number.green { color: var(--mandal-green); }
    .stat-number.blue { color: #2563eb; }
    .stat-number.amber { color: #f59e0b; }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Filter Tabs / Segmented Buttons */
    .filters-container {
        margin-bottom: 20px;
    }
    .filters-tabs {
        display: flex;
        background: #ffffff;
        padding: 6px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.02);
    }
    .filter-tab {
        flex: 1;
        padding: 10px 4px;
        text-align: center;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        text-decoration: none;
    }
    .filter-tab .badge-count {
        font-size: 11px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
        transition: all 0.25s ease;
    }
    .filter-tab.active {
        background: var(--mandal-green);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(7, 161, 88, 0.28);
    }
    .filter-tab.active .badge-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Delivery Order Cards */
    .delivery-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.04);
        margin-bottom: 18px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .delivery-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    }

    .card-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .order-id-badge {
        font-size: 17px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.3px;
    }

    .status-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-badge.new { background: #dbeafe; color: #1e40af; }
    .status-badge.accepted { background: #ede9fe; color: #5b21b6; }
    .status-badge.picked { background: #e0e7ff; color: #3730a3; }
    .status-badge.out { background: #cffafe; color: #0e7490; }
    .status-badge.arrived { background: #fef3c7; color: #b45309; }
    .status-badge.delivered { background: #dcfce7; color: #15803d; }

    .payment-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
        margin-left: 8px;
    }
    .payment-badge.cod {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .payment-badge.paid {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 14.5px;
        color: #334155;
        font-weight: 500;
    }
    .info-icon {
        color: var(--mandal-green);
        width: 18px;
        text-align: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .customer-name {
        font-weight: 700;
        color: var(--text-dark);
    }
    .amount-text {
        color: var(--text-dark);
        font-weight: 800;
        font-size: 16px;
    }
    .location-text {
        font-size: 13.5px;
        color: #475569;
        line-height: 1.4;
    }
    .time-text {
        font-size: 12px;
        color: var(--text-muted);
    }

    .card-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 16px 0;
    }

    .card-actions-row {
        display: flex;
        gap: 10px;
        margin-top: 14px;
    }

    .btn-action-primary {
        background: var(--mandal-green);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 13px 16px;
        font-size: 14px;
        font-weight: 700;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(7, 161, 88, 0.2);
    }
    .btn-action-primary:hover {
        background: var(--mandal-green-dark);
        color: white;
        transform: translateY(-1px);
    }
    .btn-action-primary:active {
        transform: translateY(1px);
    }
    .btn-action-primary:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .btn-action-outline {
        background: #ffffff;
        color: #334155;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        padding: 13px 16px;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-action-outline:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: var(--text-dark);
    }

    /* Empty State */
    .empty-state-box {
        background: #ffffff;
        border-radius: 20px;
        padding: 46px 20px;
        text-align: center;
        border: 1.5px dashed #cbd5e1;
        box-shadow: 0 4px 16px rgba(0,0,0,0.02);
        margin-bottom: 24px;
    }
    .empty-icon-wrap {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 30px;
        margin-bottom: 16px;
    }
    .empty-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 6px;
    }
    .empty-subtitle {
        font-size: 13.5px;
        color: var(--text-muted);
        max-width: 280px;
        margin: 0 auto;
        line-height: 1.45;
    }

    /* Floating Toast Alerts */
    .toast-feedback {
        position: fixed;
        bottom: 84px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #1e293b;
        color: white;
        padding: 12px 20px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        z-index: 1050;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        max-width: 90%;
    }
    .toast-feedback.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }
    .toast-feedback.toast-success { background: #059669; }
    .toast-feedback.toast-error { background: #dc2626; }
</style>

<div class="dashboard-bg"></div>

<div class="dashboard-container">
    
    <!-- 1. Delivery Boy Greeting & Profile -->
    <div class="header-row">
        <div class="header-left">
            <div class="avatar-circle">
                <svg width="44" height="44" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50" fill="#a7f3d0"/>
                    <circle cx="50" cy="40" r="20" fill="#fcd34d"/>
                    <path d="M 25 90 Q 50 60 75 90 Z" fill="#059669"/>
                    <path d="M 35 25 Q 50 10 65 25 Z" fill="#064e3b"/>
                </svg>
            </div>
            <div class="greeting-text">
                <h1 class="driver-name">Hello, <?= e($delivery_name) ?></h1>
                <span class="driver-role"><i class="fa-solid fa-id-badge me-1"></i> Delivery Partner</span>
            </div>
        </div>
        <div class="header-right">
            <a href="orders.php" class="bell-btn" title="Orders">
                <i class="fa-solid fa-bell"></i>
                <?php if ($count_new > 0): ?>
                    <span class="notification-badge" id="headerNotifBadge"><?= $count_new ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- 2. Online / Offline Toggle Card -->
    <div class="toggle-card <?= $is_available ? '' : 'offline' ?>" id="toggleCard">
        <div class="toggle-info">
            <span class="status-dot"></span>
            <div>
                <div class="toggle-text"><?= $is_available ? 'You are Online' : 'You are Offline' ?></div>
                <div class="toggle-sub"><?= $is_available ? 'Ready to accept deliveries' : 'Turn on to receive new orders' ?></div>
            </div>
        </div>
        
        <form method="POST" id="availabilityForm" class="m-0">
            <input type="hidden" name="toggle_availability" value="1">
            <label class="switch" title="Toggle Availability">
                <input type="checkbox" name="is_available" value="1" id="availabilitySwitch" <?= $is_available ? 'checked' : '' ?> onchange="document.getElementById('availabilityForm').submit()">
                <span class="slider"></span>
            </label>
        </form>
    </div>

    <!-- 3. Delivery Statistics -->
    <div class="stats-row">
        <div class="stat-card" onclick="switchFilterTab('new')" style="cursor:pointer;">
            <div class="stat-number blue" id="statNewCount"><?= $count_new ?></div>
            <div class="stat-label">New</div>
        </div>
        <div class="stat-card" onclick="switchFilterTab('accepted')" style="cursor:pointer;">
            <div class="stat-number amber" id="statAcceptedCount"><?= $count_accepted ?></div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card" onclick="switchFilterTab('delivered')" style="cursor:pointer;">
            <div class="stat-number green" id="statDeliveredCount"><?= $count_delivered ?></div>
            <div class="stat-label">Delivered</div>
        </div>
    </div>

    <!-- 4. Three Clear Home Page Filters -->
    <div class="filters-container">
        <div class="filters-tabs" role="tablist">
            <button type="button" class="filter-tab active" id="tabBtnNew" onclick="switchFilterTab('new')">
                <span>New</span>
                <span class="badge-count" id="badgeNew"><?= $count_new ?></span>
            </button>
            <button type="button" class="filter-tab" id="tabBtnAccepted" onclick="switchFilterTab('accepted')">
                <span>Accepted</span>
                <span class="badge-count" id="badgeAccepted"><?= $count_accepted ?></span>
            </button>
            <button type="button" class="filter-tab" id="tabBtnDelivered" onclick="switchFilterTab('delivered')">
                <span>Delivered</span>
                <span class="badge-count" id="badgeDelivered"><?= $count_delivered ?></span>
            </button>
        </div>
    </div>

    <!-- 5. Delivery Lists for Selected Section -->

    <!-- ======================================================== -->
    <!-- SECTION 1: NEW DELIVERIES -->
    <!-- ======================================================== -->
    <div id="sectionNew" class="delivery-section">
        <?php if (!empty($new_orders)): ?>
            <?php foreach ($new_orders as $order): ?>
                <?php
                    $order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
                    $cust_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
                    $total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                    $pm = strtolower($order['payment_method'] ?? $order['payment_type'] ?? '');
                    $is_cod = (strpos($pm, 'cash') !== false || strpos($pm, 'cod') !== false);
                    $address = $order['delivery_address'] ?? $order['shipping_address'] ?? $order['address'] ?? 'Customer Address';
                    $pincode = $order['pincode'] ?? $order['shipping_pincode'] ?? '';
                    $address_short = trim(explode("\n", $address)[0]);
                    if ($pincode) $address_short .= ' (' . $pincode . ')';
                ?>
                <div class="delivery-card" id="card-order-<?= (int)$order['id'] ?>" data-order-id="<?= (int)$order['id'] ?>">
                    <div class="card-header-row">
                        <span class="order-id-badge">#<?= e($order_no) ?></span>
                        <span class="status-badge new"><i class="fa-solid fa-bell"></i> New Request</span>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-user info-icon"></i>
                        <span class="customer-name"><?= e($cust_name) ?></span>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-indian-rupee-sign info-icon"></i>
                        <span class="amount-text">₹<?= number_format($total, 0) ?></span>
                        <?php if ($is_cod): ?>
                            <span class="payment-badge cod">COD</span>
                        <?php else: ?>
                            <span class="payment-badge paid">PAID</span>
                        <?php endif; ?>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-location-dot info-icon"></i>
                        <span class="location-text">2.4 km &bull; <?= e($address_short) ?></span>
                    </div>

                    <div class="card-actions-row">
                        <button type="button" class="btn-action-primary" onclick="acceptOrder(<?= (int)$order['id'] ?>, this)">
                            <i class="fa-solid fa-check"></i>
                            <span>Accept Delivery</span>
                        </button>
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-action-outline">
                            <span>Details</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-box">
                <div class="empty-icon-wrap">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div class="empty-title">No New Delivery Requests</div>
                <div class="empty-subtitle">New orders available for acceptance will appear here automatically.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: ACCEPTED / ASSIGNED DELIVERIES -->
    <!-- ======================================================== -->
    <div id="sectionAccepted" class="delivery-section d-none">
        <?php if (!empty($accepted_orders)): ?>
            <?php foreach ($accepted_orders as $order): ?>
                <?php
                    $order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
                    $cust_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
                    $cust_phone = $order['user_phone'] ?? $order['customer_phone'] ?? '';
                    $total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                    $pm = strtolower($order['payment_method'] ?? $order['payment_type'] ?? '');
                    $is_cod = (strpos($pm, 'cash') !== false || strpos($pm, 'cod') !== false);
                    $address = $order['delivery_address'] ?? $order['shipping_address'] ?? $order['address'] ?? 'Customer Address';
                    $pincode = $order['pincode'] ?? $order['shipping_pincode'] ?? '';
                    $address_short = trim(explode("\n", $address)[0]);
                    if ($pincode) $address_short .= ' (' . $pincode . ')';

                    $order_state = $order['delivery_boy_status'] ?? 'accepted';
                    $status_badge_class = 'accepted';
                    $status_badge_label = 'Accepted';
                    $next_action_label = 'Pick Up Order';
                    $next_action_val = 'pickup';
                    $next_action_icon = 'fa-solid fa-box';

                    if ($order_state === 'picked_up') {
                        $status_badge_class = 'picked';
                        $status_badge_label = 'Picked Up';
                        $next_action_label = 'Out for Delivery';
                        $next_action_val = 'out_for_delivery';
                        $next_action_icon = 'fa-solid fa-motorcycle';
                    } elseif ($order_state === 'out_for_delivery') {
                        $status_badge_class = 'out';
                        $status_badge_label = 'Out for Delivery';
                        $next_action_label = 'Confirm Arrival';
                        $next_action_val = 'confirm_arrival';
                        $next_action_icon = 'fa-solid fa-location-dot';
                    } elseif ($order_state === 'arrived') {
                        $status_badge_class = 'arrived';
                        $status_badge_label = 'Arrived';
                        $next_action_label = 'Customer OTP';
                        $next_action_val = 'view_details';
                        $next_action_icon = 'fa-solid fa-shield-halved';
                    } elseif ($order_state === 'payment_pending') {
                        $status_badge_class = 'arrived';
                        $status_badge_label = 'Collect Payment';
                        $next_action_label = 'Collect Payment';
                        $next_action_val = 'view_details';
                        $next_action_icon = 'fa-solid fa-money-bill-wave';
                    } elseif ($order_state === 'proof_pending') {
                        $status_badge_class = 'arrived';
                        $status_badge_label = 'Proof Pending';
                        $next_action_label = 'Submit Proof';
                        $next_action_val = 'view_details';
                        $next_action_icon = 'fa-solid fa-camera';
                    }
                ?>
                <div class="delivery-card" id="card-order-<?= (int)$order['id'] ?>" data-order-id="<?= (int)$order['id'] ?>">
                    <div class="card-header-row">
                        <span class="order-id-badge">#<?= e($order_no) ?></span>
                        <span class="status-badge <?= $status_badge_class ?>"><?= $status_badge_label ?></span>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-user info-icon"></i>
                        <span class="customer-name"><?= e($cust_name) ?></span>
                        <?php if ($cust_phone): ?>
                            <a href="tel:<?= e($cust_phone) ?>" class="ms-auto text-primary" style="font-size:13px; font-weight:700;"><i class="fa-solid fa-phone me-1"></i>Call</a>
                        <?php endif; ?>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-indian-rupee-sign info-icon"></i>
                        <span class="amount-text">₹<?= number_format($total, 0) ?></span>
                        <?php if ($is_cod): ?>
                            <span class="payment-badge cod">COD</span>
                        <?php else: ?>
                            <span class="payment-badge paid">PAID</span>
                        <?php endif; ?>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-location-dot info-icon"></i>
                        <span class="location-text"><?= e($address_short) ?></span>
                    </div>

                    <div class="card-actions-row">
                        <?php if ($next_action_val === 'view_details'): ?>
                            <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-action-primary">
                                <i class="<?= $next_action_icon ?>"></i>
                                <span><?= $next_action_label ?></span>
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn-action-primary" onclick="updateOrderStatus(<?= (int)$order['id'] ?>, '<?= $next_action_val ?>', this)">
                                <i class="<?= $next_action_icon ?>"></i>
                                <span><?= $next_action_label ?></span>
                            </button>
                        <?php endif; ?>
                        
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-action-outline">
                            <span>Details</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-box">
                <div class="empty-icon-wrap">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <div class="empty-title">You Have No Active Deliveries</div>
                <div class="empty-subtitle">Deliveries you accept will appear here ready for pickup and delivery.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 3: DELIVERED DELIVERIES -->
    <!-- ======================================================== -->
    <div id="sectionDelivered" class="delivery-section d-none">
        <?php if (!empty($delivered_orders)): ?>
            <?php foreach ($delivered_orders as $order): ?>
                <?php
                    $order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
                    $cust_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
                    $total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                    $pm = strtolower($order['payment_method'] ?? $order['payment_type'] ?? '');
                    $is_cod = (strpos($pm, 'cash') !== false || strpos($pm, 'cod') !== false);
                    $delivered_date = !empty($order['updated_at']) ? date('d M Y, h:i A', strtotime($order['updated_at'])) : (!empty($order['created_at']) ? date('d M Y, h:i A', strtotime($order['created_at'])) : 'Completed');
                ?>
                <div class="delivery-card" id="card-order-<?= (int)$order['id'] ?>">
                    <div class="card-header-row">
                        <span class="order-id-badge">#<?= e($order_no) ?></span>
                        <span class="status-badge delivered"><i class="fa-solid fa-circle-check"></i> Delivered</span>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-user info-icon"></i>
                        <span class="customer-name"><?= e($cust_name) ?></span>
                    </div>

                    <div class="info-row">
                        <i class="fa-solid fa-indian-rupee-sign info-icon"></i>
                        <span class="amount-text">₹<?= number_format($total, 0) ?></span>
                        <?php if ($is_cod): ?>
                            <span class="payment-badge cod">COD (Collected)</span>
                        <?php else: ?>
                            <span class="payment-badge paid">PAID</span>
                        <?php endif; ?>
                    </div>

                    <div class="info-row">
                        <i class="fa-regular fa-clock info-icon"></i>
                        <span class="time-text">Delivered on <?= e($delivered_date) ?></span>
                    </div>

                    <div class="card-actions-row">
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-action-outline w-100">
                            <i class="fa-solid fa-receipt me-1"></i>
                            <span>View Summary</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-box">
                <div class="empty-icon-wrap">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div class="empty-title">No Completed Deliveries Yet</div>
                <div class="empty-subtitle">Orders you complete will be listed here for your records.</div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Feedback Toast Element -->
<div class="toast-feedback" id="feedbackToast">
    <i class="fa-solid fa-circle-check" id="toastIcon"></i>
    <span id="toastMessage">Action completed</span>
</div>

<script>
    // Tab switching logic with persistence
    function switchFilterTab(tabName) {
        // Save preference in localStorage
        try {
            localStorage.setItem('delivery_boy_active_tab', tabName);
        } catch(e) {}

        // Update Tab Buttons
        const tabs = ['new', 'accepted', 'delivered'];
        tabs.forEach(t => {
            const btn = document.getElementById('tabBtn' + capitalizeFirstLetter(t));
            const sec = document.getElementById('section' + capitalizeFirstLetter(t));
            if (t === tabName) {
                if (btn) btn.classList.add('active');
                if (sec) sec.classList.remove('d-none');
            } else {
                if (btn) btn.classList.remove('active');
                if (sec) sec.classList.add('d-none');
            }
        });
    }

    function capitalizeFirstLetter(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Restore selected tab on screen load
    document.addEventListener('DOMContentLoaded', () => {
        let savedTab = 'new';
        try {
            const stored = localStorage.getItem('delivery_boy_active_tab');
            if (stored && ['new', 'accepted', 'delivered'].includes(stored)) {
                savedTab = stored;
            }
        } catch(e) {}

        // Check if URL has tab hash or query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const queryTab = urlParams.get('tab');
        if (queryTab && ['new', 'accepted', 'delivered'].includes(queryTab)) {
            savedTab = queryTab;
        }

        switchFilterTab(savedTab);
    });

    // Toast alert helper
    let toastTimeout = null;
    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('feedbackToast');
        const msg = document.getElementById('toastMessage');
        const icon = document.getElementById('toastIcon');

        msg.innerText = message;
        toast.className = 'toast-feedback ' + (isSuccess ? 'toast-success' : 'toast-error');
        icon.className = isSuccess ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';

        toast.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // Accept Order with Concurrency Check
    function acceptOrder(orderId, btnElement) {
        if (!orderId || !btnElement) return;

        btnElement.disabled = true;
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Accepting...';

        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('action', 'accept');

        fetch('ajax_order_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Order accepted successfully!', true);

                // Animate card removal from New Deliveries
                const card = document.getElementById('card-order-' + orderId);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        card.remove();
                        // Refresh page and switch to Accepted tab to view updated orders
                        localStorage.setItem('delivery_boy_active_tab', 'accepted');
                        window.location.href = 'index.php?tab=accepted';
                    }, 350);
                } else {
                    localStorage.setItem('delivery_boy_active_tab', 'accepted');
                    window.location.href = 'index.php?tab=accepted';
                }
            } else {
                showToast(data.message || 'Failed to accept order.', false);
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;

                // If already accepted by someone else, remove it from list after notice
                if (data.code === 'already_accepted') {
                    const card = document.getElementById('card-order-' + orderId);
                    if (card) {
                        setTimeout(() => {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
                        }, 1200);
                    }
                }
            }
        })
        .catch(() => {
            showToast('Network error while accepting order. Please try again.', false);
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        });
    }

    // Update Order Status (Pickup, Out for Delivery, Confirm Arrival)
    function updateOrderStatus(orderId, actionType, btnElement) {
        if (!orderId || !actionType || !btnElement) return;

        btnElement.disabled = true;
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...';

        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('action', actionType);

        fetch('ajax_order_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Status updated successfully!', true);
                setTimeout(() => {
                    window.location.href = 'index.php?tab=accepted';
                }, 400);
            } else {
                showToast(data.message || 'Failed to update order status.', false);
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
        })
        .catch(() => {
            showToast('Network error while updating status.', false);
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        });
    }

    // Availability form handling
    document.getElementById('availabilityForm').addEventListener('submit', function(e) {
        const cb = document.getElementById('availabilitySwitch');
        if (!cb.checked) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'is_available';
            hidden.value = '0';
            this.appendChild(hidden);
        }
    });

    // Pull-to-refresh
    let touchstartY = 0;
    let touchendY = 0;
    document.addEventListener('touchstart', e => {
        touchstartY = e.changedTouches[0].screenY;
    }, { passive: true });

    document.addEventListener('touchend', e => {
        touchendY = e.changedTouches[0].screenY;
        if (window.scrollY === 0 && (touchendY - touchstartY) > 120) {
            window.location.reload();
        }
    }, { passive: true });
</script>

<?php include 'includes/footer.php'; ?>
