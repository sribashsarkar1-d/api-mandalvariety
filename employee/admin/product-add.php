<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Add Product';
$show_back_btn = true;

// Fetch categories
$stmt = $pdo->query("SELECT id, name FROM employee_categories WHERE status = 'active'");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $category_id = $_POST['category_id'] ?: null;
    $sku = sanitize_input($_POST['sku']);
    $barcode = sanitize_input($_POST['barcode']);
    $brand = sanitize_input($_POST['brand']);
    $unit = $_POST['unit'];
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $selling_price = $_POST['selling_price'];
    $mrp = $_POST['mrp'] ?? 0;
    $gst = $_POST['gst_percent'] ?? 0;
    $discount = $_POST['discount'] ?: 0;
    $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $min_stock = $_POST['minimum_stock'] ?: 0;
    $quantity = $_POST['quantity'] ?: 0;
    $desc = sanitize_input($_POST['description']);
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = uniqid() . '-' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $filename;
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO employee_products 
            (category_id, name, sku, barcode, image, brand, unit, purchase_price, selling_price, mrp, gst_percent, discount, expiry_date, minimum_stock, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $category_id, $name, $sku, $barcode, $image, $brand, $unit, $purchase_price, $selling_price, $mrp, $gst, $discount, $expiry, $min_stock, $desc
        ]);
        
        $product_id = $pdo->lastInsertId();
        
        // Initialize stock
        $status = 'in_stock';
        if ($quantity <= 0) $status = 'out_of_stock';
        elseif ($quantity <= $min_stock) $status = 'low_stock';
        
        $stmtStock = $pdo->prepare("INSERT INTO employee_product_stock (product_id, quantity, stock_status) VALUES (?, ?, ?)");
        $stmtStock->execute([$product_id, $quantity, $status]);
        
        $pdo->commit();
        header("Location: products.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$auto_sku = 'SKU-' . strtoupper(substr(uniqid(), -6));
$auto_barcode = '890' . rand(100000000, 999999999);

require_once '../includes/header.php';
?>

<div class="custom-card p-3">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label text-muted small">Product Name *</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Product Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Select...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Unit *</label>
                <select name="unit" class="form-select" required>
                    <option value="piece">Piece</option>
                    <option value="kg">KG</option>
                    <option value="gram">Gram</option>
                    <option value="liter">Liter</option>
                    <option value="ml">ML</option>
                    <option value="packet">Packet</option>
                    <option value="box">Box</option>
                    <option value="dozen">Dozen</option>
                </select>
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">SKU</label>
                <input type="text" name="sku" class="form-control" value="<?= $auto_sku ?>">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Barcode</label>
                <input type="text" name="barcode" class="form-control" value="<?= $auto_barcode ?>">
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">Purchase Price</label>
                <input type="number" step="0.01" name="purchase_price" class="form-control" value="0">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">MRP</label>
                <input type="number" step="0.01" name="mrp" class="form-control" value="0">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">Selling Price *</label>
                <input type="number" step="0.01" name="selling_price" id="sellingPrice" class="form-control" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small">Discount</label>
                <input type="number" step="0.01" name="discount" id="discountAmt" class="form-control" value="0">
            </div>
        </div>
        
        <div class="p-2 mb-3 bg-light rounded border d-flex justify-content-between align-items-center">
            <span class="text-muted small fw-bold">Final Discounted Price:</span>
            <span class="fs-5 fw-bold text-success" id="finalPriceDisplay">₹0.00</span>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Initial Stock Qty</label>
                <input type="number" step="0.001" name="quantity" class="form-control" value="0">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Min Stock</label>
                <input type="number" step="0.001" name="minimum_stock" class="form-control" value="5">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control">
        </div>
        
        <div class="mb-4">
            <label class="form-label text-muted small">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2">Save Product</button>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sellingPriceInput = document.getElementById('sellingPrice');
    const discountInput = document.getElementById('discountAmt');
    const finalPriceDisplay = document.getElementById('finalPriceDisplay');

    function calculateFinalPrice() {
        const sp = parseFloat(sellingPriceInput.value) || 0;
        const disc = parseFloat(discountInput.value) || 0;
        const final = Math.max(0, sp - disc);
        finalPriceDisplay.textContent = '₹' + final.toFixed(2);
    }

    sellingPriceInput.addEventListener('input', calculateFinalPrice);
    discountInput.addEventListener('input', calculateFinalPrice);
});
</script>
<?php $extra_js = ob_get_clean(); ?>

<?php require_once '../includes/footer.php'; ?>
