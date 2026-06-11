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

require_once 'includes/header.php';
?>

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

<?php require_once 'includes/footer.php'; ?>
