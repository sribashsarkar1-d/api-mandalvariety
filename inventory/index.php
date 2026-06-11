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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Inventory Purchases</h2>
    <a href="add_purchase.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search product name..." value="<?= e($search) ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <?php if(!empty($search)): ?>
                <a href="index.php" class="btn btn-secondary ms-2">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Purchase Date</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($purchases) > 0): ?>
                    <?php foreach($purchases as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= e($row['product_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>₹<?= number_format($row['purchase_price'], 2) ?></td>
                            <td>₹<?= number_format($row['quantity'] * $row['purchase_price'], 2) ?></td>
                            <td><?= date('d M, Y', strtotime($row['purchase_date'])) ?></td>
                            <td>
                                <?php 
                                if(!empty($row['expiry_date'])) {
                                    $expDate = strtotime($row['expiry_date']);
                                    $today = strtotime(date('Y-m-d'));
                                    if ($expDate < $today) {
                                        echo "<span class='badge bg-danger'>Expired on " . date('d M, Y', $expDate) . "</span>";
                                    } elseif ($expDate < strtotime('+7 days')) {
                                        echo "<span class='badge bg-warning text-dark'>Expiring " . date('d M, Y', $expDate) . "</span>";
                                    } else {
                                        echo date('d M, Y', $expDate);
                                    }
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit_purchase.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete_purchase.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No purchases found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
