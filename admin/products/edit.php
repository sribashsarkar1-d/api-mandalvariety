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

$attributes = json_decode($product['attributes'] ?? '{}', true);
if (json_last_error() !== JSON_ERROR_NONE) $attributes = [];

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
    'attr_brand' => $attributes['Brand'] ?? '',
    'attr_size' => $attributes['Size'] ?? '',
    'attr_expiry' => $attributes['Expiry Date'] ?? '',
    'attr_food_type' => $attributes['Food Type'] ?? '',
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

    $data['attr_brand'] = trim($_POST['attr_brand'] ?? '');
    $data['attr_size'] = trim($_POST['attr_size'] ?? '');
    $data['attr_expiry'] = trim($_POST['attr_expiry'] ?? '');
    $data['attr_food_type'] = trim($_POST['attr_food_type'] ?? '');

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

    // Preserve existing images or replace
    $uploadedImages = productImages($product['images'] ?? '');
    $remove_images = $_POST['remove_images'] ?? []; // Future functionality

    if (!empty($remove_images)) {
        $uploadedImages = array_diff($uploadedImages, $remove_images);
        foreach ($remove_images as $r_img) {
            $p = __DIR__ . '/../uploads/' . basename($r_img);
            if (file_exists($p) && is_file($p)) {
                unlink($p);
            }
        }
    }

    // Check if POST size exceeded limit
    if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $errors[] = 'The uploaded files exceed the maximum allowed server upload size.';
    } elseif (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $errors[] = "Failed to create directory: $uploadDir. Check permissions.";
            }
        }

        foreach ($_FILES['images']['name'] as $i => $imgName) {
            $tmp = $_FILES['images']['tmp_name'][$i];
            $error = $_FILES['images']['error'][$i];
            $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
            
            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "File upload error code: $error for image $imgName. (Code 1=Exceeds php.ini size, 2=Exceeds form max size, 3=Partial upload, 4=No file)";
                continue;
            }

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $errors[] = "Only JPG, JPEG, PNG and WEBP images are allowed for image $imgName";
                continue;
            }

            if ($tmp && is_uploaded_file($tmp)) {
                $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $newName)) {
                    $uploadedImages[] = $newName;
                } else {
                    $errors[] = "Failed to move uploaded file to $uploadDir$newName. Check folder permissions.";
                }
            } else {
                $errors[] = "File $imgName was not uploaded via HTTP POST.";
            }
        }
    }

    // Attributes Array
    $attributesArray = [];
    if (!empty($data['attr_brand'])) $attributesArray['Brand'] = $data['attr_brand'];
    if (!empty($data['attr_size'])) $attributesArray['Size'] = $data['attr_size'];
    if (!empty($data['attr_expiry'])) $attributesArray['Expiry Date'] = $data['attr_expiry'];
    if (!empty($data['attr_food_type'])) $attributesArray['Food Type'] = $data['attr_food_type'];
    $attributesJson = !empty($attributesArray) ? json_encode($attributesArray) : null;

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
                stock = :stock,
                attributes = :attributes
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
                ':attributes' => $attributesJson,
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
            $errors[] = 'Update failed: ' . $ex->getMessage();
        }
    }
}
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --secondary-bg: #f8fafc;
        --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--secondary-bg);
        font-family: 'Inter', system-ui, sans-serif;
    }

    .page-header-premium {
        background: var(--primary-gradient);
        border-radius: 20px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 15px 30px rgba(16, 185, 129, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .page-header-premium::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .page-header-premium > * {
        z-index: 2;
    }

    @media (max-width: 768px) {
        .page-header-premium {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
        }
        .page-header-premium .btn {
            width: 100%;
            justify-content: center;
        }
    }

    .premium-card {
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }

    .premium-card-header {
        background: rgba(255,255,255,0.9);
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 25px;
        font-weight: 700;
        color: #1e293b;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .premium-card-body {
        padding: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .form-control-premium, .form-select-premium {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 12px 18px;
        font-size: 0.95rem;
        background: #ffffff;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control-premium:focus, .form-select-premium:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        outline: none;
        background: #fff;
    }

    .image-upload-zone {
        border: 2px dashed #94a3b8;
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .image-upload-zone:hover {
        background: #f1f5f9;
        border-color: #10b981;
    }

    .image-upload-zone i {
        font-size: 40px;
        color: #64748b;
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    .image-upload-zone:hover i {
        color: #10b981;
    }

    .btn-premium-primary {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 28px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        width: 100%;
        font-size: 1rem;
    }

    .btn-premium-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        color: white;
    }

    .btn-premium-light {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 14px 28px;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
        display: block;
        text-align: center;
        text-decoration: none;
    }

    .btn-premium-light:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .img-preview-card {
        border-radius: 12px;
        overflow: visible;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        background: white;
        padding: 5px;
        position: relative;
    }

    .img-preview-card img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        border-radius: 8px;
    }

    .remove-img-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        font-size: 12px;
        transition: all 0.2s;
        z-index: 10;
        padding: 0;
    }

    .remove-img-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }
</style>

<div class="w-100">
    <div class="container-fluid mt-4 mb-5 px-4">
        
        <div class="page-header-premium">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Product</h3>
                <p class="mb-0 text-white-50">Update product information, images, stock, and offers.</p>
            </div>
            <a href="list.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #059669;">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Inventory
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-circle-check me-2"></i><?= e($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="row g-4">
                
                <!-- Left Column -->
                <div class="col-lg-8">
                    
                    <!-- Basic Details -->
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-info-circle text-primary"></i> Basic Details
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control-premium" value="<?= e($data['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-select-premium" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($cats as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)$data['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                                <?= e($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" class="form-control-premium" value="<?= e($data['slug']) ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control-premium" value="<?= e($data['sku']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Product Description</label>
                                    <textarea name="description" class="form-control-premium" rows="4"><?= e($data['description']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory -->
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-tags text-success"></i> Pricing & Inventory
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Regular Price (₹) *</label>
                                    <input type="number" step="0.01" min="0" name="price" id="price" class="form-control-premium" value="<?= e($data['price']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Discount Price (₹)</label>
                                    <input type="number" step="0.01" min="0" name="discount_price" id="discount_price" class="form-control-premium bg-light" value="<?= e($data['discount_price']) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Available Stock</label>
                                    <input type="number" min="0" name="stock_quantity" class="form-control-premium" value="<?= e($data['stock_quantity']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.001" min="0" name="weight" class="form-control-premium" value="<?= e($data['weight']) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select-premium">
                                        <option value="1" <?= $data['is_active'] === '1' ? 'selected' : '' ?>>Active (Visible)</option>
                                        <option value="0" <?= $data['is_active'] === '0' ? 'selected' : '' ?>>Inactive (Hidden)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Attributes -->
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-list-check text-warning"></i> Product Attributes
                            <span class="badge bg-light text-muted ms-auto" style="font-size:0.75rem;">Optional</span>
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Brand</label>
                                    <input type="text" name="attr_brand" class="form-control-premium" value="<?= e($data['attr_brand']) ?>" placeholder="e.g. Nike, Lakme">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Size / Variants</label>
                                    <input type="text" name="attr_size" class="form-control-premium" value="<?= e($data['attr_size']) ?>" placeholder="e.g. 8, 9, 10 or M, L, XL">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Food Type</label>
                                    <select name="attr_food_type" class="form-select-premium">
                                        <option value="">N/A</option>
                                        <option value="Veg" <?= $data['attr_food_type'] === 'Veg' ? 'selected' : '' ?>>Vegetarian</option>
                                        <option value="Non-Veg" <?= $data['attr_food_type'] === 'Non-Veg' ? 'selected' : '' ?>>Non-Vegetarian</option>
                                        <option value="Vegan" <?= $data['attr_food_type'] === 'Vegan' ? 'selected' : '' ?>>Vegan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="attr_expiry" class="form-control-premium" value="<?= e($data['attr_expiry']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Offers -->
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-gift text-danger"></i> Offer System
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Offer Name</label>
                                    <input type="text" name="offer_name" id="offer_name" class="form-control-premium" value="<?= e($data['offer_name']) ?>" placeholder="Summer Sale">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Offer Type</label>
                                    <select name="offer_type" id="offer_type" class="form-select-premium">
                                        <option value="none" <?= $data['offer_type'] === 'none' ? 'selected' : '' ?>>None</option>
                                        <option value="flat" <?= $data['offer_type'] === 'flat' ? 'selected' : '' ?>>Flat (₹)</option>
                                        <option value="percent" <?= $data['offer_type'] === 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Value</label>
                                    <input type="number" step="0.01" min="0" name="offer_value" id="offer_value" class="form-control-premium" value="<?= e($data['offer_value']) ?>" placeholder="10">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-image text-info"></i> Current Images
                        </div>
                        <div class="premium-card-body">
                            <?php $existing = productImages($product['images'] ?? ''); ?>
                            <?php if (!empty($existing)): ?>
                                <div class="preview-container mt-0">
                                    <?php foreach ($existing as $img): ?>
                                        <div class="img-preview-card" id="existing-img-<?= md5($img) ?>">
                                            <button type="button" class="remove-img-btn" onclick="removeExistingImage('<?= e($img) ?>', 'existing-img-<?= md5($img) ?>')" title="Remove image">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                            <img src="../uploads/<?= e($img) ?>" alt="Product image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fa-solid fa-image-slash fa-2x mb-2"></i><br>
                                    No images found
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-cloud-arrow-up text-secondary"></i> Append Images
                        </div>
                        <div class="premium-card-body text-center">
                            <label for="images" class="image-upload-zone w-100 d-block p-3">
                                <i class="fa-solid fa-plus-circle" style="font-size:30px;"></i>
                                <h6 class="fw-bold text-dark mt-2 mb-0">Add More Images</h6>
                            </label>
                            <input type="file" name="images[]" id="images" class="d-none" accept=".jpg,.jpeg,.png,.webp" multiple>
                            <div id="imagePreviewContainer" class="preview-container"></div>
                        </div>
                    </div>

                    <div class="premium-card mb-4" style="background: transparent; border: none; box-shadow: none;">
                        <button type="submit" class="btn-premium-primary mb-3">
                            <i class="fa-solid fa-check-circle me-2"></i> Update Product
                        </button>
                        <a href="list.php" class="btn-premium-light">
                            Cancel
                        </a>
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
    let selectedFiles = [];

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

    function updateFileInputAndPreview() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;

        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const card = document.createElement('div');
                card.className = 'img-preview-card';
                card.innerHTML = `
                    <button type="button" class="remove-img-btn" onclick="removeNewImage(${index})" title="Remove image">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <img src="${e.target.result}" title="${file.name}">
                `;
                previewContainer.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeNewImage = function(index) {
        selectedFiles.splice(index, 1);
        updateFileInputAndPreview();
    };

    window.removeExistingImage = function(imageName, elementId) {
        if(confirm('Are you sure you want to remove this image? It will be deleted when you update the product.')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_images[]';
            input.value = imageName;
            document.getElementById('productForm').appendChild(input);
            
            const el = document.getElementById(elementId);
            if(el) el.remove();
        }
    };

    priceInput.addEventListener('input', updateDiscountPrice);
    offerTypeInput.addEventListener('change', updateDiscountPrice);
    offerValueInput.addEventListener('input', updateDiscountPrice);

    imageInput.addEventListener('change', function () {
        Array.from(this.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
            }
        });
        updateFileInputAndPreview();
    });

    updateDiscountPrice();
});
</script>

<?php include '../includes/footer.php'; ?>