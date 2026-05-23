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

$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search   = trim($_GET['search'] ?? '');
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status   = trim($_GET['status'] ?? '');
$sort     = trim($_GET['sort'] ?? 'latest');
$lowStockLimit = 10;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE :search OR p.slug LIKE :search OR p.sku LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if ($category > 0) {
    $where[] = "p.category_id = :category";
    $params[':category'] = $category;
}

if ($status === 'active') {
    $where[] = "p.is_active = 1";
} elseif ($status === 'inactive') {
    $where[] = "p.is_active = 0";
} elseif ($status === 'instock') {
    $where[] = "COALESCE(p.stock_quantity, 0) > 0";
} elseif ($status === 'outstock') {
    $where[] = "COALESCE(p.stock_quantity, 0) <= 0";
} elseif ($status === 'lowstock') {
    $where[] = "COALESCE(p.stock_quantity, 0) > 0 AND COALESCE(p.stock_quantity, 0) <= :lowstock";
    $params[':lowstock'] = $lowStockLimit;
}

$whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

switch ($sort) {
    case 'qty_low':
        $orderBy = "COALESCE(p.stock_quantity, 0) ASC, p.id DESC";
        break;
    case 'qty_high':
        $orderBy = "COALESCE(p.stock_quantity, 0) DESC, p.id DESC";
        break;
    case 'price_low':
        $orderBy = "COALESCE(p.price, 0) ASC, p.id DESC";
        break;
    case 'price_high':
        $orderBy = "COALESCE(p.price, 0) DESC, p.id DESC";
        break;
    case 'name_az':
        $orderBy = "p.name ASC";
        break;
    case 'name_za':
        $orderBy = "p.name DESC";
        break;
    case 'oldest':
        $orderBy = "p.id ASC";
        break;
    default:
        $orderBy = "p.id DESC";
        break;
}

$countSql = "SELECT COUNT(DISTINCT p.id)
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN offers o ON (o.product_id = p.id OR o.category_id = p.category_id)
             $whereSql";

$countStmt = $conn->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));

$sql = "SELECT 
            p.*,
            c.name AS category_name,
            o.offer_name,
            o.offer_type,
            o.offer_value,
            o.start_date,
            o.end_date,
            o.priority,
            o.category_id AS offer_category_id,
            o.product_id AS offer_product_id
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN offers o ON (o.product_id = p.id OR o.category_id = p.category_id)
        $whereSql
        GROUP BY p.id
        ORDER BY $orderBy
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$summary = $conn->query("
    SELECT
        COUNT(*) AS total_products,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_products,
        SUM(CASE WHEN COALESCE(stock_quantity,0) <= 0 THEN 1 ELSE 0 END) AS outstock_products,
        SUM(CASE WHEN COALESCE(stock_quantity,0) > 0 AND COALESCE(stock_quantity,0) <= {$lowStockLimit} THEN 1 ELSE 0 END) AS lowstock_products
    FROM products
")->fetch(PDO::FETCH_ASSOC);

function offerStatus($start, $end) {
    $today = date('Y-m-d');
    if (!empty($start) && $today < $start) return ['Upcoming', 'warning'];
    if (!empty($end) && $today > $end) return ['Ended', 'secondary'];
    return ['Running', 'success'];
}

function offerHtml($row)
{
    if (empty($row['offer_name']) || empty($row['offer_type']) || $row['offer_type'] === 'none') {
        return '<span class="text-muted small">No offer</span>';
    }

    [$label, $badge] = offerStatus($row['start_date'] ?? null, $row['end_date'] ?? null);

    $value = $row['offer_type'] === 'percent'
        ? rtrim(rtrim((string)$row['offer_value'], '0'), '.') . '%'
        : '₹' . number_format((float)$row['offer_value'], 2);

    $target = !empty($row['offer_product_id']) ? 'Product' : 'Category';

    $dateLine = '';
    if (!empty($row['start_date']) || !empty($row['end_date'])) {
        $start = !empty($row['start_date']) ? date('d M Y', strtotime($row['start_date'])) : 'Open';
        $end   = !empty($row['end_date']) ? date('d M Y', strtotime($row['end_date'])) : 'Open';
        $dateLine = '<div class="small text-muted mt-1">' . $start . ' → ' . $end . '</div>';
    }

    return '
        <div class="offer-box">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="badge bg-info text-dark">' . e($row['offer_name']) . '</span>
                <span class="badge bg-' . $badge . '">' . $label . '</span>
            </div>
            <div class="small fw-semibold">' . e($value) . ' <span class="text-muted fw-normal">(' . e($target) . ')</span></div>
            ' . $dateLine . '
        </div>
    ';
}

function stockHtml($qty, $lowStockLimit)
{
    $qty = (int)$qty;

    if ($qty <= 0) {
        return '<span class="badge rounded-pill bg-danger">Out of stock</span>';
    }

    if ($qty <= $lowStockLimit) {
        return '<span class="badge rounded-pill bg-warning text-dark">Low stock: ' . $qty . '</span>';
    }

    return '<span class="badge rounded-pill bg-success">In stock: ' . $qty . '</span>';
}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .product-cell {
    min-width: 220px;
}

.product-slider-wrap {
    width: 100%;
    max-width: 220px;
}

.product-slider {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e9edf3;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}

.product-slide-img-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    overflow: hidden;
}

