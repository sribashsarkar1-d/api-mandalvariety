<?php
require_once 'includes/config.php';
if (!isset($_SESSION['inventory_user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM inventory_purchases WHERE id = ?");
$stmt->execute([$id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $quantity = (float)($_POST['quantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'pcs');
    $purchase_price = (float)($_POST['purchase_price'] ?? 0);
    $purchase_date = $_POST['purchase_date'] ?? '';
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    if (empty($product_name) || $quantity <= 0 || $purchase_price <= 0 || empty($purchase_date)) {
        $error = "Please fill all required fields correctly.";
    } else {
        $stmt = $conn->prepare("UPDATE inventory_purchases SET product_name = ?, quantity = ?, unit = ?, purchase_price = ?, purchase_date = ?, expiry_date = ? WHERE id = ?");
        if ($stmt->execute([$product_name, $quantity, $unit, $purchase_price, $purchase_date, $expiry_date, $id])) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Failed to update purchase.";
        }
    }
}
require_once 'includes/header.php';
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php" class="btn btn-light border text-secondary rounded-circle me-3" style="width: 40px; height: 40px; padding: 7px;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h3 class="fw-bold mb-0 text-dark">Edit Purchase #<?= $id ?></h3>
        <p class="text-muted mb-0 small">Update the details of this stock entry</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0">
            <div class="card-body p-4">
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger p-2 mb-4" style="border-radius: 8px;"><i class="fas fa-exclamation-triangle me-2"></i><?= e($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-medium text-secondary">Product Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-box"></i></span>
                                <input type="text" name="product_name" class="form-control border-start-0 ps-0" value="<?= e($purchase['product_name']) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-secondary">Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-layer-group"></i></span>
                                <input type="number" step="0.01" name="quantity" class="form-control border-start-0 ps-0" value="<?= $purchase['quantity'] ?>" required min="0.01">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium text-secondary">Unit</label>
                            <?php $current_unit = $purchase['unit'] ?? 'pcs'; ?>
                            <select name="unit" class="form-select">
                                <option value="pcs" <?= $current_unit == 'pcs' ? 'selected' : '' ?>>pcs</option>
                                <option value="kg" <?= $current_unit == 'kg' ? 'selected' : '' ?>>kg</option>
                                <option value="g" <?= $current_unit == 'g' ? 'selected' : '' ?>>g</option>
                                <option value="L" <?= $current_unit == 'L' ? 'selected' : '' ?>>L</option>
                                <option value="ml" <?= $current_unit == 'ml' ? 'selected' : '' ?>>ml</option>
                                <option value="box" <?= $current_unit == 'box' ? 'selected' : '' ?>>box</option>
                                <option value="packet" <?= $current_unit == 'packet' ? 'selected' : '' ?>>packet</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Purchase Price (Per item) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-rupee-sign"></i></span>
                                <input type="number" step="0.01" name="purchase_price" class="form-control border-start-0 ps-0" value="<?= $purchase['purchase_price'] ?>" required min="0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Purchase Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="far fa-calendar-check"></i></span>
                                <input type="date" name="purchase_date" class="form-control border-start-0 ps-0" value="<?= $purchase['purchase_date'] ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Expiry Date <span class="text-muted fw-normal">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="far fa-calendar-times"></i></span>
                                <input type="date" name="expiry_date" class="form-control border-start-0 ps-0" value="<?= $purchase['expiry_date'] ?>">
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4 text-muted">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm"><i class="fas fa-save me-1"></i> Update Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
