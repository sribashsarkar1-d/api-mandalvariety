<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function makeSlug($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-\s]/', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(PDO $conn, $slug)
{
    $base = $slug ?: 'product';
    $newSlug = $base;
    $i = 1;

    while (true) {
        $stmt = $conn->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$newSlug]);
        if (!$stmt->fetch()) {
            return $newSlug;
        }
        $newSlug = $base . '-' . $i++;
    }
}

function uniqueSku(PDO $conn, $sku)
{
    $base = strtoupper(trim($sku ?: 'SKU' . time()));
    $newSku = $base;
    $i = 1;

    while (true) {
        $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$newSku]);
        if (!$stmt->fetch()) {
            return $newSku;
        }
        $newSku = $base . '-' . $i++;
    }
}

function calculateDiscountPrice($price, $offerType, $offerValue)
{
    $price = (float)$price;
    $offerValue = (float)$offerValue;

    if ($price <= 0 || $offerValue <= 0 || $offerType === 'none') {
        return null;
    }

    if ($offerType === 'flat') {
        $discountPrice = $price - $offerValue;
    } elseif ($offerType === 'percent') {
        $discountPrice = $price - (($price * $offerValue) / 100);
    } else {
        return null;
    }

    if ($discountPrice < 0) {
        $discountPrice = 0;
    }

    return number_format($discountPrice, 2, '.', '');
}

$errors = [];
$success = '';

$data = [
    'name' => '',
    'slug' => '',
    'description' => '',
    'price' => '',
    'discount_price' => '',
    'sku' => '',
    'stock_quantity' => 0,
    'stock' => 0,
    'category_id' => '',
    'weight' => '',
    'is_active' => '1',
    'offer_name' => '',
    'offer_type' => 'none',
    'offer_value' => '',
    'offer_start' => '',
    'offer_end' => '',
    'offer_target' => 'product',
    'priority' => 0,
];

