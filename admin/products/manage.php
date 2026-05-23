<?php
require_once '../../config/config.php';
require_once '../../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$edit_id = $_GET['id'] ?? 0;
$product = null;
$error = $success = '';

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $product = $stmt->fetch();
    if (!$product) {
        header('Location: list.php');
        exit;
    }
}

if ($_POST) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);
    $status = $_POST['status'];
    
    if (empty($name) || $price <= 0) {
        $error = 'Please fill required fields correctly';
    } else {
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../../uploads/";
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $image_name;
            }
        }
        
        if ($edit_id) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock_quantity=?, category=?, status=?, image=COALESCE(?, image) WHERE id=?");
            $stmt->execute([$name, $description, $price, $stock, $category, $status, $image, $edit_id]);
            $success = 'Product updated successfully!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $stock, $category, $status, $image]);
            $success = 'Product added successfully!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_id ? 'Edit' : 'Add'; ?> Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Same Sidebar (copy from list.php) -->
        <div class="sidebar">...</div>

        <div class="main-content">
            <div class="topbar">
                <a href="list.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Back</a>
                <h2><?php echo $edit_id ? 'Edit Product' : 'Add Product'; ?></h2>
                <div class="user-info"><?php echo $_SESSION['admin_username']; ?></div>
            </div>

            <div class="content-card">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo $product['name'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="Electronics" <?php echo ($product['category']??'')=='Electronics'?'selected':'';?>>Electronics</option>
                                <option value="Clothing" <?php echo ($product['category']??'')=='Clothing'?'selected':'';?>>Clothing</option>
                                <option value="Books" <?php echo ($product['category']??'')=='Books'?'selected':'';?>>Books</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price ($)*</label>
                            <input type="number" step="0.01" name="price" class="form-control" 
                                   value="<?php echo $product['price'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" 
                                   value="<?php echo $product['stock_quantity'] ?? ''; ?>" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo $product['description'] ?? ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($product && $product['image']): ?>
                            <img src="../../uploads/<?php echo $product['image']; ?>" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo ($product['status']??'active')=='active'?'selected':'';?>>Active</option>
                            <option value="inactive" <?php echo ($product['status']??'')=='inactive'?'selected':'';?>>Inactive</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo $edit_id ? 'Update Product' : 'Add Product'; ?>
                        </button>
                        <a href="list.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
