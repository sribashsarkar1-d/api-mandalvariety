<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Add Purchase';
$show_back_btn = true;

// Fetch suppliers
$stmtSup = $pdo->query("SELECT id, name FROM employee_suppliers WHERE status = 'active'");
$suppliers = $stmtSup->fetchAll();

// Fetch products
$stmtProd = $pdo->query("SELECT id, name, unit, purchase_price FROM employee_products WHERE status = 'active'");
$products = $stmtProd->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'] ?: null;
    $product_id = $_POST['product_id'];
    $qty = (float)$_POST['quantity'];
    $purchase_price = (float)$_POST['purchase_price'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    
    $total = $qty * $purchase_price;
    $purchase_number = "PUR-" . date("Ymd-His");
    $employee_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Insert Purchase
        $stmt = $pdo->prepare("
            INSERT INTO employee_purchases (purchase_number, supplier_id, employee_id, grand_total, payment_status)
            VALUES (?, ?, ?, ?, 'paid')
        ");
        $stmt->execute([$purchase_number, $supplier_id, $employee_id, $total]);
        $purchase_id = $pdo->lastInsertId();
        
        // Get product details
        $stmtP = $pdo->prepare("SELECT unit, minimum_stock FROM employee_products WHERE id = ?");
        $stmtP->execute([$product_id]);
        $prod = $stmtP->fetch();
        
        // 2. Insert Purchase Item
        $stmtItem = $pdo->prepare("
            INSERT INTO employee_purchase_items (purchase_id, product_id, quantity, unit, purchase_price, total_price, expiry_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtItem->execute([$purchase_id, $product_id, $qty, $prod['unit'], $purchase_price, $total, $expiry_date]);
        
        // 3. Update Stock
        $stmtStock = $pdo->prepare("SELECT quantity FROM employee_product_stock WHERE product_id = ? FOR UPDATE");
        $stmtStock->execute([$product_id]);
        $stock = $stmtStock->fetch();
        
        $new_qty = ($stock ? $stock['quantity'] : 0) + $qty;
        $min_stock = $prod ? $prod['minimum_stock'] : 0;
        
        $status = 'in_stock';
        if ($new_qty <= 0) $status = 'out_of_stock';
        elseif ($new_qty <= $min_stock) $status = 'low_stock';
        
        if ($stock) {
            $stmtUpd = $pdo->prepare("UPDATE employee_product_stock SET quantity = ?, stock_status = ? WHERE product_id = ?");
            $stmtUpd->execute([$new_qty, $status, $product_id]);
        } else {
            $stmtUpd = $pdo->prepare("INSERT INTO employee_product_stock (product_id, quantity, stock_status) VALUES (?, ?, ?)");
            $stmtUpd->execute([$product_id, $new_qty, $status]);
        }
        
        // 4. Update product cost/expiry
        $stmtProdUpd = $pdo->prepare("UPDATE employee_products SET purchase_price = ?, expiry_date = COALESCE(?, expiry_date) WHERE id = ?");
        $stmtProdUpd->execute([$purchase_price, $expiry_date, $product_id]);
        
        // 5. Stock movement
        $stmtMov = $pdo->prepare("INSERT INTO employee_stock_movements (product_id, employee_id, movement_type, quantity, reference_id, note) VALUES (?, ?, 'purchase', ?, ?, ?)");
        $stmtMov->execute([$product_id, $employee_id, $qty, $purchase_id, "Purchase In"]);
        
        $pdo->commit();
        header("Location: stock.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="custom-card p-3">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="mb-3">
            <label class="form-label text-muted small">Supplier (Optional)</label>
            <select name="supplier_id" class="form-select">
                <option value="">Select...</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Product *</label>
            <select name="product_id" class="form-select" id="productSelect" required>
                <option value="">Select Product...</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" data-price="<?= $p['purchase_price'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['unit'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Quantity *</label>
                <input type="number" step="0.001" name="quantity" class="form-control" required>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Purchase Price/Unit *</label>
                <input type="number" step="0.01" name="purchase_price" id="purchasePrice" class="form-control" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label text-muted small">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control">
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2">Add Stock</button>
    </form>
</div>

<script>
document.getElementById('productSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (selected.value) {
        document.getElementById('purchasePrice').value = selected.dataset.price;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