.product-slide-img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform .35s ease;
}

.product-slider:hover .product-slide-img {
    transform: scale(1.04);
}

.product-slider-control {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.72);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 14px;
    backdrop-filter: blur(6px);
    transition: all .25s ease;
}

.product-slider .carousel-control-prev,
.product-slider .carousel-control-next {
    width: 16%;
    opacity: 1;
}

.product-slider .carousel-control-prev:hover .product-slider-control,
.product-slider .carousel-control-next:hover .product-slider-control {
    background: rgba(37, 99, 235, 0.95);
    transform: scale(1.05);
}

.product-slider-indicators {
    margin-bottom: 8px;
    gap: 4px;
}

.product-slider-indicators [data-bs-target] {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    border: 0;
    background-color: rgba(255,255,255,.7);
    opacity: 1;
}

.product-slider-indicators .active {
    width: 22px;
    background-color: #2563eb;
}

.product-slider-count {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 9px;
    border-radius: 999px;
    background: rgba(15, 23, 42, .75);
    color: #fff;
    font-size: .72rem;
    font-weight: 600;
    line-height: 1;
    backdrop-filter: blur(4px);
    z-index: 3;
}

.no-image-box {
    max-width: 220px;
    border-radius: 18px;
    border: 1px solid #e9edf3;
    overflow: hidden;
    background: #f8fafc;
}

@media (max-width: 1199.98px) {
    .product-slider-wrap,
    .no-image-box {
        max-width: 180px;
    }

    .product-cell {
        min-width: 190px;
    }
}

@media (max-width: 767.98px) {
    .product-slider-wrap,
    .no-image-box {
        max-width: 140px;
    }

    .product-cell {
        min-width: 150px;
    }

    .product-slider-control {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }

    .product-slider-count {
        font-size: .65rem;
        padding: 4px 7px;
    }
}
html, body {
    overflow-x: hidden;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

.product-page {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    --card-radius: 16px;
}

.product-page .container-fluid {
    width: 100%;
    max-width: 100%;
    padding-left: 16px;
    padding-right: 16px;
}

.product-page .summary-card,
.product-page .filter-card,
.product-page .table-card {
    border: 0;
    border-radius: var(--card-radius);
    box-shadow: 0 8px 24px rgba(16, 24, 40, .06);
    overflow: hidden;
}

.product-page .page-title {
    font-size: clamp(1.25rem, 1.1rem + 0.6vw, 1.75rem);
    font-weight: 700;
    letter-spacing: -.02em;
    margin-bottom: 4px;
}

.product-page .page-subtitle {
    color: #6c757d;
    font-size: .95rem;
}

.product-page .summary-card .icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 18px;
    flex-shrink: 0;
}

.product-page .summary-label {
    font-size: .8rem;
    color: #6c757d;
    margin-bottom: 4px;
}

.product-page .summary-value {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1;
}

.product-page .filter-card .card-body,
.product-page .table-card .card-body {
    padding: 16px;
}

.product-page .filter-card .form-label {
    font-size: .82rem;
    font-weight: 600;
    margin-bottom: 6px;
}

.product-page .filter-card .form-control,
.product-page .filter-card .form-select,
.product-page .filter-card .btn {
    min-height: 44px;
    border-radius: 12px;
}

.product-page .table-card .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}

.product-page .table-card table {
    width: 100%;
    min-width: 1180px;
    margin-bottom: 0;
}

.product-page .table-card thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    white-space: nowrap;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    background: #0f172a !important;
    color: #fff !important;
}

.product-page .table-card tbody td {
    vertical-align: middle;
    font-size: .92rem;
    white-space: nowrap;
}

.product-page .table-card tbody td:nth-child(2),
.product-page .table-card tbody td:nth-child(6) {
    white-space: normal;
}

.product-page .product-name {
    font-weight: 700;
    color: #212529;
    line-height: 1.25;
}

.product-page .product-meta {
    font-size: .8rem;
    color: #6c757d;
    margin-top: 4px;
    max-width: 240px;
    line-height: 1.45;
}

.product-page .table-thumb {
    width: 68px;
    height: 68px;
    min-width: 68px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #eef1f4;
    background: #f8f9fa;
}

