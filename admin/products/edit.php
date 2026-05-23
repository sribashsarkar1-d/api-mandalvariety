<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) { return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8'); }
}

function makeSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-\s]/', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(PDO $conn, $slug, $currentId = 0) {
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('Invalid product ID');

$errors = [];
$success = '';

$categories = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Basic Information
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // 2. Pricing & Inventory
    $sku = trim($_POST['sku'] ?? '');
    $barcode = trim($_POST['barcode'] ?? '');
    $cost_price = trim($_POST['cost_price'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $discount_price = trim($_POST['discount_price'] ?? '');
    $tax_percent = trim($_POST['tax_percent'] ?? '0');
    $hsn_or_tax_code = trim($_POST['hsn_or_tax_code'] ?? '');
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $min_stock_alert = (int)($_POST['min_stock_alert'] ?? 5);

    // 3. SEO
    $seo_meta_title = trim($_POST['seo_meta_title'] ?? '');
    $seo_meta_description = trim($_POST['seo_meta_description'] ?? '');
    $seo_keywords = trim($_POST['seo_keywords'] ?? '');

    // 4. Publish Settings
    $status = trim($_POST['status'] ?? 'published');
    $is_active = ($status === 'published') ? 1 : 0;
    $featured_product = isset($_POST['featured_product']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $tags = trim($_POST['tags'] ?? '');

    // 5. Meta Data
    $meta_data = [];
    if (isset($_POST['meta']) && is_array($_POST['meta'])) {
        foreach ($_POST['meta'] as $key => $val) {
            $meta_data[$key] = is_array($val) ? implode(',', $val) : trim($val);
        }
    }
    $meta_data_json = json_encode($meta_data);

    if ($name === '') $errors[] = 'Product name is required';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Valid selling price is required';
    if ($category_id <= 0) $errors[] = 'Category is required';

    // 6. Media
    $stmtImg = $conn->prepare("SELECT images FROM products WHERE id = ?");
    $stmtImg->execute([$id]);
    $existingImagesJson = $stmtImg->fetchColumn();
    $uploadedImages = $existingImagesJson ? json_decode($existingImagesJson, true) : [];
    if (!is_array($uploadedImages)) $uploadedImages = [];

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($_FILES['images']['name'] as $i => $imgName) {
            $tmp = $_FILES['images']['tmp_name'][$i] ?? '';
            $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) continue;
            if ($tmp && is_uploaded_file($tmp)) {
                $newName = time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $newName)) {
                    $uploadedImages[] = $newName;
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $finalSlug = uniqueSlug($conn, makeSlug($slug ?: $name), $id);
            if (!$sku) $sku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 5)) . rand(1000, 9999);
            $imagesJson = json_encode(array_values(array_filter($uploadedImages)));

            $stmt = $conn->prepare("UPDATE products SET
                name = :name, slug = :slug, brand = :brand, category_id = :category_id, 
                short_description = :short_desc, description = :description,
                sku = :sku, barcode = :barcode, cost_price = :cost, price = :price, 
                discount_price = :discount, tax_percent = :tax, hsn_or_tax_code = :hsn,
                stock_quantity = :stock, stock = :stock, min_stock_alert = :min_alert, 
                seo_meta_title = :seo_title, seo_meta_description = :seo_desc, seo_keywords = :seo_kw,
                status = :status, is_active = :is_active, featured_product = :featured, 
                new_arrival = :new_arr, tags = :tags, meta_data = :meta_data, images = :images
                WHERE id = :id
            ");

            $stmt->execute([
                ':name' => $name,
                ':slug' => $finalSlug,
                ':brand' => $brand,
                ':category_id' => $category_id,
                ':short_desc' => $short_description,
                ':description' => $description,
                ':sku' => $sku,
                ':barcode' => $barcode,
                ':cost' => $cost_price !== '' ? $cost_price : null,
                ':price' => $price,
                ':discount' => $discount_price !== '' ? $discount_price : null,
                ':tax' => $tax_percent !== '' ? $tax_percent : 0,
                ':hsn' => $hsn_or_tax_code,
                ':stock' => $stock_quantity,
                ':min_alert' => $min_stock_alert,
                ':seo_title' => $seo_meta_title,
                ':seo_desc' => $seo_meta_description,
                ':seo_kw' => $seo_keywords,
                ':status' => $status,
                ':is_active' => $is_active,
                ':featured' => $featured_product,
                ':new_arr' => $new_arrival,
                ':tags' => $tags,
                ':meta_data' => $meta_data_json,
                ':images' => $imagesJson,
                ':id' => $id
            ]);

            // Delete old variants
            $conn->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);

            // Insert new variants
            if (isset($_POST['variants']['name']) && is_array($_POST['variants']['name'])) {
                $varStmt = $conn->prepare("INSERT INTO product_variants (
                    product_id, variant_name, variant_sku, variant_barcode, price, discount_price, stock_quantity, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($_POST['variants']['name'] as $vIdx => $vName) {
                    $vName = trim($vName);
                    if (!$vName) continue;

                    $vSku = trim($_POST['variants']['sku'][$vIdx] ?? '');
                    $vBarcode = trim($_POST['variants']['barcode'][$vIdx] ?? '');
                    $vPrice = trim($_POST['variants']['price'][$vIdx] ?? '');
                    $vDiscount = trim($_POST['variants']['discount'][$vIdx] ?? '');
                    $vStock = (int)($_POST['variants']['stock'][$vIdx] ?? 0);

                    if (!$vPrice) $vPrice = $price;

                    $varStmt->execute([
                        $id, $vName, $vSku, $vBarcode, $vPrice, 
                        $vDiscount !== '' ? $vDiscount : null, $vStock, 'active'
                    ]);
                }
            }

            $conn->commit();
            $success = "Product updated successfully!";
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "Failed to update product: " . $e->getMessage();
        }
    }
}

