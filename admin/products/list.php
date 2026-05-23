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
<style>
    .page-container {
        padding: 24px;
        background-color: #f8fafc;
        min-height: calc(100vh - 70px);
    }
    .summary-card {
        border-radius: 20px;
        border: none;
        background: #fff;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .summary-label {
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 8px;
    }
    .summary-value {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }
    .icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .filter-card, .table-card {
        border-radius: 20px;
        border: none;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 24px;
    }
    .filter-card .card-body, .table-card .card-body {
        padding: 24px;
    }
    .form-control, .form-select {
        border-radius: 12px;
        padding: 10px 16px;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .table-modern {
        margin-bottom: 0;
        width: 100%;
    }
    .table-modern th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 24px;
        white-space: nowrap;
    }
    .table-modern td {
        padding: 18px 24px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }
    .badge-modern {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .btn-custom {
        border-radius: 12px;
        font-weight: 600;
        padding: 8px 20px;
        transition: all 0.2s;
    }
    .btn-action {
        border-radius: 10px;
        padding: 6px 14px;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="page-container">
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

        <div class="filter-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label text-muted small fw-bold">SEARCH</label>
                        <input type="text" name="search" value="<?= e($search) ?>" class="form-control" placeholder="Name, slug, SKU...">
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold">CATEGORY</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= ($category == $c['id']) ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label text-muted small fw-bold">STOCK STATUS</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="instock" <?= ($status === 'instock') ? 'selected' : '' ?>>In Stock</option>
                            <option value="lowstock" <?= ($status === 'lowstock') ? 'selected' : '' ?>>Low Stock</option>
                            <option value="outstock" <?= ($status === 'outstock') ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold">SORT BY</label>
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

                    <div class="col-12 mt-4 d-flex justify-content-end gap-3">
                        <a href="list.php" class="btn btn-light btn-custom px-4">Reset</a>
                        <button type="submit" class="btn btn-primary btn-custom px-4">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-card">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 py-4 px-4">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Product Inventory</h5>
                    <small class="text-muted">Showing <?= count($products) ?> of <?= (int)$total ?> items</small>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Offer</th>
                                <th>Stock</th>
                                <th>Weight</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th class="text-end">Action</th>
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
                                        <td class="fw-bold">#<?= (int)$row['id'] ?></td>

                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if (!empty($imgs)): ?>
                                                    <div class="position-relative">
                                                        <img src="../uploads/<?= e($imgs[0]) ?>" alt="img" class="rounded-3 border object-fit-cover" style="width: 54px; height: 54px; background: #fff;">
                                                        <?php if(count($imgs) > 1): ?>
                                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" style="font-size: 0.65rem;">+<?= count($imgs)-1 ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-light rounded-3 border d-flex align-items-center justify-content-center text-muted" style="width: 54px; height: 54px; font-size: 0.8rem;">No Img</div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-bold text-dark mb-1" style="max-width: 250px; white-space: normal; line-height: 1.3;"><?= e($row['name']) ?></div>
                                                    <div class="small text-muted d-flex gap-2">
                                                        <span><i class="fa fa-tag me-1"></i><?= e($row['sku']) ?></span>
                                                        <span><i class="fa fa-link me-1"></i><?= e($row['slug']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge-modern bg-light text-dark border"><?= e($row['category_name'] ?? 'N/A') ?></span>
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