.product-page .offer-box {
    min-width: 170px;
}

.product-page .price-main {
    font-weight: 700;
    color: #111827;
}

.product-page .price-cut {
    color: #6c757d;
    text-decoration: line-through;
    font-size: .84rem;
}

.product-page .code-chip {
    display: inline-block;
    padding: 4px 8px;
    background: #f3f4f6;
    border-radius: 8px;
    font-size: .8rem;
    max-width: 170px;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

.product-page .action-group {
    flex-wrap: wrap;
}

.product-page .action-group .btn {
    border-radius: 10px;
    min-width: 74px;
}

.product-page .pagination {
    flex-wrap: wrap;
    gap: 6px;
}

.product-page .pagination .page-link {
    border-radius: 10px;
    margin: 0;
}

@media (max-width: 1399.98px) {
    .product-page .table-card table {
        min-width: 1080px;
    }
}

@media (max-width: 1199.98px) {
    .product-page .container-fluid {
        padding-left: 14px;
        padding-right: 14px;
    }

    .product-page .table-card table {
        min-width: 1020px;
    }
}

@media (max-width: 991.98px) {
    html, body {
        overflow-x: hidden;
    }

    .product-page .container-fluid {
        padding-left: 12px;
        padding-right: 12px;
    }

    .product-page .table-card table {
        min-width: 980px;
    }

    .product-page .page-title {
        font-size: 1.35rem;
    }

    .product-page .summary-value {
        font-size: 1.2rem;
    }
}

@media (max-width: 767.98px) {
    .product-page .container-fluid {
        padding-left: 10px;
        padding-right: 10px;
    }

    .product-page .filter-card .card-body,
    .product-page .table-card .card-body {
        padding: 12px;
    }

    .product-page .table-thumb {
        width: 56px;
        height: 56px;
        min-width: 56px;
    }

    .product-page .product-meta {
        max-width: 180px;
    }

    .product-page .table-card table {
        min-width: 920px;
    }
}
</style>

<div class="w-100 product-page">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid mt-4 mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="page-title mb-1">Product Inventory</h4>
                <div class="page-subtitle">Manage products, offers, pricing, and stock from one place.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="create.php" class="btn btn-primary px-3">+ Add Product</a>
                <a href="list.php?status=lowstock" class="btn btn-outline-warning px-3">Low Stock</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="summary-label">Total Products</div>
                            <div class="summary-value"><?= (int)($summary['total_products'] ?? 0) ?></div>
                        </div>
                        <div class="icon-wrap bg-primary-subtle text-primary">📦</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="summary-label">Active</div>
                            <div class="summary-value"><?= (int)($summary['active_products'] ?? 0) ?></div>
                        </div>
                        <div class="icon-wrap bg-success-subtle text-success">✅</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="summary-label">Low Stock</div>
                            <div class="summary-value"><?= (int)($summary['lowstock_products'] ?? 0) ?></div>
                        </div>
                        <div class="icon-wrap bg-warning-subtle text-warning">⚠️</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card summary-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="summary-label">Out of Stock</div>
                            <div class="summary-value"><?= (int)($summary['outstock_products'] ?? 0) ?></div>
                        </div>
                        <div class="icon-wrap bg-danger-subtle text-danger">⛔</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="<?= e($search) ?>" class="form-control" placeholder="Name, slug, SKU, description">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= ($category == $c['id']) ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Stock Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="instock" <?= ($status === 'instock') ? 'selected' : '' ?>>In Stock</option>
                            <option value="lowstock" <?= ($status === 'lowstock') ? 'selected' : '' ?>>Low Stock</option>
                            <option value="outstock" <?= ($status === 'outstock') ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="latest" <?= ($sort === 'latest') ? 'selected' : '' ?>>Latest</option>
                            <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : '' ?>>Oldest</option>
                            <option value="name_az" <?= ($sort === 'name_az') ? 'selected' : '' ?>>Name A-Z</option>
                            <option value="name_za" <?= ($sort === 'name_za') ? 'selected' : '' ?>>Name Z-A</option>
                            <option value="price_low" <?= ($sort === 'price_low') ? 'selected' : '' ?>>Price Low to High</option>
                            <option value="price_high" <?= ($sort === 'price_high') ? 'selected' : '' ?>>Price High to Low</option>
                            <option value="qty_low" <?= ($sort === 'qty_low') ? 'selected' : '' ?>>Quantity Low to High</option>
                            <option value="qty_high" <?= ($sort === 'qty_high') ? 'selected' : '' ?>>Quantity High to Low</option>
                        </select>
                    </div>

                    <div class="col-lg-9 col-md-2 d-flex gap-0">
                        <button type="submit" class="btn btn-dark w-100">Apply</button>
                        <a href="list.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <div>
                    <h6 class="mb-1 fw-bold">Products</h6>
                    <small class="text-muted">Showing <?= count($products) ?> of <?= (int)$total ?> items</small>
                </div>
                <div class="small text-muted">Low stock threshold: <?= (int)$lowStockLimit ?></div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover bg-white">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Slug / SKU</th>
                                <th>Price</th>
                                <th>Offer</th>
                                <th>Stock</th>
                                <th>Weight</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $row): ?>
                                    <?php
                                    $imgs = productImages($row['images'] ?? '');
                                    $createdAt = $row['createdat'] ?? ($row['created_at'] ?? '');
                                    $updatedAt = $row['updatedat'] ?? ($row['updated_at'] ?? '');
                                    ?>
                                    <tr id="row-<?= (int)$row['id'] ?>">
                                        <td class="fw-semibold"><?= (int)$row['id'] ?></td>

                                        <td class="product-cell">
    <?php if (!empty($imgs)): ?>
        <div class="product-slider-wrap">
            <div id="productSlider<?= (int)$row['id'] ?>"
                 class="carousel slide product-slider"
                 data-bs-ride="false"
                 data-bs-touch="true"
                 data-bs-interval="false">
                
                <div class="carousel-inner rounded-4">
                    <?php foreach ($imgs as $index => $img): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="product-slide-img-wrap">
                                <img
                                    src="../uploads/<?= e($img) ?>"
                                    alt="<?= e($row['name']) ?>"
                                    class="product-slide-img img-fluid"
                                    loading="lazy">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($imgs) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productSlider<?= (int)$row['id'] ?>" data-bs-slide="prev">
                        <span class="product-slider-control">
                            <i class="bi bi-chevron-left"></i>
                        </span>
                        <span class="visually-hidden">Previous</span>
                    </button>

                    <button class="carousel-control-next" type="button" data-bs-target="#productSlider<?= (int)$row['id'] ?>" data-bs-slide="next">
                        <span class="product-slider-control">
                            <i class="bi bi-chevron-right"></i>
                        </span>
                        <span class="visually-hidden">Next</span>
                    </button>

                    <div class="carousel-indicators product-slider-indicators">
                        <?php foreach ($imgs as $index => $img): ?>
                            <button type="button"
                                    data-bs-target="#productSlider<?= (int)$row['id'] ?>"
                                    data-bs-slide-to="<?= $index ?>"
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="product-slider-count">
                        <?= count($imgs) ?> photos
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="product-slide-img-wrap no-image-box">
            <img
                src="../assets/img/no-image.png"
                alt="No image"
                class="product-slide-img img-fluid"
                loading="lazy">
        </div>
    <?php endif; ?>
