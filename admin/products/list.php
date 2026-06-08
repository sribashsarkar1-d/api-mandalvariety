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

function offerStatus($start, $end) {
    $today = date('Y-m-d');
    if (!empty($start) && $today < $start) return ['Upcoming', 'warning'];
    if (!empty($end) && $today > $end) return ['Ended', 'secondary'];
    return ['Running', 'success'];
}

function offerHtml($row)
{
    if (empty($row['offer_name']) || empty($row['offer_type']) || $row['offer_type'] === 'none') {
        return '<span class="text-muted small"><i class="fa-solid fa-minus me-1"></i>No offer</span>';
    }

    [$label, $badge] = offerStatus($row['start_date'] ?? null, $row['end_date'] ?? null);

    $value = $row['offer_type'] === 'percent'
        ? rtrim(rtrim((string)$row['offer_value'], '0'), '.') . '%'
        : '₹' . number_format((float)$row['offer_value'], 2);

    $target = !empty($row['offer_product_id']) ? 'Product' : 'Category';

    return '
        <div class="d-flex flex-column align-items-start gap-1">
            <div class="badge bg-' . $badge . ' rounded-pill px-2 py-1"><i class="fa-solid fa-tag me-1"></i>' . e($row['offer_name']) . '</div>
            <div class="small fw-bold text-dark mt-1">' . e($value) . ' Off</div>
        </div>
    ';
}

function stockHtml($qty, $lowStockLimit)
{
    $qty = (int)$qty;

    if ($qty <= 0) {
        return '<span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2"><i class="fa-solid fa-circle-xmark me-1"></i>Out of Stock</span>';
    }

    if ($qty <= $lowStockLimit) {
        return '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Low: ' . $qty . '</span>';
    }

    return '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>' . $qty . ' in Stock</span>';
}

