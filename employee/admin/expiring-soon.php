<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Expiring Soon Products';

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : 'all';

// Fetch categories for filter
$stmt = $pdo->query("SELECT id, name FROM employee_categories");
$categories = $stmt->fetchAll();

// Fetch products
$query = "
    SELECT 
        p.id, p.name, p.sku, p.image, p.selling_price, p.unit, p.expiry_date,
        COALESCE(s.quantity, 0) as stock, s.stock_status, c.name as category_name
    FROM employee_products p
    LEFT JOIN employee_product_stock s ON p.id = s.product_id
    LEFT JOIN employee_categories c ON p.category_id = c.id
    WHERE p.status != 'deleted' AND p.expiry_date IS NOT NULL AND p.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
";

$params = [];
if ($category !== 'all' && !empty($category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}
$query .= " ORDER BY p.expiry_date ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Header Action
$header_action_html = '<a href="dashboard.php" class="btn btn-sm btn-light text-primary fw-bold"><i class="bi bi-arrow-left"></i> Back</a>';

require_once '../includes/header.php';
?>

<form method="GET" class="mb-3">
    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
    <div class="row g-2">
        <div class="col-8 col-md-9">
            <div class="search-box mb-0">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="dropdown">
                <button class="form-select text-start text-truncate" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php 
                    $selected_cat = 'All Cats';
                    foreach ($categories as $cat) {
                        if ($category == $cat['id']) {
                            $selected_cat = $cat['name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($selected_cat);
                    ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="max-height: 300px; overflow-y: auto;">
                    <li><a class="dropdown-item <?= $category === 'all' ? 'active' : '' ?>" href="?category=all&search=<?= urlencode($search) ?>">All Categories</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a class="dropdown-item <?= $category == $cat['id'] ? 'active' : '' ?>" href="?category=<?= $cat['id'] ?>&search=<?= urlencode($search) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</form>

<div class="row g-2">
    <?php if (empty($products)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No products expiring soon</p>
        </div>
    <?php else: ?>
        <?php foreach ($products as $p): ?>
        <?php
            $stockBadge = '';
            if ($p['stock'] <= 0) $stockBadge = '<span class="badge bg-danger">Out of Stock</span>';
            elseif ($p['stock_status'] == 'low_stock') $stockBadge = '<span class="badge bg-warning text-dark">Low Stock</span>';
            else $stockBadge = '<span class="badge bg-success">In Stock</span>';
            
            $img = empty($p['image']) ? BASE_URL . '/assets/images/no-image.png' : BASE_URL . '/uploads/products/' . $p['image'];
        ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="product-card">
                <img src="<?= $img ?>" class="product-image" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2060%2060%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_1%20text%20%7B%20fill%3A%23999%3Bfont-weight%3Anormal%3Bfont-family%3AInter%2C%20Helvetica%2C%20sans-serif%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_1%22%3E%3Crect%20width%3D%2260%22%20height%3D%2260%22%20fill%3D%22%23eee%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2214%22%20y%3D%2234%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E'" alt="<?= htmlspecialchars($p['name']) ?>">
                <div class="product-details">
                    <div class="product-title"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-meta mb-1">SKU: <?= htmlspecialchars($p['sku'] ?? 'N/A') ?></div>
                    <div class="product-price"><?= format_currency($p['selling_price']) ?> <span class="text-muted small fw-normal">/ <?= htmlspecialchars($p['unit']) ?></span></div>
                    <div class="d-flex justify-content-between align-items-end mt-1">
                        <div>
                            <span class="text-muted small d-block">Stock: <?= (float)$p['stock'] ?> <?= htmlspecialchars($p['unit']) ?></span>
                            <?= $stockBadge ?>
                        </div>
                        <a href="product-details.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