</td>

                                        <td>
                                            <span class="badge bg-light text-dark border"><?= e($row['category_name'] ?? 'N/A') ?></span>
                                        </td>

                                        <td>
                                            <div class="mb-1"><span class="code-chip"><?= e($row['slug']) ?></span></div>
                                            <div class="small text-muted">SKU: <?= e($row['sku']) ?></div>
                                        </td>

                                        <td>
                                            <div class="price-main">₹<?= number_format((float)$row['price'], 2) ?></div>
                                            <?php if (!empty($row['discount_price'])): ?>
                                                <div class="price-cut">₹<?= number_format((float)$row['discount_price'], 2) ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= offerHtml($row) ?></td>

                                        <td><?= stockHtml($row['stock_quantity'] ?? 0, $lowStockLimit) ?></td>

                                        <td>
                                            <?= !empty($row['weight']) ? e($row['weight']) . ' kg' : '<span class="text-muted">—</span>' ?>
                                        </td>

                                        <td>
                                            <?php if ((int)$row['is_active'] === 1): ?>
                                                <span class="badge rounded-pill bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="small">
                                                <div><strong>Created:</strong> <?= !empty($createdAt) ? date('d M Y', strtotime($createdAt)) : '—' ?></div>
                                                <div class="text-muted"><strong>Updated:</strong> <?= !empty($updatedAt) ? date('d M Y', strtotime($updatedAt)) : '—' ?></div>
                                            </div>
                                        </td>

                                        <td class="text-end pe-3">
                                            <div class="action-group d-flex justify-content-end gap-2">
                                                <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <button class="btn btn-outline-danger btn-sm deleteBtn" data-id="<?= (int)$row['id'] ?>">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="text-muted mb-2">No products found.</div>
                                        <a href="create.php" class="btn btn-primary btn-sm">Create Product</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination flex-wrap">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;

        if (confirm('Delete this product?')) {
            fetch('delete.php?id=' + id)
                .then(res => res.text())
                .then(() => {
                    const row = document.getElementById('row-' + id);
                    if (row) row.remove();
                })
                .catch(() => {
                    alert('Delete failed!');
                });
        }
    });
});
</script>