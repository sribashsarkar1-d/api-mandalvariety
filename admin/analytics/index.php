<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    .analytics-container {
        padding: 24px;
        background-color: #f8fafc;
        min-height: calc(100vh - 70px);
    }
    .stat-card {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        z-index: 1;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
    }
    .stat-icon.primary { background: linear-gradient(135deg, rgba(79,70,229,0.1) 0%, rgba(59,130,246,0.1) 100%); color: #4f46e5; }
    .stat-icon.success { background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(52,211,153,0.1) 100%); color: #10b981; }
    .stat-icon.warning { background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(251,191,36,0.1) 100%); color: #f59e0b; }
    .stat-icon.info { background: linear-gradient(135deg, rgba(14,165,233,0.1) 0%, rgba(56,189,248,0.1) 100%); color: #0ea5e9; }
    .stat-icon.danger { background: linear-gradient(135deg, rgba(220,38,38,0.1) 0%, rgba(248,113,113,0.1) 100%); color: #dc2626; }
    
    .stat-label {
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 8px;
    }
    .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.1;
    }
    .stat-meta {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: auto;
    }
    
    .card-box {
        border-radius: 20px;
        border: none;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .card-head {
        background: transparent;
        border-bottom: 1px solid #f1f5f9;
        padding: 24px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .card-title {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1.1rem;
    }
    .badge-soft {
        background: #f1f5f9;
        color: #475569;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .chart-box {
        padding: 24px;
        flex-grow: 1;
    }
    .chart-holder {
        position: relative;
        height: 350px;
        width: 100%;
    }
    .table-wrap {
        padding: 0;
        flex-grow: 1;
        overflow-x: auto;
    }
    .analytics-table {
        margin-bottom: 0;
        width: 100%;
    }
    .analytics-table th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 28px;
        border-top: none;
        white-space: nowrap;
    }
    .analytics-table td {
        padding: 18px 28px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-weight: 500;
    }
    .analytics-table tbody tr {
        transition: background-color 0.2s ease;
    }
    .analytics-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .empty-box {
        padding: 48px 24px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.95rem;
    }
    .product-box {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 240px;
    }
    .product-img {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        flex-shrink: 0;
    }
    .strong-text {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
        margin-bottom: 2px;
    }
    .mini-text {
        font-size: 0.85rem;
        color: #64748b;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        text-transform: uppercase;
    }
    .price {
        font-weight: 700;
        color: #059669;
        font-size: 0.95rem;
    }
    .total {
        font-weight: 800;
        color: #2563eb;
        font-size: 0.95rem;
    }
    
    .dark-mode .analytics-container { background: #0f172a; }
    .dark-mode .stat-card, .dark-mode .card-box { background: #1e293b; box-shadow: none; }
    .dark-mode .stat-value, .dark-mode .card-title, .dark-mode .strong-text { color: #f8fafc; }
    .dark-mode .card-head { border-bottom-color: #334155; }
    .dark-mode .analytics-table th { background: #0f172a; border-bottom-color: #334155; color: #94a3b8; }
    .dark-mode .analytics-table td { border-bottom-color: #334155; color: #cbd5e1; }
    .dark-mode .analytics-table tbody tr:hover { background-color: #0f172a; }
    .dark-mode .badge-soft, .dark-mode .status-pill { background: #334155; color: #e2e8f0; }
    .dark-mode .product-img { border-color: #334155; }
    @media (max-width: 768px){
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

    <div class="analytics-container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="mb-1 fw-bold" style="color: var(--bs-heading-color);">Store Analytics Dashboard</h3>
                <p class="text-muted mb-0 fs-6">Live metrics, recent activity, and top-performing products.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
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
                    <div class="stat-icon primary"><svg class="icon"><use href="#icon-customers"></use></svg></div>
                    <span class="stat-label">Total Users</span>
                    <h3 class="stat-value"><?= number_format($totalUsers) ?></h3>
                    <div class="stat-meta">Registered accounts</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon info"><svg class="icon"><use href="#icon-products"></use></svg></div>
                    <span class="stat-label">Products</span>
                    <h3 class="stat-value"><?= number_format($totalProducts) ?></h3>
                    <div class="stat-meta"><?= number_format($totalCategories) ?> categories</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon warning"><svg class="icon"><use href="#icon-wishlist"></use></svg></div>
                    <span class="stat-label">Wishlist Items</span>
                    <h3 class="stat-value"><?= number_format($totalWishlists) ?></h3>
                    <div class="stat-meta"><?= number_format($activeWishlistUsers) ?> active users</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon success"><svg class="icon"><use href="#icon-cart"></use></svg></div>
                    <span class="stat-label">Cart Items</span>
                    <h3 class="stat-value"><?= number_format($totalCartItems) ?></h3>
                    <div class="stat-meta"><?= number_format($totalCarts) ?> carts</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon primary"><svg class="icon"><use href="#icon-orders"></use></svg></div>
                    <span class="stat-label">Orders</span>
                    <h3 class="stat-value"><?= number_format($totalOrders) ?></h3>
                    <div class="stat-meta">Total orders placed</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon success"><svg class="icon"><use href="#icon-analytics"></use></svg></div>
                    <span class="stat-label">Revenue</span>
                    <h3 class="stat-value">₹<?= number_format($totalRevenue, 2) ?></h3>
                    <div class="stat-meta">Total generated revenue</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon warning"><svg class="icon"><use href="#icon-review"></use></svg></div>
                    <span class="stat-label">Pending Orders</span>
                    <h3 class="stat-value"><?= number_format($pendingOrders) ?></h3>
                    <div class="stat-meta">Awaiting processing</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon info"><svg class="icon"><use href="#icon-settings"></use></svg></div>
                    <span class="stat-label">Completed Orders</span>
                    <h3 class="stat-value"><?= number_format($completedOrders) ?></h3>
                    <div class="stat-meta">Successfully delivered</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
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

        <div class="row g-4 mb-5">
            <div class="col-lg-6 d-flex">
                <div class="card-box w-100">
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

            <div class="col-lg-6 d-flex">
                <div class="card-box w-100">
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

        <div class="row g-4 mb-5">
            <div class="col-lg-6 d-flex">
                <div class="card-box w-100">
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
                                            <td><div class="strong-text text-center"><?= (int)($product['total_wishlist'] ?? 0) ?></div></td>
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

            <div class="col-lg-6 d-flex">
                <div class="card-box w-100">
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
                                    <th class="text-center">Wishlist Items</th>
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
                                            <td class="text-center"><div class="strong-text"><?= (int)($user['wishlist_items'] ?? 0) ?></div></td>
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
                                    <th class="text-center">Order Lines</th>
                                    <th class="text-center">Quantity Sold</th>
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
                                            <td class="text-center"><div class="strong-text"><?= (int)($product['total_lines'] ?? 0) ?></div></td>
                                            <td class="text-center"><div class="strong-text"><?= (int)($product['total_qty'] ?? 0) ?></div></td>
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