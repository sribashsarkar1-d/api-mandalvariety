<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET['id'];
$page_title = 'Edit Product';
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
    $purchase_price = $_POST['purchase_price'];
    $selling_price = $_POST['selling_price'];
    $mrp = $_POST['mrp'];
    $gst = $_POST['gst_percent'] ?: 0;
    $discount = $_POST['discount'] ?: 0;
    $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $min_stock = $_POST['minimum_stock'] ?: 0;
    $desc = sanitize_input($_POST['description']);
    $status = $_POST['status'];
    
    // Handle image upload
    $image_update_sql = "";
    $params = [$category_id, $name, $sku, $barcode, $brand, $unit, $purchase_price, $selling_price, $mrp, $gst, $discount, $expiry, $min_stock, $desc, $status];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = uniqid() . '-' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image_update_sql = ", image = ?";
            $params[] = $filename;
        }
    }
    
    $params[] = $product_id;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE employee_products SET 
            category_id = ?, name = ?, sku = ?, barcode = ?, brand = ?, unit = ?, 
            purchase_price = ?, selling_price = ?, mrp = ?, gst_percent = ?, 
            discount = ?, expiry_date = ?, minimum_stock = ?, description = ?, status = ?
            $image_update_sql
            WHERE id = ?
        ");
        
        $stmt->execute($params);
        $success = "Product updated successfully.";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch current product
$stmt = $pdo->prepare("SELECT * FROM employee_products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

require_once '../includes/header.php';
?>

<div class="custom-card p-3">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="text-center mb-4">
        <?php $img = empty($product['image']) ? BASE_URL . '/assets/images/no-image.png' : BASE_URL . '/uploads/products/' . $product['image']; ?>
        <img src="<?= $img ?>" class="rounded shadow-sm" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22100%22%20height%3D%22100%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_1%20text%20%7B%20fill%3A%23999%3Bfont-weight%3Anormal%3Bfont-family%3AInter%2C%20Helvetica%2C%20sans-serif%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_1%22%3E%3Crect%20width%3D%22100%22%20height%3D%22100%22%20fill%3D%22%23eee%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2227%22%20y%3D%2254%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E'">
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label text-muted small">Product Name *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Update Image (Leave empty to keep current)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Select...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Unit *</label>
                <select name="unit" class="form-select" required>
                    <?php 
                    $units = ['piece', 'kg', 'gram', 'liter', 'ml', 'packet', 'box', 'dozen'];
                    foreach ($units as $u) {
                        $sel = ($product['unit'] === $u) ? 'selected' : '';
                        echo "<option value='{$u}' {$sel}>" . ucfirst($u) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">SKU</label>
                <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Barcode</label>
                <input type="text" name="barcode" class="form-control" value="<?= htmlspecialchars($product['barcode']) ?>">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Brand</label>
            <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($product['brand']) ?>">
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-4">
                <label class="form-label text-muted small">Purchase (₹) *</label>
                <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= htmlspecialchars($product['purchase_price']) ?>" required>
            </div>
            <div class="col-4">
                <label class="form-label text-muted small">Selling (₹) *</label>
                <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= htmlspecialchars($product['selling_price']) ?>" required>
            </div>
            <div class="col-4">
                <label class="form-label text-muted small">MRP (₹)</label>
                <input type="number" step="0.01" name="mrp" class="form-control" value="<?= htmlspecialchars($product['mrp']) ?>">
            </div>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-4">
                <label class="form-label text-muted small">GST (%)</label>
                <input type="number" step="0.01" name="gst_percent" class="form-control" value="<?= htmlspecialchars($product['gst_percent']) ?>">
            </div>
            <div class="col-4">
                <label class="form-label text-muted small">Disc (₹)</label>
                <input type="number" step="0.01" name="discount" class="form-control" value="<?= htmlspecialchars($product['discount']) ?>">
            </div>
            <div class="col-4">
                <label class="form-label text-muted small">Min Stock</label>
                <input type="number" step="0.001" name="minimum_stock" class="form-control" value="<?= htmlspecialchars($product['minimum_stock']) ?>">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" value="<?= htmlspecialchars($product['expiry_date']) ?>">
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="form-label text-muted small">Description</label>
            <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2">Update Product</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
