<?php
require_once 'includes/config.php';
if (!isset($_SESSION['inventory_user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $purchase_price = (float)($_POST['purchase_price'] ?? 0);
    $purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    if (empty($product_name) || $quantity <= 0 || $purchase_price < 0) {
        $error = "Please provide valid product name, quantity, and price.";
    } else {
        $stmt = $conn->prepare("INSERT INTO inventory_purchases (product_name, quantity, purchase_price, purchase_date, expiry_date) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$product_name, $quantity, $purchase_price, $purchase_date, $expiry_date])) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Failed to add purchase.";
        }
    }
}
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Add New Purchase</h4>
            </div>
            <div class="card-body">
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Product Name (ki nam)</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity (koy pice kinlo)</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Price / Cost (koto dam)</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Date (kobe kinlo)</label>
                            <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date (optional)</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Purchase</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