// Fetch Product Data
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) die('Product not found');

$varStmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$varStmt->execute([$id]);
$product_variants = $varStmt->fetchAll(PDO::FETCH_ASSOC);

$metaData = json_decode($product['meta_data'] ?? '{}', true) ?: [];
$imagesArray = [];
$decodedImages = json_decode($product['images'] ?? '[]', true);
if(is_array($decodedImages)) $imagesArray = $decodedImages;

// Use $_POST for validation failure redraw, else use DB data
$form = $_POST ?: $product;
// Checkboxes override
if(empty($_POST)) {
    $form['featured_product'] = $product['featured_product'] ?? 0;
    $form['new_arrival'] = $product['new_arrival'] ?? 0;
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Product</h4>
                <p class="text-muted small mb-0">Update information and manage dynamic attributes</p>
            </div>
            <a href="list.php" class="btn btn-outline-secondary px-4">Back to Products</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger bg-danger-subtle border-0">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success bg-success-subtle border-0"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="advancedProductForm">
            <div class="row g-4">
                <div class="col-lg-8">
                    <ul class="nav nav-pills mb-4 gap-2" id="productTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active px-4 rounded-pill" data-bs-toggle="pill" data-bs-target="#basic" type="button">Basic Info</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" data-bs-toggle="pill" data-bs-target="#pricing" type="button">Pricing & Stock</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" data-bs-toggle="pill" data-bs-target="#dynamic" type="button">Category Specifics</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" data-bs-toggle="pill" data-bs-target="#variants" type="button">Variants</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" data-bs-toggle="pill" data-bs-target="#seo" type="button">SEO</button></li>
                    </ul>

                    <div class="tab-content bg-white p-4 rounded-4 shadow-sm" id="productTabsContent">
                        
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <h5 class="mb-4 fw-bold">Basic Information</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?= e($form['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" id="categorySelect" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" data-name="<?= strtolower($cat['name']) ?>" <?= ((int)($form['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Brand</label>
                                    <input type="text" name="brand" class="form-control" value="<?= e($form['brand'] ?? '') ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Slug (URL)</label>
                                    <input type="text" name="slug" class="form-control" value="<?= e($form['slug'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="short_description" class="form-control" rows="2"><?= e($form['short_description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full Description</label>
                                    <textarea name="description" class="form-control" rows="5"><?= e($form['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pricing" role="tabpanel">
                            <h5 class="mb-4 fw-bold">Pricing & Inventory</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Selling Price *</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="<?= e($form['price'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Discounted Price</label>
                                    <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= e($form['discount_price'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cost Price</label>
                                    <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= e($form['cost_price'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax / GST (%)</label>
                                    <input type="number" step="0.01" name="tax_percent" class="form-control" value="<?= e($form['tax_percent'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">HSN or Tax Code</label>
                                    <input type="text" name="hsn_or_tax_code" class="form-control" value="<?= e($form['hsn_or_tax_code'] ?? '') ?>">
                                </div>
                                <div class="col-12"><hr class="text-muted"></div>
                                <div class="col-md-4">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control" value="<?= e($form['sku'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Barcode</label>
                                    <input type="text" name="barcode" class="form-control" value="<?= e($form['barcode'] ?? '') ?>">
                                </div>
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" name="stock_quantity" class="form-control" value="<?= e($form['stock_quantity'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Min Stock Alert</label>
                                    <input type="number" name="min_stock_alert" class="form-control" value="<?= e($form['min_stock_alert'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="dynamic" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Category Specific Details</h5>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2" id="currentCategoryBadge">No Category Selected</span>
                            </div>
                            <div id="dynamicFieldsContainer" class="row g-3"></div>
                        </div>

                        <div class="tab-pane fade" id="variants" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Product Variants</h5>
                                </div>
                                <button type="button" class="btn btn-sm btn-dark px-3 rounded-pill" id="addVariantBtn">+ Add Variant</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th>Variant Name*</th>
                                            <th>SKU</th>
                                            <th>Price (₹)</th>
                                            <th>Discount (₹)</th>
                                            <th>Stock</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantTableBody">
                                        <?php if(empty($product_variants) && empty($_POST['variants']['name'])): ?>
                                            <tr id="noVariantsRow"><td colspan="6" class="text-center py-4 text-muted">No variants added.</td></tr>
                                        <?php else: ?>
                                            <?php 
                                            // Handling repopulation from form submission or db
                                            $vNames = $_POST['variants']['name'] ?? array_column($product_variants, 'variant_name');
                                            $vSkus = $_POST['variants']['sku'] ?? array_column($product_variants, 'variant_sku');
                                            $vPrices = $_POST['variants']['price'] ?? array_column($product_variants, 'price');
                                            $vDiscounts = $_POST['variants']['discount'] ?? array_column($product_variants, 'discount_price');
                                            $vStocks = $_POST['variants']['stock'] ?? array_column($product_variants, 'stock_quantity');

                                            foreach ($vNames as $vIdx => $vName): 
                                                if(empty($vName)) continue;
                                            ?>
                                            <tr>
                                                <td><input type="text" name="variants[name][]" class="form-control form-control-sm" value="<?= e($vName) ?>" required></td>
                                                <td><input type="text" name="variants[sku][]" class="form-control form-control-sm" value="<?= e($vSkus[$vIdx] ?? '') ?>"></td>
                                                <td><input type="number" step="0.01" name="variants[price][]" class="form-control form-control-sm" value="<?= e($vPrices[$vIdx] ?? '') ?>"></td>
                                                <td><input type="number" step="0.01" name="variants[discount][]" class="form-control form-control-sm" value="<?= e($vDiscounts[$vIdx] ?? '') ?>"></td>
                                                <td><input type="number" name="variants[stock][]" class="form-control form-control-sm" value="<?= e($vStocks[$vIdx] ?? '0') ?>"></td>
                                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-variant"><i class="bi bi-trash"></i></button></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="seo" role="tabpanel">
                            <h5 class="mb-4 fw-bold">Search Engine Optimization</h5>
                            <div class="row g-3">
                                <div class="col-12"><label class="form-label">Meta Title</label><input type="text" name="seo_meta_title" class="form-control" value="<?= e($form['seo_meta_title'] ?? '') ?>"></div>
                                <div class="col-12"><label class="form-label">Meta Description</label><textarea name="seo_meta_description" class="form-control" rows="3"><?= e($form['seo_meta_description'] ?? '') ?></textarea></div>
                                <div class="col-12"><label class="form-label">Meta Keywords</label><input type="text" name="seo_keywords" class="form-control" value="<?= e($form['seo_keywords'] ?? '') ?>"></div>
                                <div class="col-12"><label class="form-label">Search Tags</label><input type="text" name="tags" class="form-control" value="<?= e($form['tags'] ?? '') ?>"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Product Media</h5>
                            <?php if(!empty($imagesArray)): ?>
                                <div class="row g-2 mb-3">
                                    <?php foreach($imagesArray as $img): ?>
                                        <div class="col-4"><div class="border rounded p-1"><img src="../uploads/<?= e($img) ?>" class="img-fluid rounded" style="aspect-ratio:1;object-fit:cover;width:100%;"></div></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">ADD NEW IMAGES</label>
                                <input type="file" name="images[]" id="images" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
                                <small class="text-muted mt-1 d-block">These will be added to the gallery.</small>
                            </div>
                            <div id="imagePreviewContainer" class="row g-2 mt-2"></div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Publish Settings</h5>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">STATUS</label>
                                <select name="status" class="form-select">
                                    <option value="published" <?= ($form['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                                    <option value="draft" <?= ($form['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="inactive" <?= ($form['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">PRODUCT FLAGS</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" name="featured_product" id="featured_product" <?= !empty($form['featured_product']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="featured_product">Mark as Featured</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="new_arrival" id="new_arrival" <?= !empty($form['new_arrival']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="new_arrival">Mark as New Arrival</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">Update Product</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const existingMetaData = <?= json_encode($metaData) ?>;

const catConfigs = {
    'dress': [
        {name: 'department', label: 'Department', type: 'select', options: ['Men', 'Women', 'Boys', 'Girls', 'Children', 'Unisex']},
        {name: 'fabric', label: 'Fabric/Material', type: 'text'},
        {name: 'sleeve_type', label: 'Sleeve Type', type: 'select', options: ['Full Sleeve', 'Half Sleeve', 'Sleeveless']},
        {name: 'fit_type', label: 'Fit Type', type: 'select', options: ['Regular', 'Slim Fit', 'Loose']},
        {name: 'pattern', label: 'Pattern', type: 'text'},
        {name: 'wash_care', label: 'Wash Care', type: 'text'},
        {name: 'occasion', label: 'Occasion', type: 'text'}
    ],
    'shoes': [
        {name: 'department', label: 'Department', type: 'select', options: ['Men', 'Women', 'Boys', 'Girls', 'Children']},
        {name: 'shoe_type', label: 'Shoe Type', type: 'text'},
        {name: 'material', label: 'Upper Material', type: 'text'},
        {name: 'sole_material', label: 'Sole Material', type: 'text'},
        {name: 'closure_type', label: 'Closure Type', type: 'select', options: ['Lace-up', 'Slip-on', 'Velcro']},
        {name: 'occasion', label: 'Occasion', type: 'text'}
    ],
    'food': [
        {name: 'food_type', label: 'Food Type', type: 'text'},
        {name: 'veg_nonveg', label: 'Dietary Preference', type: 'select', options: ['Vegetarian', 'Non-Vegetarian', 'Egg']},
        {name: 'ingredients', label: 'Ingredients', type: 'textarea'},
        {name: 'nutrition_info', label: 'Nutrition Info', type: 'textarea'},
        {name: 'manufacturing_date', label: 'Manufacturing Date', type: 'date'},
        {name: 'expiry_date', label: 'Expiry Date', type: 'date'},
        {name: 'fssai_info', label: 'FSSAI License No.', type: 'text'},
        {name: 'allergy_info', label: 'Allergy Information', type: 'text'}
    ],
    'grocery': [
        {name: 'grocery_type', label: 'Grocery Type', type: 'text'},
        {name: 'packaging_type', label: 'Packaging Type', type: 'text'},
        {name: 'manufacturing_date', label: 'Manufacturing Date', type: 'date'},
        {name: 'expiry_date', label: 'Expiry Date', type: 'date'},
        {name: 'shelf_life', label: 'Shelf Life', type: 'text'}
    ],
    'cosmetics': [
        {name: 'skin_type', label: 'Suitable Skin Type', type: 'select', options: ['All Skin Types', 'Dry', 'Oily', 'Sensitive']},
        {name: 'finish_type', label: 'Finish Type', type: 'text'},
        {name: 'spf_level', label: 'SPF Level', type: 'text'},
        {name: 'ingredients', label: 'Ingredients', type: 'textarea'},
        {name: 'cruelty_free', label: 'Cruelty Free', type: 'select', options: ['Yes', 'No']},
        {name: 'vegan', label: 'Vegan', type: 'select', options: ['Yes', 'No']},
        {name: 'batch_number', label: 'Batch Number', type: 'text'}
    ],
    'tobacco': [
        {name: 'tobacco_type', label: 'Tobacco Type', type: 'text'},
        {name: 'nicotine_strength', label: 'Nicotine Strength', type: 'text'},
        {name: 'warning_label', label: 'Health Warning Label', type: 'textarea'},
        {name: 'age_restricted', label: 'Age Restricted (18+)', type: 'select', options: ['Yes', 'No']},
        {name: 'origin_country', label: 'Country of Origin', type: 'text'}
    ],
    'electronics': [
        {name: 'warranty_period', label: 'Warranty Period', type: 'text'},
        {name: 'model_number', label: 'Model Number', type: 'text'},
        {name: 'power_source', label: 'Power Source', type: 'text'}
    ]
};

document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('categorySelect');
    const dynamicFieldsContainer = document.getElementById('dynamicFieldsContainer');
    const currentCategoryBadge = document.getElementById('currentCategoryBadge');

    function renderDynamicFields() {
        if(categorySelect.selectedIndex <= 0) {
            dynamicFieldsContainer.innerHTML = '<div class="col-12 text-muted">Select a category...</div>';
            return;
        }

        const option = categorySelect.options[categorySelect.selectedIndex];
        const catName = option.getAttribute('data-name');
        currentCategoryBadge.textContent = option.text;

        let configKey = null;
        if(catName.includes('dress') || catName.includes('cloth') || catName.includes('apparel')) configKey = 'dress';
        else if(catName.includes('shoe') || catName.includes('footwear')) configKey = 'shoes';
        else if(catName.includes('food') || catName.includes('khabar') || catName.includes('drink') || catName.includes('packet')) configKey = 'food';
        else if(catName.includes('grocer')) configKey = 'grocery';
        else if(catName.includes('cosmetic') || catName.includes('beauty')) configKey = 'cosmetics';
        else if(catName.includes('tobacco') || catName.includes('tambako')) configKey = 'tobacco';
        else if(catName.includes('electronic')) configKey = 'electronics';

        dynamicFieldsContainer.innerHTML = '';

        if(configKey && catConfigs[configKey]) {
            catConfigs[configKey].forEach(field => {
                const col = document.createElement('div');
                col.className = field.type === 'textarea' ? 'col-12' : 'col-md-6';
                
                const val = existingMetaData[field.name] || '';

                let inputHtml = '';
                if(field.type === 'select') {
                    const optionsHtml = field.options.map(opt => `<option value="${opt}" ${opt===val?'selected':''}>${opt}</option>`).join('');
                    inputHtml = `<select name="meta[${field.name}]" class="form-select"><option value="">-- Select --</option>${optionsHtml}</select>`;
                } else if(field.type === 'textarea') {
                    inputHtml = `<textarea name="meta[${field.name}]" class="form-control" rows="2">${val}</textarea>`;
                } else {
                    inputHtml = `<input type="${field.type}" name="meta[${field.name}]" class="form-control" value="${val}">`;
                }

                col.innerHTML = `<label class="form-label">${field.label}</label>${inputHtml}`;
                dynamicFieldsContainer.appendChild(col);
            });
        }
    }

    categorySelect.addEventListener('change', renderDynamicFields);
    renderDynamicFields();

    const addVariantBtn = document.getElementById('addVariantBtn');
    const variantTableBody = document.getElementById('variantTableBody');
    const noVariantsRow = document.getElementById('noVariantsRow');

    addVariantBtn.addEventListener('click', () => {
        if(noVariantsRow) noVariantsRow.style.display = 'none';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="variants[name][]" class="form-control form-control-sm" required></td>
            <td><input type="text" name="variants[sku][]" class="form-control form-control-sm"></td>
            <td><input type="number" step="0.01" name="variants[price][]" class="form-control form-control-sm"></td>
            <td><input type="number" step="0.01" name="variants[discount][]" class="form-control form-control-sm"></td>
            <td><input type="number" name="variants[stock][]" class="form-control form-control-sm" value="0"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-variant"><i class="bi bi-trash"></i></button></td>
        `;
        variantTableBody.appendChild(tr);
    });

    variantTableBody.addEventListener('click', (e) => {
        if(e.target.closest('.remove-variant')) {
            e.target.closest('tr').remove();
            if(variantTableBody.children.length === 0 && noVariantsRow) {
                noVariantsRow.style.display = 'table-row';
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>