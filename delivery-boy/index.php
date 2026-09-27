<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$delivery_name = $_SESSION['delivery_name'];

// Toggle availability status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability'])) {
    $new_status = (int)$_POST['is_available'];
    $stmt = $conn->prepare("UPDATE delivery_boys SET is_available = ? WHERE id = ?");
    $stmt->execute([$new_status, $delivery_id]);
    header("Location: index.php");
    exit;
}

// Get current availability
$stmt = $conn->prepare("SELECT is_available FROM delivery_boys WHERE id = ?");
$stmt->execute([$delivery_id]);
$boy = $stmt->fetch();
$is_available = (int)$boy['is_available'] === 1;

// Fetch assigned active orders
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ? AND o.status NOT IN ('delivered', 'cancelled', 'returned')
    ORDER BY o.created_at DESC
");
$stmt->execute([$delivery_id]);
$active_orders = $stmt->fetchAll();

// Fetch status counts
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM orders WHERE assigned_delivery_id = ? GROUP BY status");
$stmt->execute([$delivery_id]);
$status_counts = [];
while($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}

// Define the stats
$assigned_count = ($status_counts['assigned'] ?? 0) + ($status_counts['pending'] ?? 0);
$picked_count = ($status_counts['out_for_delivery'] ?? 0) + ($status_counts['shipped'] ?? 0) + ($status_counts['processing'] ?? 0);
$delivered_count = $status_counts['delivered'] ?? 0;

