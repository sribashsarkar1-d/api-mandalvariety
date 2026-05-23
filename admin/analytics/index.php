<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    :root{
        --bg:#f4f7fb;
        --card:#ffffff;
        --text:#1f2937;
        --muted:#6b7280;
        --border:#e5e7eb;
        --primary:#2563eb;
        --primary-soft:rgba(37,99,235,.10);
        --success:#198754;
        --warning:#f59e0b;
        --danger:#dc3545;
        --shadow:0 8px 24px rgba(15,23,42,.06);
        --radius:18px;
    }
    body{background:var(--bg);}
    .analytics-page{padding:24px;}
    .page-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px;}
    .page-title{font-size:26px;font-weight:800;color:var(--text);margin:0;}
    .page-subtitle{font-size:14px;color:var(--muted);margin:6px 0 0;}
    .card-box,.stat-card{
        background:var(--card);
        border:1px solid var(--border);
        border-radius:var(--radius);
        box-shadow:var(--shadow);
    }
    .stat-card{padding:20px;height:100%;transition:.2s ease;}
    .stat-card:hover{transform:translateY(-2px);}
    .stat-label{font-size:13px;color:var(--muted);margin-bottom:8px;display:block;}
    .stat-value{font-size:28px;font-weight:800;color:var(--text);line-height:1.1;margin:0;}
    .stat-meta{font-size:12px;color:var(--muted);margin-top:6px;}
    .stat-icon{
        width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;
        background:var(--primary-soft);color:var(--primary);font-size:18px;margin-bottom:14px;
    }
    .card-head{
        padding:18px 20px;border-bottom:1px solid var(--border);
        display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;
    }
    .card-title{font-size:16px;font-weight:700;color:var(--text);margin:0;}
    .badge-soft{
        background:var(--primary-soft);color:var(--primary);
        padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;
    }
    .table-wrap{padding:0;}
    .table.analytics-table{margin:0;}
    .analytics-table thead th{
        background:#f8fafc;color:#667085;font-size:12px;text-transform:uppercase;
        letter-spacing:.04em;font-weight:700;padding:14px 16px;border-bottom:1px solid var(--border);
        white-space:nowrap;
    }
    .analytics-table tbody td{
        padding:15px 16px;vertical-align:middle;border-top:1px solid #f1f5f9;
    }
    .analytics-table tbody tr:hover{background:#fbfdff;}
    .mini-text{font-size:12px;color:var(--muted);}
    .strong-text{font-size:14px;font-weight:700;color:var(--text);}
    .status-pill{
        display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;
        font-size:12px;font-weight:700;background:#f3f4f6;color:#374151;
    }
    .price{font-size:14px;font-weight:800;color:var(--success);}
    .total{font-size:14px;font-weight:800;color:var(--primary);}
    .product-box{display:flex;align-items:center;gap:12px;min-width:220px;}
    .product-img{
        width:56px;height:56px;object-fit:cover;border-radius:12px;
        border:1px solid var(--border);background:#fff;flex-shrink:0;
    }
    .chart-box{padding:20px;}
    .chart-holder{position:relative;height:320px;}
    .empty-box{padding:38px 18px;text-align:center;color:var(--muted);font-size:14px;}
    .alert{border:none;border-radius:14px;box-shadow:var(--shadow);}
    @media (max-width: 768px){
        .analytics-page{padding:16px;}
        .page-title{font-size:22px;}
        .chart-holder{height:260px;}
    }
</style>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function productImage($imagesJson) {
    $images = json_decode((string)$imagesJson, true);
    if (is_array($images) && !empty($images[0])) {
        return '../uploads/' . $images[0];
    }
    return 'https://via.placeholder.com/60x60?text=No+Img';
}

function safeDateFormat($value) {
    if (empty($value)) return 'N/A';
    $ts = strtotime($value);
    return $ts ? date('d M, Y', $ts) : 'N/A';
}

function quoteIdentifier($name) {
    return '`' . str_replace('`', '``', $name) . '`';
}

function getCurrentDatabase(PDO $conn) {
    return (string)$conn->query('SELECT DATABASE()')->fetchColumn();
}

function tableExists(PDO $conn, $tableName) {
    $db = getCurrentDatabase($conn);
    $sql = "SELECT COUNT(*) 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':db' => $db,
        ':table_name' => $tableName
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

function getTableColumns(PDO $conn, $tableName) {
    $columns = [];
    if (!tableExists($conn, $tableName)) {
        return $columns;
    }

    $sql = "SELECT COLUMN_NAME
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':db' => getCurrentDatabase($conn),
        ':table_name' => $tableName
    ]);

    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $col) {
        $columns[] = $col;
    }
    return $columns;
}