function getAttributesHtml($jsonAttr) {
    if (empty($jsonAttr)) return '';
    $arr = json_decode($jsonAttr, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($arr)) return '';
    
    $html = '<div class="d-flex flex-wrap gap-1 mt-2">';
    foreach($arr as $k => $v) {
        $html .= '<span class="badge bg-light border text-dark" style="font-size:0.7rem; font-weight:500;">' . e($k) . ': ' . e($v) . '</span>';
    }
    $html .= '</div>';
    return $html;
}
?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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
        box-shadow: 0 15px 30px rgba(37, 99, 235, 0.2);
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
        .page-header-premium .d-flex {
            width: 100%;
            flex-wrap: wrap;
        }
        .page-header-premium .btn {
            flex: 1;
            justify-content: center;
        }
    }

    .premium-card {
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .premium-card-body {
        padding: 25px;
    }

    .form-control-premium, .form-select-premium {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 10px 16px;
        font-size: 0.9rem;
        background: #ffffff;
        transition: all 0.3s ease;
    }

    .form-control-premium:focus, .form-select-premium:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        outline: none;
    }

    .table-premium {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-premium th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 16px 20px;
        white-space: nowrap;
    }

    .table-premium td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table-premium tbody tr {
        transition: background-color 0.2s ease;
    }

    .table-premium tbody tr:hover {
        background-color: #f8fafc;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: #f0fdf4;
        color: #16a34a;
    }
    .btn-edit:hover {
        background: #16a34a;
        color: white;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }
    
    .product-img-thumb {
        width: 60px; 
        height: 60px; 
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        background: #fff;
    }

    .price-main {
        font-weight: 800;
        font-size: 1.1rem;
        color: #1e293b;
    }
    
    .price-cut {
        font-size: 0.85rem;
        text-decoration: line-through;
        color: #94a3b8;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>
    <div class="container-fluid mt-4 mb-5 px-4">
        
        <div class="page-header-premium">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-boxes-stacked me-2"></i> Product Inventory</h3>
                <p class="mb-0 text-white-50">Manage all products, categories, stock, and offers.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="list.php?status=lowstock" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Low Stock
                </a>
                <a href="create.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #2563eb;">
                    <i class="fa-solid fa-plus me-2"></i> Add Product
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="premium-card mb-4">
            <div class="premium-card-body pb-3">
                <form method="GET" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-magnifying-glass me-1"></i> SEARCH</label>
                        <input type="text" name="search" value="<?= e($search) ?>" class="form-control-premium" placeholder="Name, SKU, Slug...">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-layer-group me-1"></i> CATEGORY</label>
                        <select name="category" class="form-select-premium">
                            <option value="">All Categories</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= ($category == $c['id']) ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-box-open me-1"></i> STATUS</label>
                        <select name="status" class="form-select-premium">
                            <option value="">All</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="instock" <?= ($status === 'instock') ? 'selected' : '' ?>>In Stock</option>
                            <option value="lowstock" <?= ($status === 'lowstock') ? 'selected' : '' ?>>Low Stock</option>
                            <option value="outstock" <?= ($status === 'outstock') ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-sort me-1"></i> SORT</label>
                        <select name="sort" class="form-select-premium">
                            <option value="latest" <?= ($sort === 'latest') ? 'selected' : '' ?>>Latest</option>
                            <option value="name_az" <?= ($sort === 'name_az') ? 'selected' : '' ?>>Name A-Z</option>
                            <option value="price_low" <?= ($sort === 'price_low') ? 'selected' : '' ?>>Price Low - High</option>
                            <option value="price_high" <?= ($sort === 'price_high') ? 'selected' : '' ?>>Price High - Low</option>
                            <option value="qty_low" <?= ($sort === 'qty_low') ? 'selected' : '' ?>>Stock Low - High</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="list.php" class="btn btn-light rounded-pill px-4 fw-bold text-muted border">Reset</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: var(--primary-gradient); border:none;">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Pricing</th>
                            <th>Offer</th>
                            <th>Inventory</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $row): ?>
                                <?php
                                $imgs = productImages($row['images'] ?? '');
                                ?>
                                <tr id="row-<?= (int)$row['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($imgs)): ?>
                                                <div class="position-relative">
                                                    <img src="../uploads/<?= e($imgs[0]) ?>" alt="img" class="product-img-thumb">
                                                </div>
                                            <?php else: ?>
                                                <div class="product-img-thumb d-flex align-items-center justify-content-center text-muted" style="background: #f1f5f9; font-size: 0.8rem;">
                                                    <i class="fa-solid fa-image-slash"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 1.05rem;"><?= e($row['name']) ?></div>
                                                <div class="small text-muted d-flex gap-2 align-items-center">
                                                    <span class="badge bg-light border text-secondary px-2"><i class="fa-solid fa-layer-group me-1"></i><?= e($row['category_name'] ?? 'N/A') ?></span>
                                                    <span>SKU: <?= e($row['sku']) ?></span>
                                                </div>
                                                <?= getAttributesHtml($row['attributes'] ?? '') ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if (!empty($row['discount_price'])): ?>
                                            <div class="price-cut">₹<?= number_format((float)$row['price'], 2) ?></div>
                                            <div class="price-main text-success">₹<?= number_format((float)$row['discount_price'], 2) ?></div>
                                        <?php else: ?>
                                            <div class="price-main">₹<?= number_format((float)$row['price'], 2) ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= offerHtml($row) ?></td>

                                    <td><?= stockHtml($row['stock_quantity'] ?? 0, $lowStockLimit) ?></td>

                                    <td>
                                        <?php if ((int)$row['is_active'] === 1): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1"><i class="fa-solid fa-eye me-1"></i>Active</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1"><i class="fa-solid fa-eye-slash me-1"></i>Hidden</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn-action btn-edit" title="Edit Product">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button class="btn-action btn-delete deleteBtn" data-id="<?= (int)$row['id'] ?>" title="Delete Product">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i><br>
                                        No products found matching your criteria.
                                    </div>
                                    <a href="create.php" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: var(--primary-gradient); border:none;">
                                        Create Your First Product
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pages > 1): ?>
                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Showing page <?= $page ?> of <?= $pages ?></span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        if (confirm('Are you absolutely sure you want to delete this product?')) {
            fetch('delete.php?id=' + id)
                .then(res => res.text())
                .then(() => {
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.style.transition = "opacity 0.3s";
                        row.style.opacity = 0;
                        setTimeout(() => row.remove(), 300);
                    }
                })
                .catch(() => {
                    alert('Delete failed! Please try again.');
                });
        }
    });
});
</script>