$categories = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name'] = trim($_POST['name'] ?? '');
    $data['slug'] = trim($_POST['slug'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['price'] = trim($_POST['price'] ?? '');
    $data['sku'] = trim($_POST['sku'] ?? '');
    $data['stock_quantity'] = (int)($_POST['stock_quantity'] ?? 0);
    $data['stock'] = (int)($_POST['stock'] ?? 0);
    $data['category_id'] = (int)($_POST['category_id'] ?? 0);
    $data['weight'] = ($_POST['weight'] ?? '') !== '' ? (float)$_POST['weight'] : '';
    $data['is_active'] = (string)($_POST['is_active'] ?? '1');

    $data['offer_name'] = trim($_POST['offer_name'] ?? '');
    $data['offer_type'] = trim($_POST['offer_type'] ?? 'none');
    $data['offer_value'] = trim($_POST['offer_value'] ?? '');
    $data['offer_start'] = trim($_POST['offer_start'] ?? '');
    $data['offer_end'] = trim($_POST['offer_end'] ?? '');
    $data['offer_target'] = trim($_POST['offer_target'] ?? 'product');
    $data['priority'] = (int)($_POST['priority'] ?? 0);

    if ($data['name'] === '') $errors[] = 'Product name required';
    if ($data['price'] === '' || !is_numeric($data['price'])) $errors[] = 'Valid price required';
    if ($data['category_id'] <= 0) $errors[] = 'Category required';

    if (!in_array($data['offer_type'], ['none', 'flat', 'percent'], true)) {
        $errors[] = 'Invalid offer type';
    }

    if (!in_array($data['offer_target'], ['product', 'category'], true)) {
        $errors[] = 'Invalid offer target';
    }

    if ($data['offer_type'] !== 'none') {
        if ($data['offer_name'] === '') $errors[] = 'Offer name required';
        if ($data['offer_value'] === '' || !is_numeric($data['offer_value']) || (float)$data['offer_value'] <= 0) {
            $errors[] = 'Valid offer value required';
        }
        if ($data['offer_type'] === 'percent' && (float)$data['offer_value'] > 100) {
            $errors[] = 'Percentage offer cannot be more than 100';
        }
        if ($data['offer_start'] !== '' && $data['offer_end'] !== '' && $data['offer_start'] > $data['offer_end']) {
            $errors[] = 'Offer end date must be after start date';
        }
    }

    if (empty($errors)) {
        $data['discount_price'] = calculateDiscountPrice($data['price'], $data['offer_type'], $data['offer_value']);
    }

    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($_FILES['images']['name'] as $i => $imgName) {
            $tmp = $_FILES['images']['tmp_name'][$i] ?? '';
            $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Only JPG, JPEG, PNG and WEBP files are allowed';
                continue;
            }

            if ($tmp && is_uploaded_file($tmp)) {
                $newName = time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $newName)) {
                    $uploadedImages[] = $newName;
                }
            }
        }
    } else {
        $errors[] = 'At least one image is required';
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $slug = uniqueSlug($conn, makeSlug($data['slug'] ?: $data['name']));
            $sku = uniqueSku($conn, $data['sku'] ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 5)) . rand(100, 999));
            $imagesJson = json_encode($uploadedImages);

            $stmt = $conn->prepare("INSERT INTO products
                (name, slug, description, price, discount_price, sku, stock_quantity, category_id, images, weight, is_active, stock)
                VALUES
                (:name, :slug, :description, :price, :discount_price, :sku, :stock_quantity, :category_id, :images, :weight, :is_active, :stock)");

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
                ':is_active' => (int)$data['is_active'],
                ':stock' => $data['stock'],
            ]);

            $productId = $conn->lastInsertId();

            if ($data['offer_type'] !== 'none' && $data['offer_value'] !== '' && is_numeric($data['offer_value'])) {
                $stmtOffer = $conn->prepare("INSERT INTO offers
                    (product_id, category_id, offer_name, offer_type, offer_value, start_date, end_date, priority)
                    VALUES
                    (:product_id, :category_id, :offer_name, :offer_type, :offer_value, :start_date, :end_date, :priority)");

                $stmtOffer->execute([
                    ':product_id' => $data['offer_target'] === 'product' ? $productId : null,
                    ':category_id' => $data['offer_target'] === 'category' ? $data['category_id'] : null,
                    ':offer_name' => $data['offer_name'],
                    ':offer_type' => $data['offer_type'],
                    ':offer_value' => $data['offer_value'],
                    ':start_date' => $data['offer_start'] !== '' ? $data['offer_start'] : null,
                    ':end_date' => $data['offer_end'] !== '' ? $data['offer_end'] : null,
                    ':priority' => $data['priority']
                ]);
            }

            $conn->commit();
            $success = 'Product created successfully';

            $data = [
                'name' => '',
                'slug' => '',
                'description' => '',
                'price' => '',
                'discount_price' => '',
                'sku' => '',
                'stock_quantity' => 0,
                'stock' => 0,
                'category_id' => '',
                'weight' => '',
                'is_active' => '1',
                'offer_name' => '',
                'offer_type' => 'none',
                'offer_value' => '',
                'offer_start' => '',
                'offer_end' => '',
                'offer_target' => 'product',
                'priority' => 0,
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'Failed to save product. ' . $e->getMessage();
        }
    }
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">➕ Add Product</h4>
                <small class="text-muted">Create product with multiple images, pricing, stock and offer details</small>
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

        <form method="POST" enctype="multipart/form-data" id="productForm">
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
                                    <input type="text" name="slug" class="form-control" value="<?= e($data['slug']) ?>" placeholder="auto-generate-if-empty">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control" value="<?= e($data['sku']) ?>" placeholder="auto-generate-if-empty">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)$data['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                                <?= e($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="5" placeholder="Enter product details..."><?= e($data['description']) ?></textarea>
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
                                    <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" value="<?= e($data['price']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Discount Price</label>
                                    <input type="number" step="0.01" min="0" name="discount_price" id="discount_price" class="form-control" value="<?= e($data['discount_price']) ?>" readonly>
                                    <small class="text-muted">Auto-calculated</small>
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
                                    <input type="text" name="offer_name" id="offer_name" class="form-control" value="<?= e($data['offer_name']) ?>" placeholder="Summer Sale / Eid Offer">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Offer Type</label>
                                    <select name="offer_type" id="offer_type" class="form-select">
                                        <option value="none" <?= $data['offer_type'] === 'none' ? 'selected' : '' ?>>No Offer</option>
                                        <option value="flat" <?= $data['offer_type'] === 'flat' ? 'selected' : '' ?>>Flat Discount</option>
                                        <option value="percent" <?= $data['offer_type'] === 'percent' ? 'selected' : '' ?>>Percent Discount</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Offer Value</label>
                                    <input type="number" step="0.01" min="0" name="offer_value" id="offer_value" class="form-control" value="<?= e($data['offer_value']) ?>" placeholder="100 or 10">
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
                        <div class="card-header bg-white"><strong>Product Images</strong></div>
                        <div class="card-body">
                            <label class="form-label">Upload Multiple Images *</label>
                            <input type="file" name="images[]" id="images" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple required>
                            <small class="text-muted d-block mt-2">Allowed: JPG, JPEG, PNG, WEBP</small>

                            <div id="imagePreviewContainer" class="row g-2 mt-3"></div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white"><strong>Publish</strong></div>
                        <div class="card-body d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Product</button>
                            <a href="list.php" class="btn btn-light border">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const priceInput = document.getElementById('price');
    const offerTypeInput = document.getElementById('offer_type');
    const offerValueInput = document.getElementById('offer_value');
    const discountPriceInput = document.getElementById('discount_price');
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreviewContainer');

    function updateDiscountPrice() {
        const price = parseFloat(priceInput.value) || 0;
        const offerType = offerTypeInput.value;
        const offerValue = parseFloat(offerValueInput.value);

        if (!price || offerType === 'none' || isNaN(offerValue) || offerValue <= 0) {
            discountPriceInput.value = '';
            return;
        }

        let discountPrice = price;

        if (offerType === 'flat') {
            discountPrice = price - offerValue;
        } else if (offerType === 'percent') {
            discountPrice = price - ((price * offerValue) / 100);
        }

        if (discountPrice < 0) {
            discountPrice = 0;
        }

        discountPriceInput.value = discountPrice.toFixed(2);
    }

    function previewImages(files) {
        previewContainer.innerHTML = '';

        Array.from(files).forEach((file) => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();

            reader.onload = function (e) {
                const col = document.createElement('div');
                col.className = 'col-6';

                col.innerHTML = `
                    <div class="border rounded p-2 bg-light">
                        <img src="${e.target.result}" class="img-fluid rounded mb-2" style="height:120px;width:100%;object-fit:cover;">
                        <small class="text-muted d-block text-truncate">${file.name}</small>
                    </div>
                `;

                previewContainer.appendChild(col);
            };

            reader.readAsDataURL(file);
        });
    }

    priceInput.addEventListener('input', updateDiscountPrice);
    offerTypeInput.addEventListener('change', updateDiscountPrice);
    offerValueInput.addEventListener('input', updateDiscountPrice);

    imageInput.addEventListener('change', function () {
        previewImages(this.files);
    });

    updateDiscountPrice();
});
</script>

<?php include '../includes/footer.php'; ?>