function hasColumn(array $columns, $column) {
    return in_array($column, $columns, true);
}

$errors = [];

$totalUsers = 0;
$totalProducts = 0;
$totalCategories = 0;
$totalWishlists = 0;
$activeWishlistUsers = 0;
$totalCarts = 0;
$totalCartItems = 0;
$inventoryValue = 0;
$totalOrders = 0;
$totalRevenue = 0;
$pendingOrders = 0;
$completedOrders = 0;

$recentOrders = [];
$recentCarts = [];
$topWishlistProducts = [];
$topWishlistUsers = [];
$topSellingProducts = [];

$chartLabels = [];
$chartWishlistCounts = [];
$chartSalesCounts = [];

try {
    $usersColumns = getTableColumns($conn, 'users');
    $productsColumns = getTableColumns($conn, 'products');
    $categoriesColumns = getTableColumns($conn, 'categories');
    $wishlistsColumns = getTableColumns($conn, 'wishlists');
    $cartsColumns = getTableColumns($conn, 'carts');
    $cartItemsColumns = getTableColumns($conn, 'cart_items');
    $ordersColumns = getTableColumns($conn, 'orders');
    $orderItemsColumns = getTableColumns($conn, 'order_items');

    if (tableExists($conn, 'users')) {
        $totalUsers = (int)$conn->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    }

    if (tableExists($conn, 'products')) {
        $totalProducts = (int)$conn->query("SELECT COUNT(*) FROM `products`")->fetchColumn();

        if (hasColumn($productsColumns, 'price')) {
            $inventoryValue = (float)$conn->query("SELECT COALESCE(SUM(`price`),0) FROM `products`")->fetchColumn();
        }
    }

    if (tableExists($conn, 'categories')) {
        $totalCategories = (int)$conn->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
    }

    if (tableExists($conn, 'wishlists')) {
        $totalWishlists = (int)$conn->query("SELECT COUNT(*) FROM `wishlists`")->fetchColumn();

        if (hasColumn($wishlistsColumns, 'user_id')) {
            $activeWishlistUsers = (int)$conn->query("SELECT COUNT(DISTINCT `user_id`) FROM `wishlists`")->fetchColumn();
        }
    }

    if (tableExists($conn, 'carts')) {
        $totalCarts = (int)$conn->query("SELECT COUNT(*) FROM `carts`")->fetchColumn();
    }

    if (tableExists($conn, 'cart_items')) {
        $totalCartItems = (int)$conn->query("SELECT COUNT(*) FROM `cart_items`")->fetchColumn();
    }

    $statusColumn = null;
    $orderTotalColumn = null;
    $orderDateColumn = null;

    if (tableExists($conn, 'orders')) {
        $totalOrders = (int)$conn->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();

        if (hasColumn($ordersColumns, 'total_amount')) $orderTotalColumn = 'total_amount';
        elseif (hasColumn($ordersColumns, 'grand_total')) $orderTotalColumn = 'grand_total';
        elseif (hasColumn($ordersColumns, 'total')) $orderTotalColumn = 'total';

        if ($orderTotalColumn) {
            $totalRevenue = (float)$conn->query("SELECT COALESCE(SUM(" . quoteIdentifier($orderTotalColumn) . "),0) FROM `orders`")->fetchColumn();
        }

        if (hasColumn($ordersColumns, 'order_status')) $statusColumn = 'order_status';
        elseif (hasColumn($ordersColumns, 'status')) $statusColumn = 'status';

        if ($statusColumn) {
            $qStatus = quoteIdentifier($statusColumn);
            $pendingOrders = (int)$conn->query("
                SELECT COUNT(*) FROM `orders`
                WHERE LOWER(COALESCE($qStatus, '')) IN ('pending','processing')
            ")->fetchColumn();

            $completedOrders = (int)$conn->query("
                SELECT COUNT(*) FROM `orders`
                WHERE LOWER(COALESCE($qStatus, '')) IN ('completed','delivered','paid')
            ")->fetchColumn();
        }

        if (hasColumn($ordersColumns, 'created_at')) $orderDateColumn = 'created_at';
        elseif (hasColumn($ordersColumns, 'order_date')) $orderDateColumn = 'order_date';
        elseif (hasColumn($ordersColumns, 'created_on')) $orderDateColumn = 'created_on';

        $selectUserId = hasColumn($ordersColumns, 'user_id') ? "COALESCE(o.`user_id`,0) AS user_id" : "0 AS user_id";
        $selectUserName = (tableExists($conn, 'users') && hasColumn($ordersColumns, 'user_id')) ? "COALESCE(u.`name`,'N/A') AS user_name" : "'N/A' AS user_name";
        $selectUserEmail = (tableExists($conn, 'users') && hasColumn($ordersColumns, 'user_id')) ? "COALESCE(u.`email`,'') AS user_email" : "'' AS user_email";
        $selectTotal = $orderTotalColumn ? "COALESCE(o." . quoteIdentifier($orderTotalColumn) . ",0) AS total_amount" : "0 AS total_amount";
        $selectStatus = $statusColumn ? "COALESCE(o." . quoteIdentifier($statusColumn) . ",'N/A') AS order_status" : "'N/A' AS order_status";
        $selectDate = $orderDateColumn ? "o." . quoteIdentifier($orderDateColumn) . " AS created_at" : "NULL AS created_at";

        $recentOrdersSql = "
            SELECT
                o.`id`,
                $selectUserId,
                $selectUserName,
                $selectUserEmail,
                $selectTotal,
                $selectStatus,
                $selectDate
            FROM `orders` o
        ";

        if (tableExists($conn, 'users') && hasColumn($ordersColumns, 'user_id')) {
            $recentOrdersSql .= " LEFT JOIN `users` u ON u.`id` = o.`user_id` ";
        }

        $recentOrdersSql .= " ORDER BY o.`id` DESC LIMIT 10";
        $recentOrders = $conn->query($recentOrdersSql)->fetchAll(PDO::FETCH_ASSOC);
    }

    if (tableExists($conn, 'carts')) {
        $cartDateColumn = null;
        if (hasColumn($cartsColumns, 'updated_at')) $cartDateColumn = 'updated_at';
        elseif (hasColumn($cartsColumns, 'created_at')) $cartDateColumn = 'created_at';

        $cartTotalExpr = "0";
        if (tableExists($conn, 'cart_items')) {
            if (hasColumn($cartItemsColumns, 'quantity') && hasColumn($cartItemsColumns, 'price_at_purchase')) {
                $cartTotalExpr = "COALESCE(SUM(ci.`quantity` * ci.`price_at_purchase`),0)";
            } elseif (hasColumn($cartItemsColumns, 'quantity') && hasColumn($cartItemsColumns, 'price')) {
                $cartTotalExpr = "COALESCE(SUM(ci.`quantity` * ci.`price`),0)";
            }
        }

        $recentCartsSql = "
            SELECT
                c.`id` AS cart_id,
                " . (hasColumn($cartsColumns, 'user_id') ? "COALESCE(c.`user_id`,0) AS user_id" : "0 AS user_id") . ",
                " . ((tableExists($conn, 'users') && hasColumn($cartsColumns, 'user_id')) ? "COALESCE(u.`name`,'N/A') AS user_name" : "'N/A' AS user_name") . ",
                " . ((tableExists($conn, 'users') && hasColumn($cartsColumns, 'user_id')) ? "COALESCE(u.`email`,'') AS user_email" : "'' AS user_email") . ",
                " . (tableExists($conn, 'cart_items') ? "COUNT(ci.`id`)" : "0") . " AS total_items,
                $cartTotalExpr AS cart_total,
                " . ($cartDateColumn ? "c." . quoteIdentifier($cartDateColumn) : "NULL") . " AS updated_at
            FROM `carts` c
        ";

        if (tableExists($conn, 'users') && hasColumn($cartsColumns, 'user_id')) {
            $recentCartsSql .= " LEFT JOIN `users` u ON u.`id` = c.`user_id` ";
        }

        if (tableExists($conn, 'cart_items')) {
            $recentCartsSql .= " LEFT JOIN `cart_items` ci ON ci.`cart_id` = c.`id` ";
        }

        $recentCartsSql .= " GROUP BY c.`id` ORDER BY c.`id` DESC LIMIT 10";
        $recentCarts = $conn->query($recentCartsSql)->fetchAll(PDO::FETCH_ASSOC);
    }

    if (tableExists($conn, 'wishlists') && tableExists($conn, 'products')) {
        $topWishlistSql = "
            SELECT
                p.`id` AS product_id,
                COALESCE(p.`name`,'Deleted Product') AS product_name,
                " . (hasColumn($productsColumns, 'sku') ? "COALESCE(p.`sku`,'N/A')" : "'N/A'") . " AS sku,
                " . (hasColumn($productsColumns, 'price') ? "COALESCE(p.`price`,0)" : "0") . " AS price,
                " . (hasColumn($productsColumns, 'images') ? "p.`images`" : "NULL") . " AS images,
                " . ((tableExists($conn, 'categories') && hasColumn($productsColumns, 'category_id')) ? "COALESCE(cat.`name`,'N/A')" : "'N/A'") . " AS category_name,
                COUNT(w.`id`) AS total_wishlist
            FROM `wishlists` w
            LEFT JOIN `products` p ON p.`id` = w.`product_id`
        ";

        if (tableExists($conn, 'categories') && hasColumn($productsColumns, 'category_id')) {
            $topWishlistSql .= " LEFT JOIN `categories` cat ON cat.`id` = p.`category_id` ";
        }

        $topWishlistSql .= " GROUP BY p.`id` ORDER BY total_wishlist DESC, p.`id` DESC LIMIT 10";
        $topWishlistProducts = $conn->query($topWishlistSql)->fetchAll(PDO::FETCH_ASSOC);
    }

    if (tableExists($conn, 'wishlists') && tableExists($conn, 'users') && hasColumn($wishlistsColumns, 'user_id')) {
        $topWishlistUsers = $conn->query("
            SELECT
                u.`id` AS user_id,
                COALESCE(u.`name`,'N/A') AS user_name,
                COALESCE(u.`email`,'') AS user_email,
                COUNT(w.`id`) AS wishlist_items
            FROM `wishlists` w
            LEFT JOIN `users` u ON u.`id` = w.`user_id`
            GROUP BY u.`id`
            ORDER BY wishlist_items DESC, u.`id` DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    if (tableExists($conn, 'order_items') && tableExists($conn, 'products')) {
        $qtyExpr = hasColumn($orderItemsColumns, 'quantity') ? "oi.`quantity`" : "1";
        $priceExpr = hasColumn($orderItemsColumns, 'price') ? "oi.`price`" : (hasColumn($orderItemsColumns, 'price_at_purchase') ? "oi.`price_at_purchase`" : "0");

        $topSellingProducts = $conn->query("
            SELECT
                p.`id` AS product_id,
                COALESCE(p.`name`,'Deleted Product') AS product_name,
                " . (hasColumn($productsColumns, 'sku') ? "COALESCE(p.`sku`,'N/A')" : "'N/A'") . " AS sku,
                " . (hasColumn($productsColumns, 'images') ? "p.`images`" : "NULL") . " AS images,
                COUNT(oi.`id`) AS total_lines,
                COALESCE(SUM($qtyExpr),0) AS total_qty,
                COALESCE(SUM(($qtyExpr) * ($priceExpr)),0) AS total_sales
            FROM `order_items` oi
            LEFT JOIN `products` p ON p.`id` = oi.`product_id`
            GROUP BY p.`id`
            ORDER BY total_qty DESC, total_sales DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    $salesMap = [];
    foreach ($topSellingProducts as $row) {
        $salesMap[(string)($row['product_name'] ?? '')] = (int)($row['total_qty'] ?? 0);
    }

    $chartSource = array_slice($topWishlistProducts, 0, 6);
    foreach ($chartSource as $row) {
        $name = (string)($row['product_name'] ?? 'Product');
        $chartLabels[] = mb_strimwidth($name, 0, 18, '...');
        $chartWishlistCounts[] = (int)($row['total_wishlist'] ?? 0);
        $chartSalesCounts[] = (int)($salesMap[$name] ?? 0);
    }

} catch (Throwable $e) {
    $errors[] = 'Analytics load failed: ' . $e->getMessage();
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid analytics-page">
        <div class="page-head">
            <div>
                <h4 class="page-title">📊 Store Analytics Dashboard</h4>
                <p class="page-subtitle">Live metrics, recent activity, and top-performing products.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-users"></i></div>
                    <span class="stat-label">Total Users</span>
                    <h3 class="stat-value"><?= number_format($totalUsers) ?></h3>
                    <div class="stat-meta">Registered accounts</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-box"></i></div>
                    <span class="stat-label">Products</span>
                    <h3 class="stat-value"><?= number_format($totalProducts) ?></h3>
                    <div class="stat-meta"><?= number_format($totalCategories) ?> categories</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-heart"></i></div>
                    <span class="stat-label">Wishlist Items</span>
                    <h3 class="stat-value"><?= number_format($totalWishlists) ?></h3>
                    <div class="stat-meta"><?= number_format($activeWishlistUsers) ?> active users</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                    <span class="stat-label">Cart Items</span>
                    <h3 class="stat-value"><?= number_format($totalCartItems) ?></h3>
                    <div class="stat-meta"><?= number_format($totalCarts) ?> carts</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-file-invoice"></i></div>
                    <span class="stat-label">Orders</span>
                    <h3 class="stat-value"><?= number_format($totalOrders) ?></h3>
                    <div class="stat-meta">From orders table</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
                    <span class="stat-label">Revenue</span>
                    <h3 class="stat-value">₹<?= number_format($totalRevenue, 2) ?></h3>
                    <div class="stat-meta">Detected total column</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-clock"></i></div>
                    <span class="stat-label">Pending Orders</span>
                    <h3 class="stat-value"><?= number_format($pendingOrders) ?></h3>
                    <div class="stat-meta">Pending or processing</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                    <span class="stat-label">Completed Orders</span>
                    <h3 class="stat-value"><?= number_format($completedOrders) ?></h3>
                    <div class="stat-meta">Completed, delivered, or paid</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card-box">
                    <div class="card-head">
                        <h5 class="card-title">Wishlist vs Sales Chart</h5>
                        <span class="badge-soft"><?= count($chartLabels) ?> Products</span>
                    </div>
                    <div class="chart-box">
                        <?php if (!empty($chartLabels)): ?>
                            <div class="chart-holder">
                                <canvas id="wishlistSalesChart"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="empty-box">No chart data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card-box">
                    <div class="card-head">
                        <h5 class="card-title">Recent Orders</h5>
                        <span class="badge-soft"><?= count($recentOrders) ?> Rows</span>
                    </div>
                    <div class="table-responsive table-wrap">
                        <table class="table analytics-table align-middle">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentOrders)): ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <div class="strong-text">#<?= (int)($order['id'] ?? 0) ?></div>
                                                <div class="mini-text">User ID: <?= (int)($order['user_id'] ?? 0) ?></div>
                                            </td>
                                            <td>
                                                <div class="strong-text"><?= e($order['user_name'] ?? 'N/A') ?></div>
                                                <div class="mini-text"><?= e($order['user_email'] ?? '') ?></div>
                                            </td>
                                            <td><span class="status-pill"><?= e($order['order_status'] ?? 'N/A') ?></span></td>
                                            <td><span class="price">₹<?= number_format((float)($order['total_amount'] ?? 0), 2) ?></span></td>
                                            <td><span class="mini-text"><?= safeDateFormat($order['created_at'] ?? null) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5"><div class="empty-box">No orders data found</div></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-box">
                    <div class="card-head">
                        <h5 class="card-title">Recent Carts</h5>
                        <span class="badge-soft"><?= count($recentCarts) ?> Rows</span>
                    </div>
                    <div class="table-responsive table-wrap">
                        <table class="table analytics-table align-middle">
                            <thead>
                                <tr>
                                    <th>Cart</th>
                                    <th>User</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentCarts)): ?>
                                    <?php foreach ($recentCarts as $cart): ?>
                                        <tr>
                                            <td>
                                                <div class="strong-text">#<?= (int)($cart['cart_id'] ?? 0) ?></div>
                                                <div class="mini-text">User ID: <?= (int)($cart['user_id'] ?? 0) ?></div>
                                            </td>
                                            <td>
                                                <div class="strong-text"><?= e($cart['user_name'] ?? 'N/A') ?></div>
                                                <div class="mini-text"><?= e($cart['user_email'] ?? '') ?></div>
                                            </td>
                                            <td><div class="strong-text"><?= (int)($cart['total_items'] ?? 0) ?></div></td>
                                            <td><span class="total">₹<?= number_format((float)($cart['cart_total'] ?? 0), 2) ?></span></td>
                                            <td><span class="mini-text"><?= safeDateFormat($cart['updated_at'] ?? null) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5"><div class="empty-box">No cart data found</div></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card-box">
                    <div class="card-head">
                        <h5 class="card-title">Top Wishlist Products</h5>
                        <span class="badge-soft"><?= count($topWishlistProducts) ?> Products</span>
                    </div>
                    <div class="table-responsive table-wrap">
                        <table class="table analytics-table align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Wishlists</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topWishlistProducts)): ?>
                                    <?php foreach ($topWishlistProducts as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="product-box">
                                                    <img src="<?= e(productImage($product['images'] ?? null)) ?>" alt="product" class="product-img">
                                                    <div>
                                                        <div class="strong-text"><?= e($product['product_name'] ?? 'Deleted Product') ?></div>
                                                        <div class="mini-text">SKU: <?= e($product['sku'] ?? 'N/A') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="status-pill"><?= e($product['category_name'] ?? 'N/A') ?></span></td>
                                            <td><span class="price">₹<?= number_format((float)($product['price'] ?? 0), 2) ?></span></td>
                                            <td><div class="strong-text"><?= (int)($product['total_wishlist'] ?? 0) ?></div></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4"><div class="empty-box">No wishlist product data found</div></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-box">
                    <div class="card-head">
                        <h5 class="card-title">Top Wishlist Users</h5>
                        <span class="badge-soft"><?= count($topWishlistUsers) ?> Users</span>
                    </div>
                    <div class="table-responsive table-wrap">
                        <table class="table analytics-table align-middle">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Wishlist Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topWishlistUsers)): ?>
                                    <?php foreach ($topWishlistUsers as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="strong-text"><?= e($user['user_name'] ?? 'N/A') ?></div>
                                                <div class="mini-text">User ID: <?= (int)($user['user_id'] ?? 0) ?></div>
                                            </td>
                                            <td><div class="mini-text"><?= e($user['user_email'] ?? '') ?></div></td>
                                            <td><div class="strong-text"><?= (int)($user['wishlist_items'] ?? 0) ?></div></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3"><div class="empty-box">No wishlist user data found</div></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card-box">
                    <div class="card-head">
                        <h5 class="card-title">Top Selling Products</h5>
                        <span class="badge-soft"><?= count($topSellingProducts) ?> Products</span>
                    </div>
                    <div class="table-responsive table-wrap">
                        <table class="table analytics-table align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Order Lines</th>
                                    <th>Quantity Sold</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topSellingProducts)): ?>
                                    <?php foreach ($topSellingProducts as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="product-box">
                                                    <img src="<?= e(productImage($product['images'] ?? null)) ?>" alt="product" class="product-img">
                                                    <div>
                                                        <div class="strong-text"><?= e($product['product_name'] ?? 'Deleted Product') ?></div>
                                                        <div class="mini-text">SKU: <?= e($product['sku'] ?? 'N/A') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><div class="strong-text"><?= (int)($product['total_lines'] ?? 0) ?></div></td>
                                            <td><div class="strong-text"><?= (int)($product['total_qty'] ?? 0) ?></div></td>
                                            <td><span class="total">₹<?= number_format((float)($product['total_sales'] ?? 0), 2) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4"><div class="empty-box">No sales data found</div></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const wishlistData = <?= json_encode($chartWishlistCounts) ?>;
    const salesData = <?= json_encode($chartSalesCounts) ?>;

    const canvas = document.getElementById('wishlistSalesChart');
    if (!canvas || !labels.length) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Wishlist Count',
                    data: wishlistData,
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 8
                },
                {
                    label: 'Sold Quantity',
                    data: salesData,
                    backgroundColor: 'rgba(25, 135, 84, 0.75)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1,
                    borderRadius: 8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        color: '#374151',
                        font: { size: 12, weight: '600' }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#6b7280', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#6b7280' },
                    grid: { color: 'rgba(148,163,184,.18)' }
                }
            }
        }
    });
});
</script>