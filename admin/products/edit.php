<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('productImages')) {
    function productImages($images)
    {
        if (empty($images)) return [];
        $images = trim($images);

        $json = json_decode($images, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return array_values(array_filter(array_map('trim', $json)));
        }

        return array_values(array_filter(array_map('trim', explode(',', $images))));
    }
}

if (!function_exists('makeSlug')) {
    function makeSlug($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\-\s]/', '', $text);
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('uniqueSlug')) {
    function uniqueSlug(PDO $conn, $slug, $currentId = 0)
    {
        $base = $slug ?: 'product';
        $newSlug = $base;
        $i = 1;

        while (true) {
            $stmt = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $stmt->execute([$newSlug, $currentId]);
            if (!$stmt->fetch()) return $newSlug;
            $newSlug = $base . '-' . $i++;
        }
    }
}

if (!function_exists('uniqueSku')) {
    function uniqueSku(PDO $conn, $sku, $currentId = 0)
    {
        $base = strtoupper(trim($sku ?: 'SKU' . time()));
        $newSku = $base;
        $i = 1;

        while (true) {
            $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
            $stmt->execute([$newSku, $currentId]);
            if (!$stmt->fetch()) return $newSku;
            $newSku = $base . '-' . $i++;
        }
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid product ID');
}

$errors = [];
$success = '';

$cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Product not found');
}