?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f6fbfa; /* Match the faint greenish background from mockup */
    }

    /* Abstract cloud background */
    .dashboard-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 250px;
        background-image: url('data:image/svg+xml;utf8,<svg width="400" height="200" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg"><path d="M 50 150 Q 80 100 120 130 Q 170 80 230 110 Q 280 60 340 100 Q 380 70 420 120 L 420 200 L 0 200 L 0 150" fill="%23e8f7f0" opacity="0.7"/></svg>');
        background-size: cover;
        background-position: center top;
        z-index: 0;
    }

    .dashboard-container {
        padding: 24px 20px;
        position: relative;
        z-index: 1;
    }

    /* Top Header */
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #cfebe0;
        border: 2px solid #ffffff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
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
        font-size: 14px;
        color: #4b5563;
        font-weight: 500;
    }

    .header-right {
        position: relative;
    }

    .bell-icon {
        font-size: 24px;
        color: #4b5563;
        position: relative;
    }

    .notification-badge {
        position: absolute;
        top: -4px;
        right: -6px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 700;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
    }

    /* Green Toggle Card */
    .toggle-card {
        background: var(--mandal-green);
        border-radius: 20px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(7, 161, 88, 0.2);
    }

    .toggle-text {
        color: white;
        font-size: 24px;
        font-weight: 800;
    }

    /* Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 64px;
        height: 36px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.3);
        transition: .4s;
        border-radius: 36px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 28px;
        width: 28px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    input:checked + .slider {
        background-color: rgba(255,255,255,0.4);
    }
    input:checked + .slider:before {
        transform: translateX(28px);
    }

    /* Offline state handling */
    .toggle-card.offline {
        background: #64748b;
        box-shadow: 0 10px 25px rgba(100, 116, 139, 0.2);
    }
    .toggle-card.offline .slider:before {
        box-shadow: 0 2px 5px rgba(0,0,0,0.4);
    }

    /* Stats Row */
    .stats-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px 10px;
        flex: 1;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
    }

    .stat-number {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-dark);
        font-weight: 500;
    }

    /* Request Card */
    .request-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.06);
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .city-bg {
        position: absolute;
        bottom: 0;
        right: -20px;
        height: 100%;
        width: 60%;
        background-image: url('data:image/svg+xml;utf8,<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><rect x="150" y="80" width="30" height="120" fill="%23dcfce7" rx="4"/><rect x="120" y="110" width="25" height="90" fill="%23bbf7d0" rx="4"/><rect x="80" y="140" width="35" height="60" fill="%23dcfce7" rx="4"/><circle cx="160" cy="180" r="20" fill="%2386efac" opacity="0.5"/><circle cx="100" cy="190" r="30" fill="%2386efac" opacity="0.4"/></svg>');
        background-size: cover;
        background-position: right bottom;
        background-repeat: no-repeat;
        z-index: 0;
        opacity: 0.8;
    }

    .request-content {
        position: relative;
        z-index: 1;
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .request-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-dark);
    }

    .close-icon {
        color: #94a3b8;
        font-size: 18px;
        cursor: pointer;
    }

    .order-id {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 15px;
        display: block;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        font-size: 16px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .info-icon {
        color: var(--mandal-green);
        width: 20px;
        text-align: center;
        font-size: 18px;
    }

    .cod-badge {
        background: #f59e0b;
        color: white;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        margin-left: 8px;
    }

    .amount-text {
        color: #ef4444;
        font-weight: 800;
    }

    .btn-details {
        background: var(--mandal-green);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 15px;
        width: 100%;
        font-size: 16px;
        font-weight: 700;
        margin-top: 15px;
        text-decoration: none;
        display: block;
        text-align: center;
        transition: transform 0.2s;
    }
    .btn-details:active {
        transform: scale(0.98);
    }

</style>

<div class="dashboard-bg"></div>

<div class="dashboard-container">
    
    <!-- Top Header -->
    <div class="header-row">
        <div class="header-left">
            <div class="avatar-circle">
                <!-- Using an SVG avatar to mimic the 3D boy in the screenshot -->
                <svg width="40" height="40" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50" fill="#a7f3d0"/>
                    <circle cx="50" cy="40" r="20" fill="#fcd34d"/>
                    <path d="M 25 90 Q 50 60 75 90 Z" fill="#059669"/>
                    <path d="M 35 25 Q 50 10 65 25 Z" fill="#064e3b"/> <!-- Hat -->
                </svg>
            </div>
            <div class="greeting-text">
                <h1 class="driver-name">Hello, <?= e($delivery_name) ?></h1>
                <span class="driver-role">Delivery Partner</span>
            </div>
        </div>
        <div class="header-right">
            <i class="fa-solid fa-bell bell-icon"></i>
            <span class="notification-badge">3</span>
        </div>
    </div>

    <!-- Green Toggle Card linking to Availability Page (Screen 5) -->
    <a href="availability.php" class="toggle-card <?= $is_available ? '' : 'offline' ?>" style="text-decoration:none;">
        <div class="toggle-text"><?= $is_available ? 'Online' : 'Offline' ?></div>
        
        <label class="switch" style="pointer-events:none;">
            <input type="checkbox" <?= $is_available ? 'checked' : '' ?>>
            <span class="slider"></span>
        </label>
    </a>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?= $assigned_count ?></div>
            <div class="stat-label">Assigned</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $picked_count ?></div>
            <div class="stat-label">Picked</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $delivered_count ?></div>
            <div class="stat-label">Delivered</div>
        </div>
    </div>

    <!-- Active Orders as Delivery Requests -->
    <div id="tasks">
        <?php if (!empty($active_orders)): ?>
            <?php foreach ($active_orders as $order): ?>
                <div class="request-card">
                    <div class="city-bg"></div>
                    <div class="request-content">
                        <div class="request-header">
                            <div class="request-title">New Delivery Request</div>
                            <i class="fa-solid fa-xmark close-icon"></i>
                        </div>
                        
                        <span class="order-id">#<?= e($order['order_number'] ?? $order['order_no'] ?? $order['id']) ?></span>
                        
                        <div class="info-row">
                            <i class="fa-solid fa-user info-icon"></i>
                            <span><?= e($order['user_name'] ?? $order['customer_name'] ?? 'Customer') ?></span>
                        </div>
                        
                        <div class="info-row">
                            <i class="fa-solid fa-indian-rupee-sign info-icon"></i>
                            <span class="amount-text">₹<?= number_format((float)($order['grand_total'] ?? $order['total_amount'] ?? 0), 0) ?></span>
                            <?php if (strpos(strtolower($order['payment_method'] ?? ''), 'cash') !== false || strpos(strtolower($order['payment_method'] ?? ''), 'cod') !== false): ?>
                                <span class="cod-badge">COD</span>
                            <?php else: ?>
                                <span class="cod-badge" style="background:#10b981;">PAID</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="info-row">
                            <i class="fa-solid fa-location-dot info-icon"></i>
                            <!-- Simulating distance and location as per mockup -->
                            <span>2.4 km • Delivery Location</span>
                        </div>
                        
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-details">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="request-card" style="text-align: center; padding: 40px 20px;">
                <div class="city-bg" style="opacity:0.3"></div>
                <div class="request-content">
                    <i class="fa-solid fa-mug-hot" style="font-size:40px; color:#cbd5e1; margin-bottom:15px;"></i>
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-dark);">No Active Orders</h3>
                    <p style="color:var(--text-muted); font-size:14px;">You will see new delivery requests here once they are assigned to you.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    // Handle pull to refresh
    let touchstartY = 0;
    let touchendY = 0;
    
    document.addEventListener('touchstart', e => {
        touchstartY = e.changedTouches[0].screenY;
    });

    document.addEventListener('touchend', e => {
        touchendY = e.changedTouches[0].screenY;
        if (window.scrollY === 0 && (touchendY - touchstartY) > 100) {
            setTimeout(() => {
                window.location.reload();
            }, 300);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
