<?php
require_once 'includes/config.php';
if (!isset($_SESSION['inventory_user_id'])) {
    header("Location: login.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$query = "SELECT * FROM inventory_purchases";
$params = [];

if (!empty($search)) {
    $query .= " WHERE product_name LIKE ?";
    $params[] = "%$search%";
}

$query .= " ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Calculate Summary Data
$total_purchases = count($purchases);
$total_amount = 0;
foreach($purchases as $row) {
    $total_amount += ((float)$row['quantity'] * (float)$row['purchase_price']);
}

require_once 'includes/header.php';
?>

<style>
/* Custom Mobile UI Styles */
.mobile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0 20px 0;
}
.mobile-header-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-dark);
}
.hamburger-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #334155;
    padding: 0;
}
.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}
.notification-icon {
    position: relative;
    font-size: 1.25rem;
    color: #334155;
}
.notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: white;
    font-size: 0.6rem;
    font-weight: bold;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-text {
    margin-bottom: 20px;
}
.welcome-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
}
.welcome-name span { color: var(--primary); }
.welcome-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 4px;
}

.summary-card {
    background: var(--gradient-primary);
    border-radius: 20px;
    padding: 24px;
    color: white;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
    display: flex;
    justify-content: space-between;
}
.summary-block {
    display: flex;
    flex-direction: column;
}
.summary-label {
    font-size: 0.8rem;
    opacity: 0.9;
    margin-bottom: 4px;
}
.summary-value {
    font-size: 1.5rem;
    font-weight: 700;
}
.summary-icon-container {
    background: rgba(255, 255, 255, 0.15);
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-left: 15px;
}

.search-section {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}
.search-input-wrapper {
    flex-grow: 1;
    position: relative;
}
.search-input-wrapper i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}
.search-input-wrapper input {
    width: 100%;
    padding: 14px 16px 14px 45px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: white;
    font-size: 0.95rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.search-input-wrapper input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(84, 56, 220, 0.1);
}
.filter-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 16px;
    width: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(84, 56, 220, 0.2);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    color: #0f172a;
}
.add-new-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
}