$offerStmt = $conn->prepare("
    SELECT *
    FROM offers
    WHERE product_id = ? OR category_id = ?
    ORDER BY priority DESC, id DESC
    LIMIT 1
");
$offerStmt->execute([$id, $product['category_id']]);
$offer = $offerStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$data = [
    'name' => $product['name'] ?? '',
    'slug' => $product['slug'] ?? '',
    'description' => $product['description'] ?? '',
    'price' => $product['price'] ?? '',
    'discount_price' => $product['discount_price'] ?? '',
    'sku' => $product['sku'] ?? '',
    'stock_quantity' => $product['stock_quantity'] ?? 0,
    'stock' => $product['stock'] ?? 0,
    'category_id' => $product['category_id'] ?? '',
    'weight' => $product['weight'] ?? '',
    'is_active' => (string)($product['is_active'] ?? '1'),
    'offer_name' => $offer['offer_name'] ?? '',
    'offer_type' => $offer['offer_type'] ?? 'none',
    'offer_value' => $offer['offer_value'] ?? '',
    'offer_start' => $offer['start_date'] ?? '',
    'offer_end' => $offer['end_date'] ?? '',
    'offer_target' => !empty($offer['product_id']) ? 'product' : 'category',
    'priority' => $offer['priority'] ?? 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name'] = trim($_POST['name'] ?? '');
    $data['slug'] = trim($_POST['slug'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['price'] = $_POST['price'] ?? '';
    $data['discount_price'] = $_POST['discount_price'] ?? '';
    $data['sku'] = trim($_POST['sku'] ?? '');
    $data['stock_quantity'] = (int)($_POST['stock_quantity'] ?? 0);
    $data['stock'] = (int)($_POST['stock'] ?? 0);
    $data['category_id'] = (int)($_POST['category_id'] ?? 0);
    $data['weight'] = ($_POST['weight'] ?? '') !== '' ? (float)$_POST['weight'] : '';
    $data['is_active'] = (string)($_POST['is_active'] ?? '1');

    $data['offer_name'] = trim($_POST['offer_name'] ?? '');
    $data['offer_type'] = $_POST['offer_type'] ?? 'none';
    $data['offer_value'] = $_POST['offer_value'] ?? '';
    $data['offer_start'] = $_POST['offer_start'] ?? '';
    $data['offer_end'] = $_POST['offer_end'] ?? '';
    $data['offer_target'] = $_POST['offer_target'] ?? 'product';
    $data['priority'] = (int)($_POST['priority'] ?? 0);

    if ($data['name'] === '') $errors[] = 'Product name required';
    if ($data['price'] === '' || !is_numeric($data['price'])) $errors[] = 'Valid price required';
    if ($data['category_id'] <= 0) $errors[] = 'Category required';

    if ($data['discount_price'] !== '' && !is_numeric($data['discount_price'])) {
        $errors[] = 'Discount price must be numeric';
    }

    if ($data['offer_type'] !== 'none') {
        if ($data['offer_name'] === '') $errors[] = 'Offer name required when offer is enabled';
        if ($data['offer_value'] === '' || !is_numeric($data['offer_value'])) $errors[] = 'Valid offer value required';
        if (!in_array($data['offer_target'], ['product', 'category'], true)) {
            $errors[] = 'Invalid offer target';
        }
    }

    $uploadedImages = productImages($product['images'] ?? '');

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['images']['name'] as $i => $imgName) {
            $tmp = $_FILES['images']['tmp_name'][$i];
            $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $errors[] = 'Only JPG, JPEG, PNG and WEBP images are allowed';
                continue;
            }

            if (is_uploaded_file($tmp)) {
                $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
                move_uploaded_file($tmp, $uploadDir . $newName);
                $uploadedImages[] = $newName;
            }
        }
    }

    if (empty($errors)) {
        $slug = uniqueSlug($conn, makeSlug($data['slug'] ?: $data['name']), $id);
        $sku = uniqueSku($conn, $data['sku'] ?: strtoupper(substr($data['name'], 0, 5)) . rand(100, 999), $id);
        $imagesJson = json_encode(array_values(array_filter($uploadedImages)));

        $conn->beginTransaction();

        try {
            $stmt = $conn->prepare("UPDATE products SET
                name = :name,
                slug = :slug,
                description = :description,
                price = :price,
                discount_price = :discount_price,
                sku = :sku,
                stock_quantity = :stock_quantity,
                category_id = :category_id,
                images = :images,
                weight = :weight,
                is_active = :is_active,
                stock = :stock
                WHERE id = :id
            ");

            $stmt->execute([
                ':name' => $data['name'],
                ':slug' => $slug,
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':discount_price' => $data['discount_price'] !== '' ? $data['discount_price'] : null,
                ':sku' => $sku,
                ':stock_quantity' => $data['stock_quantity'],
                ':category_id' => $data['category_id'],
                ':images' => $imagesJson,
                ':weight' => $data['weight'] !== '' ? $data['weight'] : null,
                ':is_active' => $data['is_active'],
                ':stock' => $data['stock'],
                ':id' => $id,
            ]);

            $conn->prepare("DELETE FROM offers WHERE product_id = ? OR category_id = ?")->execute([$id, $product['category_id']]);

            if (!empty($data['offer_type']) && $data['offer_type'] !== 'none') {
                $stmtOffer = $conn->prepare("INSERT INTO offers
                    (product_id, category_id, offer_name, offer_type, offer_value, start_date, end_date, priority)
                    VALUES
                    (:product_id, :category_id, :offer_name, :offer_type, :offer_value, :start_date, :end_date, :priority)");

                $stmtOffer->execute([
                    ':product_id' => $data['offer_target'] === 'product' ? $id : null,
                    ':category_id' => $data['offer_target'] === 'category' ? $data['category_id'] : null,
                    ':offer_name' => $data['offer_name'],
                    ':offer_type' => $data['offer_type'],
                    ':offer_value' => $data['offer_value'],
                    ':start_date' => $data['offer_start'] ?: null,
                    ':end_date' => $data['offer_end'] ?: null,
                    ':priority' => $data['priority']
                ]);
            }

            $conn->commit();
            $success = "Product updated successfully";

            $stmt = $conn->prepare("
                SELECT p.*, c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $conn->rollBack();
            $errors[] = 'Update failed';
        }
    }
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">✏️ Edit Product</h4>
                <small class="text-muted">Update product information, images, stock, and offer details</small>
            </div>
            <a href="list.php" class="btn btn-outline-secondary">← Back to List</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Basic Details</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?= e($data['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control" value="<?= e($data['slug']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control" value="<?= e($data['sku']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($cats as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)$data['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                                <?= e($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="5"><?= e($data['description']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Price & Stock</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Price *</label>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= e($data['price']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Discount Price</label>
                                    <input type="number" step="0.01" min="0" name="discount_price" class="form-control" value="<?= e($data['discount_price']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.001" min="0" name="weight" class="form-control" value="<?= e($data['weight']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" min="0" name="stock_quantity" class="form-control" value="<?= e($data['stock_quantity']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock</label>
                                    <input type="number" min="0" name="stock" class="form-control" value="<?= e($data['stock']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" <?= $data['is_active'] === '1' ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= $data['is_active'] === '0' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Offer System</strong></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Offer Name</label>
                                    <input type="text" name="offer_name" class="form-control" value="<?= e($data['offer_name']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Offer Type</label>
                                    <select name="offer_type" class="form-select">
                                        <option value="none" <?= $data['offer_type'] === 'none' ? 'selected' : '' ?>>No Offer</option>
                                        <option value="flat" <?= $data['offer_type'] === 'flat' ? 'selected' : '' ?>>Flat Discount</option>
                                        <option value="percent" <?= $data['offer_type'] === 'percent' ? 'selected' : '' ?>>Percent Discount</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Offer Value</label>
                                    <input type="number" step="0.01" min="0" name="offer_value" class="form-control" value="<?= e($data['offer_value']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Offer Apply To</label>
                                    <select name="offer_target" class="form-select">
                                        <option value="product" <?= $data['offer_target'] === 'product' ? 'selected' : '' ?>>This Product</option>
                                        <option value="category" <?= $data['offer_target'] === 'category' ? 'selected' : '' ?>>Whole Category</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Offer Start</label>
                                    <input type="date" name="offer_start" class="form-control" value="<?= e($data['offer_start']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Offer End</label>
                                    <input type="date" name="offer_end" class="form-control" value="<?= e($data['offer_end']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Priority</label>
                                    <input type="number" min="0" name="priority" class="form-control" value="<?= e($data['priority']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Existing Images</strong></div>
                        <div class="card-body">
                            <?php $existing = productImages($product['images'] ?? ''); ?>
                            <?php if (!empty($existing)): ?>
                                <div class="row g-2">
                                    <?php foreach ($existing as $img): ?>
                                        <div class="col-6">
                                            <img src="../uploads/<?= e($img) ?>" class="img-fluid rounded border" alt="Product image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">No images found.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Add More Images</strong></div>
                        <div class="card-body">
                            <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
                            <small class="text-muted d-block mt-2">New uploaded images will be appended to the current gallery.</small>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Publish</strong></div>
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Product</button>
                            <a href="list.php" class="btn btn-light border">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>