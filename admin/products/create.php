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

function uniqueSlug(PDO $conn, $slug) {
    $base = $slug ?: 'product';
    $newSlug = $base;
    $i = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$newSlug]);
        if (!$stmt->fetch()) return $newSlug;
        $newSlug = $base . '-' . $i++;
    }
}

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
    $status = trim($_POST['status'] ?? 'draft');
    $is_active = ($status === 'published') ? 1 : 0;
    $featured_product = isset($_POST['featured_product']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $tags = trim($_POST['tags'] ?? '');

    // 5. Meta Data (Category Specific)
    $meta_data = [];
    if (isset($_POST['meta']) && is_array($_POST['meta'])) {
        foreach ($_POST['meta'] as $key => $val) {
            $meta_data[$key] = is_array($val) ? implode(',', $val) : trim($val);
        }
    }
    $meta_data_json = json_encode($meta_data);

    // Validations
    if ($name === '') $errors[] = 'Product name is required';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Valid selling price is required';
    if ($category_id <= 0) $errors[] = 'Category is required';

    // 6. Media Uploads
    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($_FILES['images']['name'] as $i => $imgName) {
            $tmp = $_FILES['images']['tmp_name'][$i] ?? '';
            $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = "Only JPG, PNG and WEBP allowed. Skipping $imgName";
                continue;
            }
            if ($tmp && is_uploaded_file($tmp)) {
                $newName = time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $ext;
                if (move_uploaded_file($tmp, $uploadDir . $newName)) {
                    $uploadedImages[] = $newName;
                }
            }
        }
    }
    if (empty($uploadedImages)) {
        $errors[] = 'At least one valid product image is required';
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $finalSlug = uniqueSlug($conn, makeSlug($slug ?: $name));
            if (!$sku) $sku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 5)) . rand(1000, 9999);
            $imagesJson = json_encode($uploadedImages);

            $stmt = $conn->prepare("INSERT INTO products (
                name, slug, brand, category_id, short_description, description, 
                sku, barcode, cost_price, price, discount_price, tax_percent, hsn_or_tax_code, 
                stock_quantity, stock, min_stock_alert, seo_meta_title, seo_meta_description, seo_keywords,
                status, is_active, featured_product, new_arrival, tags, meta_data, images
            ) VALUES (
                :name, :slug, :brand, :category_id, :short_desc, :description,
                :sku, :barcode, :cost, :price, :discount, :tax, :hsn,
                :stock, :stock, :min_alert, :seo_title, :seo_desc, :seo_kw,
                :status, :is_active, :featured, :new_arr, :tags, :meta_data, :images
            )");

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
                ':images' => $imagesJson
            ]);

            $product_id = $conn->lastInsertId();

            // 7. Process Variants
            if (isset($_POST['variants']['name']) && is_array($_POST['variants']['name'])) {
                $varStmt = $conn->prepare("INSERT INTO product_variants (
                    product_id, variant_name, variant_sku, variant_barcode, price, discount_price, stock_quantity, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($_POST['variants']['name'] as $vIdx => $vName) {
                    $vName = trim($vName);
                    if (!$vName) continue; // Skip empty rows

                    $vSku = trim($_POST['variants']['sku'][$vIdx] ?? '');
                    $vBarcode = trim($_POST['variants']['barcode'][$vIdx] ?? '');
                    $vPrice = trim($_POST['variants']['price'][$vIdx] ?? '');
                    $vDiscount = trim($_POST['variants']['discount'][$vIdx] ?? '');
                    $vStock = (int)($_POST['variants']['stock'][$vIdx] ?? 0);
                    $vStatus = 'active';

                    if (!$vPrice) $vPrice = $price; // Fallback to main price

                    $varStmt->execute([
                        $product_id, $vName, $vSku, $vBarcode, $vPrice, 
                        $vDiscount !== '' ? $vDiscount : null, $vStock, $vStatus
                    ]);
                }
            }

            $conn->commit();
            $success = "Product created successfully!";
            // Reset form conceptually (since page reloads with success msg)
            $_POST = [];
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "Failed to save product: " . $e->getMessage();
        }
    }
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add New Product</h4>
                <p class="text-muted small mb-0">Create scalable products with dynamic attributes and variants</p>
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
                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills mb-4 gap-2" id="productTabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active px-4 rounded-pill" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic" type="button">Basic Info</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" id="pricing-tab" data-bs-toggle="pill" data-bs-target="#pricing" type="button">Pricing & Stock</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" id="dynamic-tab" data-bs-toggle="pill" data-bs-target="#dynamic" type="button">Category Specifics</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" id="variants-tab" data-bs-toggle="pill" data-bs-target="#variants" type="button">Variants</button></li>
                        <li class="nav-item"><button class="nav-link px-4 rounded-pill" id="seo-tab" data-bs-toggle="pill" data-bs-target="#seo" type="button">SEO</button></li>
                    </ul>

                    <div class="tab-content bg-white p-4 rounded-4 shadow-sm" id="productTabsContent">
                        
                        <!-- 1. Basic Info -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <h5 class="mb-4 fw-bold">Basic Information</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" id="categorySelect" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" data-name="<?= strtolower($cat['name']) ?>" <?= ((int)($_POST['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Brand</label>
                                    <input type="text" name="brand" class="form-control" value="<?= e($_POST['brand'] ?? '') ?>" placeholder="e.g. Nike, Apple">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Slug (URL)</label>
                                    <input type="text" name="slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>" placeholder="Auto-generated if empty">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="short_description" class="form-control" rows="2" placeholder="Brief summary..."><?= e($_POST['short_description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full Description</label>
                                    <textarea name="description" class="form-control" rows="5" placeholder="Detailed description..."><?= e($_POST['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Pricing & Stock -->
                        <div class="tab-pane fade" id="pricing" role="tabpanel">
                            <h5 class="mb-4 fw-bold">Pricing & Inventory</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Selling Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="price" class="form-control" value="<?= e($_POST['price'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Discounted Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= e($_POST['discount_price'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= e($_POST['cost_price'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax / GST (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="tax_percent" class="form-control" value="<?= e($_POST['tax_percent'] ?? '') ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">HSN or Tax Code</label>
                                    <input type="text" name="hsn_or_tax_code" class="form-control" value="<?= e($_POST['hsn_or_tax_code'] ?? '') ?>">
                                </div>
                                <div class="col-12"><hr class="text-muted"></div>
                                <div class="col-md-4">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control" value="<?= e($_POST['sku'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Barcode (UPC/EAN)</label>
                                    <input type="text" name="barcode" class="form-control" value="<?= e($_POST['barcode'] ?? '') ?>">
                                </div>
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" name="stock_quantity" class="form-control" value="<?= e($_POST['stock_quantity'] ?? '0') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Low Stock Alert Level</label>
                                    <input type="number" name="min_stock_alert" class="form-control" value="<?= e($_POST['min_stock_alert'] ?? '5') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Dynamic Category Specifics -->
                        <div class="tab-pane fade" id="dynamic" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Category Specific Details</h5>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2" id="currentCategoryBadge">No Category Selected</span>
                            </div>
                            <div id="dynamicFieldsContainer" class="row g-3">
                                <div class="col-12 text-center text-muted py-5" id="dynamicPlaceholder">
                                    <i class="bi bi-ui-radios fs-1 mb-2 d-block"></i>
                                    Please select a category in the Basic Info tab to load specific fields.
                                </div>
                            </div>
                        </div>

                        <!-- 4. Variants -->
                        <div class="tab-pane fade" id="variants" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Product Variants</h5>
                                    <small class="text-muted">Add sizes, colors, or weights (e.g., "M", "Red", "1KG")</small>
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
                                        <!-- Dynamic Rows -->
                                        <tr id="noVariantsRow">
                                            <td colspan="6" class="text-center py-4 text-muted">No variants added. Product will be sold as a single unit.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 5. SEO -->
                        <div class="tab-pane fade" id="seo" role="tabpanel">
                            <h5 class="mb-4 fw-bold">Search Engine Optimization</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="seo_meta_title" class="form-control" value="<?= e($_POST['seo_meta_title'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="seo_meta_description" class="form-control" rows="3"><?= e($_POST['seo_meta_description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" name="seo_keywords" class="form-control" placeholder="keyword1, keyword2, keyword3" value="<?= e($_POST['seo_keywords'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Search Tags</label>
                                    <input type="text" name="tags" class="form-control" placeholder="Comma separated tags for internal search" value="<?= e($_POST['tags'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Media Section -->
                    <div class="card shadow-sm border-0 mb-4 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Product Media</h5>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">UPLOAD IMAGES *</label>
                                <input type="file" name="images[]" id="images" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple required>
                                <small class="text-muted mt-1 d-block">First image will be the featured thumbnail. Select multiple files.</small>
                            </div>
                            <div id="imagePreviewContainer" class="row g-2 mt-2"></div>
                        </div>
                    </div>

                    <!-- Publishing Settings -->
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Publish Settings</h5>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">STATUS</label>
                                <select name="status" class="form-select">
                                    <option value="published">Published (Visible to all)</option>
                                    <option value="draft">Draft (Hidden)</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">PRODUCT FLAGS</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" name="featured_product" id="featured_product">
                                    <label class="form-check-label" for="featured_product">Mark as Featured</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="new_arrival" id="new_arrival">
                                    <label class="form-check-label" for="new_arrival">Mark as New Arrival</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">Save & Publish Product</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Dynamic Category Fields Logic
const catConfigs = {
    'dress': [
        {name: 'department', label: 'Department', type: 'select', options: ['Men', 'Women', 'Boys', 'Girls', 'Children', 'Unisex']},
        {name: 'fabric', label: 'Fabric/Material', type: 'text', placeholder: 'Cotton, Silk...'},
        {name: 'sleeve_type', label: 'Sleeve Type', type: 'select', options: ['Full Sleeve', 'Half Sleeve', 'Sleeveless']},
        {name: 'fit_type', label: 'Fit Type', type: 'select', options: ['Regular', 'Slim Fit', 'Loose']},
        {name: 'pattern', label: 'Pattern', type: 'text', placeholder: 'Solid, Printed...'},
        {name: 'wash_care', label: 'Wash Care', type: 'text', placeholder: 'Machine wash...'},
        {name: 'occasion', label: 'Occasion', type: 'text', placeholder: 'Casual, Party...'}
    ],
    'shoes': [
        {name: 'department', label: 'Department', type: 'select', options: ['Men', 'Women', 'Boys', 'Girls', 'Children']},
        {name: 'shoe_type', label: 'Shoe Type', type: 'text', placeholder: 'Sneakers, Loafers...'},
        {name: 'material', label: 'Upper Material', type: 'text', placeholder: 'Leather, Synthetic...'},
        {name: 'sole_material', label: 'Sole Material', type: 'text', placeholder: 'Rubber, EVA...'},
        {name: 'closure_type', label: 'Closure Type', type: 'select', options: ['Lace-up', 'Slip-on', 'Velcro']},
        {name: 'occasion', label: 'Occasion', type: 'text', placeholder: 'Sports, Casual...'}
    ],
    'food': [
        {name: 'food_type', label: 'Food Type', type: 'text', placeholder: 'Snacks, Beverage...'},
        {name: 'veg_nonveg', label: 'Dietary Preference', type: 'select', options: ['Vegetarian', 'Non-Vegetarian', 'Egg']},
        {name: 'ingredients', label: 'Ingredients', type: 'textarea'},
        {name: 'nutrition_info', label: 'Nutrition Info', type: 'textarea'},
        {name: 'manufacturing_date', label: 'Manufacturing Date', type: 'date'},
        {name: 'expiry_date', label: 'Expiry Date', type: 'date'},
        {name: 'fssai_info', label: 'FSSAI License No.', type: 'text'},
        {name: 'allergy_info', label: 'Allergy Information', type: 'text'}
    ],
    'grocery': [
        {name: 'grocery_type', label: 'Grocery Type', type: 'text', placeholder: 'Staples, Spices...'},
        {name: 'packaging_type', label: 'Packaging Type', type: 'text', placeholder: 'Pouch, Jar, Box...'},
        {name: 'manufacturing_date', label: 'Manufacturing Date', type: 'date'},
        {name: 'expiry_date', label: 'Expiry Date', type: 'date'},
        {name: 'shelf_life', label: 'Shelf Life', type: 'text', placeholder: '6 Months...'}
    ],
    'cosmetics': [
        {name: 'skin_type', label: 'Suitable Skin Type', type: 'select', options: ['All Skin Types', 'Dry', 'Oily', 'Sensitive']},
        {name: 'finish_type', label: 'Finish Type', type: 'text', placeholder: 'Matte, Glossy...'},
        {name: 'spf_level', label: 'SPF Level', type: 'text', placeholder: 'SPF 30, None...'},
        {name: 'ingredients', label: 'Ingredients', type: 'textarea'},
        {name: 'cruelty_free', label: 'Cruelty Free', type: 'select', options: ['Yes', 'No']},
        {name: 'vegan', label: 'Vegan', type: 'select', options: ['Yes', 'No']},
        {name: 'batch_number', label: 'Batch Number', type: 'text'}
    ],
    'tobacco': [
        {name: 'tobacco_type', label: 'Tobacco Type', type: 'text'},
        {name: 'nicotine_strength', label: 'Nicotine Strength', type: 'text'},
        {name: 'warning_label', label: 'Health Warning Label', type: 'textarea', placeholder: 'Required legal warning...'},
        {name: 'age_restricted', label: 'Age Restricted (18+)', type: 'select', options: ['Yes', 'No']},
        {name: 'origin_country', label: 'Country of Origin', type: 'text'}
    ],
    'electronics': [
        {name: 'warranty_period', label: 'Warranty Period', type: 'text', placeholder: '1 Year...'},
        {name: 'model_number', label: 'Model Number', type: 'text'},
        {name: 'power_source', label: 'Power Source', type: 'text', placeholder: 'Battery, AC...'}
    ]
};

document.addEventListener('DOMContentLoaded', () => {
    // 1. Dynamic Category Fields
    const categorySelect = document.getElementById('categorySelect');
    const dynamicFieldsContainer = document.getElementById('dynamicFieldsContainer');
    const dynamicPlaceholder = document.getElementById('dynamicPlaceholder');
    const currentCategoryBadge = document.getElementById('currentCategoryBadge');

    function renderDynamicFields() {
        if(categorySelect.selectedIndex <= 0) {
            dynamicFieldsContainer.innerHTML = '';
            dynamicFieldsContainer.appendChild(dynamicPlaceholder);
            currentCategoryBadge.textContent = 'No Category Selected';
            return;
        }

        const option = categorySelect.options[categorySelect.selectedIndex];
        const catName = option.getAttribute('data-name');
        currentCategoryBadge.textContent = option.text;

        // Map generic names to specific configurations
        let configKey = null;
        if(catName.includes('dress') || catName.includes('cloth') || catName.includes('apparel')) configKey = 'dress';
        else if(catName.includes('shoe') || catName.includes('footwear')) configKey = 'shoes';
        else if(catName.includes('food') || catName.includes('khabar') || catName.includes('drink') || catName.includes('packet')) configKey = 'food';
        else if(catName.includes('grocer')) configKey = 'grocery';
        else if(catName.includes('cosmetic') || catName.includes('beauty') || catName.includes('makeup')) configKey = 'cosmetics';
        else if(catName.includes('tobacco') || catName.includes('tambako')) configKey = 'tobacco';
        else if(catName.includes('electronic') || catName.includes('appliance')) configKey = 'electronics';

        dynamicFieldsContainer.innerHTML = '';

        if(configKey && catConfigs[configKey]) {
            catConfigs[configKey].forEach(field => {
                const col = document.createElement('div');
                col.className = field.type === 'textarea' ? 'col-12' : 'col-md-6';
                
                let inputHtml = '';
                if(field.type === 'select') {
                    const optionsHtml = field.options.map(opt => `<option value="${opt}">${opt}</option>`).join('');
                    inputHtml = `<select name="meta[${field.name}]" class="form-select"><option value="">-- Select --</option>${optionsHtml}</select>`;
                } else if(field.type === 'textarea') {
                    inputHtml = `<textarea name="meta[${field.name}]" class="form-control" rows="2" placeholder="${field.placeholder || ''}"></textarea>`;
                } else {
                    inputHtml = `<input type="${field.type}" name="meta[${field.name}]" class="form-control" placeholder="${field.placeholder || ''}">`;
                }

                col.innerHTML = `<label class="form-label">${field.label}</label>${inputHtml}`;
                dynamicFieldsContainer.appendChild(col);
            });
        } else {
            dynamicFieldsContainer.innerHTML = `<div class="col-12 text-muted"><i>No specific fields configured for this category. You can use Basic Info for descriptions.</i></div>`;
        }
    }

    categorySelect.addEventListener('change', renderDynamicFields);

    // 2. Variants Row Builder
    const addVariantBtn = document.getElementById('addVariantBtn');
    const variantTableBody = document.getElementById('variantTableBody');
    const noVariantsRow = document.getElementById('noVariantsRow');
    let variantCount = 0;

    addVariantBtn.addEventListener('click', () => {
        if(noVariantsRow) noVariantsRow.style.display = 'none';
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="variants[name][]" class="form-control form-control-sm" placeholder="e.g. XL, Red" required></td>
            <td><input type="text" name="variants[sku][]" class="form-control form-control-sm" placeholder="SKU"></td>
            <td><input type="number" step="0.01" name="variants[price][]" class="form-control form-control-sm" placeholder="Overrides main price"></td>
            <td><input type="number" step="0.01" name="variants[discount][]" class="form-control form-control-sm"></td>
            <td><input type="number" name="variants[stock][]" class="form-control form-control-sm" value="0"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-variant"><i class="bi bi-trash"></i></button></td>
        `;
        variantTableBody.appendChild(tr);
        variantCount++;
    });

    variantTableBody.addEventListener('click', (e) => {
        if(e.target.closest('.remove-variant')) {
            e.target.closest('tr').remove();
            variantCount--;
            if(variantCount === 0 && noVariantsRow) noVariantsRow.style.display = 'table-row';
        }
    });

    // 3. Image Preview
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreviewContainer');

    imageInput.addEventListener('change', function () {
        previewContainer.innerHTML = '';
        Array.from(this.files).forEach((file) => {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                const col = document.createElement('div');
                col.className = 'col-4 col-sm-3';
                col.innerHTML = `<div class="border rounded p-1"><img src="${e.target.result}" class="img-fluid rounded" style="aspect-ratio: 1; object-fit: cover; width: 100%;"></div>`;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>