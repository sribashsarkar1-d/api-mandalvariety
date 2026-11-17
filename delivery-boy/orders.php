<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = (int)$_SESSION['delivery_id'];
$tab = $_GET['tab'] ?? 'assigned'; // 'assigned' or 'history'
if (!in_array($tab, ['assigned', 'history'])) {
    $tab = 'assigned';
}

$search = trim($_GET['search'] ?? '');

$where_clause = "WHERE o.assigned_delivery_id = ?";
$params = [$delivery_id];

if ($tab === 'history') {
    $where_clause .= " AND (o.delivery_boy_status = 'delivered' OR o.status = 'delivered')";
} else {
    $where_clause .= " AND o.status NOT IN ('delivered', 'cancelled', 'returned')";
}

if ($search !== '') {
    $where_clause .= " AND (o.order_number LIKE ? OR o.id LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = "
    SELECT o.*, u.name as user_name, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    $where_clause
    ORDER BY o.updated_at DESC, o.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<style>
    .orders-page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .btn-back-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-text);
        text-decoration: none;
        font-size: 14px;
        transition: background 0.15s;
    }

    .btn-back-circle:active {
        background: var(--light-green);
    }

    .orders-page-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
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
        text-decoration: none;
    }

    .tab-pill-btn.active {
        background: var(--primary-green);
        color: #FFFFFF;
        border-color: var(--primary-green);
        box-shadow: 0 4px 12px rgba(0, 155, 85, 0.22);
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
        text-decoration: none;
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

    <div class="orders-page-header">
        <a href="index.php" class="btn-back-circle">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="orders-page-title">My Orders</h2>
    </div>

    <!-- Tabs: Assigned & History -->
    <div class="pill-tabs-wrap">
        <a href="orders.php?tab=assigned" class="tab-pill-btn <?= $tab === 'assigned' ? 'active' : '' ?>">
            <i class="fa-solid fa-bars-staggered"></i>
            <span>Assigned</span>
        </a>
        <a href="orders.php?tab=history" class="tab-pill-btn <?= $tab === 'history' ? 'active' : '' ?>">
            <i class="fa-regular fa-clock"></i>
            <span>History</span>
        </a>
    </div>

    <!-- Search and Action Bar -->
    <div class="search-actions-bar">
        <div class="search-box-pill">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="orderSearchInput" class="search-input" value="<?= e($search) ?>" placeholder="Search by Order ID..." oninput="filterOrdersLive(this.value)">
        </div>
        <a href="orders.php?tab=<?= e($tab) ?>" class="action-circle-btn" title="Refresh">
            <i class="fa-solid fa-arrows-rotate"></i>
        </a>
    </div>

    <!-- Orders List -->
    <div id="ordersContainer">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
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

                    $is_delivered = ($order['delivery_boy_status'] === 'delivered' || $order['status'] === 'delivered');
                    $badge_label = 'Assigned';
                    if ($is_delivered) {
                        $badge_label = 'Delivered';
                    } elseif (!empty($order['delivery_boy_status'])) {
                        $db_st = $order['delivery_boy_status'];
                        if ($db_st === 'picked_up') $badge_label = 'Picked Up';
                        elseif ($db_st === 'out_for_delivery') $badge_label = 'Out for Delivery';
                        elseif ($db_st === 'arrived') $badge_label = 'Arrived';
                    }
                ?>
                <div class="order-card order-item-card" data-order-number="<?= strtolower(e($order_no)) ?>" data-customer-name="<?= strtolower(e($cust_name)) ?>">
                    <div class="order-card-top">
                        <div class="order-card-left">
                            <div class="order-bag-icon" style="<?= $is_delivered ? 'background:#DCFCE7; color:#15803D;' : '' ?>">
                                <i class="fa-solid <?= $is_delivered ? 'fa-circle-check' : 'fa-bag-shopping' ?>"></i>
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
                        <div class="badge-status" style="<?= $is_delivered ? 'background:#DCFCE7; color:#15803D;' : '' ?>">
                            <span class="status-dot" style="<?= $is_delivered ? 'background-color:#15803D;' : '' ?>"></span>
                            <span><?= e($badge_label) ?></span>
                        </div>
                    </div>

                    <div class="order-card-divider"></div>

                    <div class="order-card-bottom">
                        <div class="order-price-wrap">
                            <span class="currency">₹</span>
                            <span class="amount"><?= number_format($total, 0) ?></span>
                        </div>
                        <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="btn-view-details" style="<?= $is_delivered ? 'background:#475569;' : '' ?>">
                            <span><?= $is_delivered ? 'View Summary' : 'View Details' ?></span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-card-state">
                <div class="empty-state-icon">
                    <i class="fa-solid <?= $tab === 'history' ? 'fa-clock-rotate-left' : 'fa-box-open' ?>"></i>
                </div>
                <h4 class="empty-state-title"><?= $tab === 'history' ? 'No Completed Deliveries' : 'No Assigned Orders' ?></h4>
                <p class="empty-state-desc">
                    <?= $tab === 'history' 
                        ? 'Completed deliveries will appear here once delivered.' 
                        : 'New orders assigned to you by admin will appear here.' ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
// Live instantaneous filtering
function filterOrdersLive(query) {
    const term = (query || '').trim().toLowerCase();
    const cards = document.querySelectorAll('.order-item-card');
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
