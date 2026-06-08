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
    SELECT id, order_number, grand_total, status, delivery_address, pincode, created_at
    FROM orders 
    WHERE assigned_delivery_id = ? AND status IN ('confirmed', 'preparing', 'out_for_delivery')
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
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }

    .welcome-card {
        background: var(--primary-gradient);
        color: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
    }

    .status-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.1);
        padding: 10px 15px;
        border-radius: 12px;
        margin-top: 15px;
    }

    .order-card {
        border-left: 5px solid #10b981;
        transition: transform 0.2s;
    }

    .order-card:hover {
        transform: translateX(5px);
    }

    .order-status {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 5px 10px;
        border-radius: 6px;
    }

    .status-confirmed { background: #dbeafe; color: #1e40af; }
    .status-preparing { background: #fef3c7; color: #b45309; }
    .status-out_for_delivery { background: #ffedd5; color: #c2410c; }
    
    .stats-card {
        text-align: center;
        padding: 20px;
    }
    
    .stats-card i {
        font-size: 30px;
        color: #10b981;
        margin-bottom: 10px;
    }
</style>

<div class="dashboard-container">
    
    <!-- Welcome Card -->
    <div class="welcome-card">
        <h4 class="mb-1 fw-bold">Hello, <?= e($delivery_name) ?>!</h4>
        <p class="mb-0 text-white-50 small">Manage your deliveries today.</p>
        
        <form method="POST" class="status-toggle">
            <input type="hidden" name="toggle_availability" value="1">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="is_available" value="1" id="availabilitySwitch" <?= $is_available ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="form-check-label fw-bold text-white small ms-2" for="availabilitySwitch">
                    <?= $is_available ? 'You are Available' : 'You are Offline' ?>
                </label>
            </div>
            <!-- If offline, value will not be sent, so we handle it gracefully. Wait, checkbox doesn't send value if unchecked. Let's fix that below. -->
        </form>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="premium-card stats-card h-100">
                <i class="fa-solid fa-box-open"></i>
                <h3 class="fw-bold mb-0"><?= count($active_orders) ?></h3>
                <div class="text-muted small fw-bold">Active Orders</div>
            </div>
        </div>
        <div class="col-6">
            <div class="premium-card stats-card h-100">
                <i class="fa-solid fa-circle-check"></i>
                <h3 class="fw-bold mb-0"><?= $delivered_count ?></h3>
                <div class="text-muted small fw-bold">Delivered</div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check me-2"></i> Your Active Tasks</h5>

    <?php if (empty($active_orders)): ?>
        <div class="premium-card p-4 text-center text-muted">
            <i class="fa-solid fa-mug-hot fa-3x mb-3 text-light"></i>
            <p class="mb-0">You have no active orders assigned right now.</p>
        </div>
    <?php else: ?>
        <?php foreach ($active_orders as $order): ?>
            <div class="premium-card order-card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-bold">#<?= e($order['order_number']) ?></div>
                        <div class="text-muted small"><?= date('h:i A, M d', strtotime($order['created_at'])) ?></div>
                    </div>
                    <span class="order-status status-<?= e($order['status']) ?>">
                        <?= str_replace('_', ' ', e($order['status'])) ?>
                    </span>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-3 mt-3">
                    <i class="fa-solid fa-location-dot text-danger"></i>
                    <div class="small text-muted text-truncate">
                        <?= e($order['delivery_address']) ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-success">₹<?= number_format((float)$order['grand_total'], 2) ?></div>
                    <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                        View Details
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
    // Ensure unchecked checkbox sends a 0
    document.querySelector('form.status-toggle').addEventListener('submit', function(e) {
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

<?php include 'includes/footer.php'; ?>