/* Mobile Purchase Cards */
.purchase-card {
    background: white;
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    position: relative;
}
.pc-image {
    width: 60px;
    height: 60px;
    background: #f8fafc;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #cbd5e1;
    margin-right: 16px;
    flex-shrink: 0;
}
.pc-content {
    flex-grow: 1;
}
.pc-title {
    font-weight: 700;
    font-size: 1rem;
    color: #0f172a;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pc-id {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 6px;
}
.pc-qty-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-block;
}
.pc-price-col {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-right: 40px; /* Space for action buttons */
}
.pc-total {
    font-weight: 700;
    font-size: 1.05rem;
    color: #0f172a;
    margin-bottom: 2px;
}
.pc-price {
    font-size: 0.8rem;
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 4px;
}
.pc-date {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-bottom: 4px;
}
.pc-status {
    font-size: 0.7rem;
    font-weight: 700;
}
.pc-status.valid { color: #10b981; }
.pc-status.exp-soon { color: #f59e0b; }
.pc-status.expired { color: #ef4444; }
.pc-status.none { color: #94a3b8; }

.pc-actions {
    position: absolute;
    right: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.pc-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    text-decoration: none;
}
.pc-btn-edit {
    background: #eff6ff;
    color: #3b82f6;
}
.pc-btn-delete {
    background: #fef2f2;
    color: #ef4444;
}
</style>

<!-- MOBILE UI (Visible only on mobile) -->
<div class="d-block d-md-none">
    
    <!-- App Header -->
    <div class="mobile-header">
        <button class="hamburger-btn" id="mobileSidebarToggler">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-header-title">Inventory Pro</div>
        <div class="header-actions">
            <div class="notification-icon">
                <i class="far fa-bell"></i>
                <div class="notification-badge">3</div>
            </div>
        </div>
    </div>

    <!-- Welcome Text -->
    <div class="welcome-text">
        <div class="welcome-name">Hello, <span><?= e($_SESSION['inventory_username'] ?? 'User') ?></span></div>
        <div class="welcome-subtitle">Manage your shop's stock & purchases</div>
    </div>

    <!-- Summary Card -->
    <div class="summary-card">
        <div class="d-flex align-items-center">
            <div class="summary-block">
                <span class="summary-label">Total Purchases</span>
                <span class="summary-value"><?= number_format($total_purchases) ?></span>
            </div>
            <div class="summary-icon-container">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <div class="summary-block">
                <span class="summary-label">Total Amount</span>
                <span class="summary-value">₹<?= number_format($total_amount) ?></span>
            </div>
            <div class="summary-icon-container">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <form method="GET" action="index.php" class="search-section">
        <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search by product name..." value="<?= e($search) ?>">
        </div>
        <button type="submit" class="filter-btn">
            <i class="fas fa-filter"></i>
        </button>
    </form>

    <!-- Purchases Header -->
    <div class="section-header">
        <h3 class="section-title">All Purchases</h3>
        <a href="add_purchase.php" class="add-new-btn"><i class="fas fa-plus me-1"></i> Add New</a>
    </div>

    <!-- Purchase Cards -->
    <div class="purchase-cards-container pb-4">
        <?php if(count($purchases) > 0): ?>
            <?php foreach($purchases as $row): ?>
                <div class="purchase-card">
                    <div class="pc-image">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="pc-content">
                        <div class="pc-title"><?= e($row['product_name']) ?></div>
                        <div class="pc-id">ID: #<?= $row['id'] ?></div>
                        <div class="pc-qty-badge"><?= floatval($row['quantity']) ?> <?= e($row['unit'] ?? 'pcs') ?></div>
                    </div>
                    <div class="pc-price-col">
                        <div class="pc-total">₹<?= number_format($row['quantity'] * $row['purchase_price'], 2) ?></div>
                        <div class="pc-price">₹<?= number_format($row['purchase_price'], 2) ?></div>
                        <div class="pc-date"><i class="far fa-calendar-alt"></i> <?= date('d M, Y', strtotime($row['purchase_date'])) ?></div>
                        
                        <?php 
                        if(!empty($row['expiry_date'])) {
                            $expDate = strtotime($row['expiry_date']);
                            $today = strtotime(date('Y-m-d'));
                            if ($expDate < $today) {
                                echo "<div class='pc-status expired'>Expired</div>";
                            } elseif ($expDate < strtotime('+7 days')) {
                                echo "<div class='pc-status exp-soon'>Expiring Soon</div>";
                            } else {
                                echo "<div class='pc-status valid'>Valid</div>";
                            }
                        } else {
                            echo "<div class='pc-status none'>No Expiry</div>";
                        }
                        ?>
                    </div>
                    <div class="pc-actions">
                        <a href="edit_purchase.php?id=<?= $row['id'] ?>" class="pc-btn pc-btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete_purchase.php?id=<?= $row['id'] ?>" class="pc-btn pc-btn-delete" onclick="return confirm('Delete this purchase?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box-open fs-1 text-light mb-3"></i><br>
                No purchases found.
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- DESKTOP UI (Hidden on mobile) -->
<div class="d-none d-md-block">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Inventory Purchases</h2>
            <p class="text-muted mb-0 small">Manage your shop's stock and purchases efficiently.</p>
        </div>
        <a href="add_purchase.php" class="btn btn-primary shadow-sm px-4">
            <i class="fas fa-plus me-1"></i> Add New
        </a>
    </div>

    <div class="card mb-4 border-0">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="index.php" class="d-flex flex-column flex-md-row gap-2">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by product name..." value="<?= e($search) ?>">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-search me-1"></i> Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="index.php" class="btn btn-outline-secondary px-3">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Purchase Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if(count($purchases) > 0): ?>
                        <?php foreach($purchases as $row): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-muted">#<?= $row['id'] ?></td>
                                <td class="fw-bold text-dark"><?= e($row['product_name']) ?></td>
                                <td><span class="badge bg-light text-dark border px-2 py-1"><?= floatval($row['quantity']) ?> <?= e($row['unit'] ?? 'pcs') ?></span></td>
                                <td class="fw-medium">₹<?= number_format($row['purchase_price'], 2) ?></td>
                                <td class="fw-bold text-success">₹<?= number_format($row['quantity'] * $row['purchase_price'], 2) ?></td>
                                <td class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?= date('d M, Y', strtotime($row['purchase_date'])) ?></td>
                                <td>
                                    <?php 
                                    if(!empty($row['expiry_date'])) {
                                        $expDate = strtotime($row['expiry_date']);
                                        $today = strtotime(date('Y-m-d'));
                                        $dateStr = date('d M, Y', $expDate);
                                        if ($expDate < $today) {
                                            echo "<span class='badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 rounded-pill'><i class='fas fa-exclamation-circle me-1'></i> Expired</span><br><small class='text-muted'>$dateStr</small>";
                                        } elseif ($expDate < strtotime('+7 days')) {
                                            echo "<span class='badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1 rounded-pill'><i class='fas fa-clock me-1'></i> Expiring Soon</span><br><small class='text-muted'>$dateStr</small>";
                                        } else {
                                            echo "<span class='badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill'><i class='fas fa-check-circle me-1'></i> Valid</span><br><small class='text-muted'>$dateStr</small>";
                                        }
                                    } else {
                                        echo "<span class='text-muted small'>No Expiry</span>";
                                    }
                                    ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_purchase.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border text-primary rounded-circle" title="Edit" style="width: 32px; height: 32px; padding: 4px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_purchase.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light border text-danger rounded-circle ms-1" title="Delete" onclick="return confirm('Are you sure you want to delete this item?');" style="width: 32px; height: 32px; padding: 4px;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fs-1 text-light mb-3"></i><br>
                                No purchases found. Start adding your inventory!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div> <!-- End Desktop container -->

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const mobileToggler = document.getElementById('mobileSidebarToggler');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if(mobileToggler && sidebar && backdrop) {
            mobileToggler.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                if (sidebar.classList.contains('show')) {
                    backdrop.classList.add('show');
                } else {
                    backdrop.classList.remove('show');
                }
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
