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
// Active orders: not delivered, not cancelled, not returned
$stmt = $conn->prepare("
    SELECT *
    FROM orders 
    WHERE assigned_delivery_id = ? AND status NOT IN ('delivered', 'cancelled', 'returned')
    ORDER BY created_at DESC
");
$stmt->execute([$delivery_id]);
$active_orders = $stmt->fetchAll();

// Fetch completed orders count for stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE assigned_delivery_id = ? AND status = 'delivered'");
$stmt->execute([$delivery_id]);
$delivered_count = $stmt->fetchColumn();
?>

<?php include 'includes/header.php'; ?>

<style>
    .dashboard-container {
        padding: 24px 20px;
    }

    /* Native Header */
    .mobile-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }
    
    .header-left {
        display: flex;
        flex-direction: column;
    }
    
    .greeting-sub {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 2px;
    }
    
    .driver-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
        line-height: 1.2;
    }
    
    .driver-msg {
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    
    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .header-icon {
        color: var(--text-dark);
        font-size: 1.25rem;
        text-decoration: none;
        position: relative;
    }
    
    .notification-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        width: 10px;
        height: 10px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid white;
    }
    
    .avatar-img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--mandal-green-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--mandal-green);
        font-size: 1.5rem;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    /* Availability Card */
    .availability-card {
        background: var(--mandal-green-gradient);
        border-radius: 20px;
        padding: 24px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(7, 161, 88, 0.25);
        position: relative;
        overflow: hidden;
    }
    
    .availability-card::after {
        content: '\f21c'; /* motorcycle icon */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: -20px;
        bottom: -30px;
        font-size: 8rem;
        opacity: 0.05;
        transform: rotate(-15deg);
        pointer-events: none;
    }

    /* Custom Toggle Switch */
    .custom-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 32px;
    }
    .custom-switch input {
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
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    input:checked + .slider {
        background-color: #ffffff;
    }
    input:checked + .slider:before {
        transform: translateX(28px);
        background-color: var(--mandal-green);
    }

    /* Stats Grid */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 24px;
    }
    
    .stats-card {
        text-align: center;
        padding: 20px 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .stats-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 12px;
    }
    .stats-icon.orders { background: #e0f2fe; color: #0ea5e9; }
    .stats-icon.delivered { background: #dbeafe; color: #3b82f6; }
    
    /* Quick Actions */
    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 16px;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    
    .action-btn {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--text-dark);
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .action-btn.disabled { opacity: 0.5; pointer-events: none; }
    .action-btn i { font-size: 1.4rem; margin-bottom: 8px; color: var(--text-dark); }
    .action-btn span { font-size: 0.75rem; font-weight: 500; text-align: center; }

    /* Safety Card */
    .safety-card {
        background: var(--mandal-green-light);
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    .safety-icon-wrapper {
        background: var(--mandal-green);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* Modern Order Card */
    .order-card {
        padding: 20px;
        position: relative;
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .order-id {
        background: var(--mandal-green-light);
        color: var(--mandal-green);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 4px 10px;
        border-radius: 6px;
    }
    
    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .status-confirmed { background: #e0e7ff; color: #4338ca; }
    .status-preparing { background: #fef3c7; color: #b45309; }
    .status-out_for_delivery { background: #ffedd5; color: #c2410c; }
    
    .order-time {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 20px;
        display: block;
    }

    .timeline {
        position: relative;
        padding-left: 24px;
        margin-bottom: 20px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 16px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: white;
        border: 3px solid;
    }
    .icon-pickup { border-color: var(--mandal-green); }
    .icon-drop { border-color: #ef4444; }
    
    .timeline-title {
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 2px;
    }
    .timeline-desc {
        font-size: 0.8rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px dashed #e2e8f0;
        padding-top: 16px;
        margin-bottom: 16px;
    }
</style>

<div class="dashboard-container">
    
    <!-- Mobile Header -->
    <div class="mobile-header">
        <a href="javascript:void(0)" class="header-icon mt-2"><i class="fa-solid fa-bars"></i></a>
        
        <div class="header-left align-items-center text-center mx-auto">
            <span class="greeting-sub">Good Morning,</span>
            <h1 class="driver-name"><?= e($delivery_name) ?></h1>
            <span class="driver-msg">Stay safe and deliver on time!</span>
        </div>
        
        <div class="header-right">
            <a href="index.php" class="header-icon mt-2 me-2" onclick="this.querySelector('i').classList.add('fa-spin')">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
            <a href="javascript:void(0)" class="header-icon mt-2">
                <i class="fa-regular fa-bell"></i>
                <span class="notification-badge"></span>
            </a>
            <div class="avatar-img ms-2">
                <i class="fa-solid fa-user-astronaut"></i>
            </div>
        </div>
    </div>

    <!-- Availability Card -->
    <div class="availability-card">
        <div>
            <div style="font-size: 0.9rem; margin-bottom: 2px;">You are</div>
            <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 8px;"><?= $is_available ? 'Available' : 'Offline' ?></h2>
            <div style="font-size: 0.8rem; opacity: 0.9;"><?= $is_available ? 'Ready to receive orders' : 'Go online to start receiving orders' ?></div>
        </div>
        <form method="POST" id="availabilityForm" class="m-0">
            <input type="hidden" name="toggle_availability" value="1">
            <label class="custom-switch">
                <input type="checkbox" name="is_available" value="1" id="availabilitySwitch" <?= $is_available ? 'checked' : '' ?> onchange="document.getElementById('availabilityForm').submit()">
                <span class="slider"></span>
            </label>
        </form>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row">
        <div class="premium-card stats-card">
            <div class="stats-icon orders"><i class="fa-solid fa-box-open"></i></div>
            <h3 class="fw-bold mb-1 fs-4"><?= count($active_orders) ?></h3>
            <div class="text-muted small">Active Orders</div>
        </div>
        <div class="premium-card stats-card">
            <div class="stats-icon delivered"><i class="fa-regular fa-circle-check"></i></div>
            <h3 class="fw-bold mb-1 fs-4"><?= $delivered_count ?></h3>
            <div class="text-muted small">Completed</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h5 class="section-title">Quick Actions</h5>
    <div class="quick-actions">
        <a href="#tasks" class="action-btn">
            <i class="fa-solid fa-clipboard-list" style="color: #6366f1;"></i>
            <span>My Tasks</span>
        </a>
        <a href="javascript:void(0)" class="action-btn disabled">
            <i class="fa-solid fa-map-location-dot" style="color: #3b82f6;"></i>
            <span>Map</span>
        </a>
        <a href="earnings.php" class="action-btn">
            <i class="fa-solid fa-wallet" style="color: #f59e0b;"></i>
            <span>Earnings</span>
        </a>
        <a href="profile.php" class="action-btn">
            <i class="fa-regular fa-user" style="color: #8b5cf6;"></i>
            <span>Profile</span>
        </a>
    </div>

    <!-- Safety Card -->
    <div class="safety-card">
        <div class="safety-icon-wrapper">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1" style="color: var(--text-dark);">Safety First!</h6>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Follow traffic rules and wear your helmet.</div>
        </div>
    </div>

    <h5 class="section-title" id="tasks">My Tasks</h5>

    <?php if (empty($active_orders)): ?>
        <div class="premium-card p-4 text-center text-muted">
            <div style="width: 80px; height: 80px; background: var(--secondary-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="fa-solid fa-mug-hot fa-2x" style="color: #cbd5e1;"></i>
            </div>
            <p class="mb-0 fw-500">No active orders right now.</p>
        </div>
    <?php else: ?>
        <?php foreach ($active_orders as $order): ?>
            <div class="premium-card order-card">
                <div class="order-header">
                    <span class="order-id">#<?= e($order['order_number'] ?? $order['order_no'] ?? 'N/A') ?></span>
                    <span class="status-badge status-<?= e($order['status'] ?? 'unknown') ?>">
                        <?= str_replace('_', ' ', e($order['status'] ?? 'unknown')) ?>
                    </span>
                </div>
                
                <span class="order-time"><?= !empty($order['created_at']) ? date('h:i A, M d', strtotime($order['created_at'])) : 'Unknown time' ?></span>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon icon-pickup"></div>
                        <div class="timeline-title text-success">Pickup</div>
                        <div class="timeline-desc fw-500 text-dark">Mandal Variety Store</div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon icon-drop"></div>
                        <div class="timeline-title text-danger">Drop</div>
                        <div class="timeline-desc">
                            <?= e($order['shipping_address'] ?? $order['delivery_address'] ?? $order['address'] ?? 'No address provided') ?>
                        </div>
                    </div>
                </div>

                <div class="order-footer">
                    <div>
                        <div class="small text-muted mb-1"><i class="fa-solid fa-money-bill-wave me-1"></i> Amount</div>
                        <div class="fw-bold fs-5 text-dark">₹<?= number_format((float)($order['grand_total'] ?? $order['total_amount'] ?? 0), 2) ?></div>
                    </div>
                </div>
                
                <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-premium d-flex justify-content-between align-items-center">
                    <span>View Details</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
    // Ensure unchecked checkbox sends a 0 if the form is submitted via some other means
    // Since we now use onchange on the checkbox itself to submit the form, it sends the value if checked.
    // However, if unchecked, standard HTML behavior omits it.
    // The previous script added a hidden input when unchecked. Let's adapt it for the new DOM.
    document.getElementById('availabilityForm').addEventListener('submit', function(e) {
        let cb = document.getElementById('availabilitySwitch');
        if(!cb.checked) {
            let hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'is_available';
            hidden.value = '0';
            this.appendChild(hidden);
        }
    });
</script>

<!-- Pull to Refresh Logic -->
<style>
    body {
        overscroll-behavior-y: contain; /* Prevents default browser pull-to-refresh to use our custom one if needed, but actually native is fine. We will just let native do its thing and add a fallback refresh button. */
    }
</style>
<script>
    let touchstartY = 0;
    let touchendY = 0;
    
    document.addEventListener('touchstart', e => {
        touchstartY = e.changedTouches[0].screenY;
    });

    document.addEventListener('touchend', e => {
        touchendY = e.changedTouches[0].screenY;
        handleSwipe();
    });

    function handleSwipe() {
        // If swiped down significantly and at the very top of the page
        if (window.scrollY === 0 && (touchendY - touchstartY) > 100) {
            // Optional: Show a loading indicator here
            const refreshIcon = document.querySelector('.fa-rotate-right');
            if (refreshIcon) refreshIcon.classList.add('fa-spin');
            
            setTimeout(() => {
                window.location.reload();
            }, 300);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
