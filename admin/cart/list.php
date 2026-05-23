<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    body{
        background:#f4f7fb;
    }

    .cart-wrapper{
        padding:24px;
    }

    .cart-page-title{
        font-size:24px;
        font-weight:700;
        color:#212529;
        margin-bottom:4px;
    }

    .cart-page-subtitle{
        font-size:14px;
        color:#6c757d;
        margin-bottom:0;
    }

    .cart-stat-card,
    .cart-main-card{
        background:#fff;
        border:none;
        border-radius:16px;
        box-shadow:0 4px 20px rgba(0,0,0,0.06);
    }

    .cart-stat-card{
        padding:20px;
        height:100%;
        transition:all .2s ease-in-out;
    }

    .cart-stat-card:hover{
        transform:translateY(-2px);
    }

    .cart-stat-label{
        font-size:13px;
        color:#6c757d;
        margin-bottom:8px;
        display:block;
    }

    .cart-stat-value{
        font-size:28px;
        font-weight:700;
        color:#0d6efd;
        margin:0;
        line-height:1.1;
    }

    .cart-main-header{
        padding:18px 22px;
        border-bottom:1px solid #eef1f5;
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:12px;
    }

    .cart-main-title{
        font-size:17px;
        font-weight:700;
        color:#212529;
        margin:0;
    }

    .cart-badge-count{
        background:#e8f1ff;
        color:#0d6efd;
        padding:7px 12px;
        border-radius:50px;
        font-size:12px;
        font-weight:700;
    }

    .cart-table{
        margin-bottom:0;
    }

    .cart-table thead th{
        background:#f8f9fc;
        color:#495057;
        font-size:12px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.4px;
        padding:14px 16px;
        border-bottom:1px solid #e9ecef;
        white-space:nowrap;
    }

    .cart-table tbody td{
        padding:16px;
        vertical-align:middle;
        border-top:1px solid #f1f3f5;
    }

    .cart-table tbody tr{
        transition:background .2s ease-in-out;
    }

    .cart-table tbody tr:hover{
        background:#f9fbff;
    }

    .cart-product-box{
        display:flex;
        align-items:center;
        gap:12px;
        min-width:220px;
    }

    .cart-product-img{
        width:64px;
        height:64px;
        object-fit:cover;
        border-radius:12px;
        border:1px solid #e9ecef;
        background:#fff;
    }

    .cart-product-name{
        font-size:14px;
        font-weight:700;
        color:#212529;
        margin-bottom:4px;
    }

    .cart-small-text{
        font-size:12px;
        color:#6c757d;
        margin:0;
    }

    .cart-user-name{
        font-size:14px;
        font-weight:600;
        color:#212529;
        margin-bottom:3px;
    }

    .cart-price{
        font-size:14px;
        font-weight:700;
        color:#198754;
    }

    .cart-total{
        font-size:14px;
        font-weight:700;
        color:#0d6efd;
    }

    .cart-category{
        display:inline-block;
        background:#f1f3f5;
        color:#495057;
        padding:6px 10px;
        border-radius:30px;
        font-size:12px;
        font-weight:600;
    }

    .cart-date{
        font-size:13px;
        color:#6c757d;
        white-space:nowrap;
    }

    .cart-action-btn{
        border-radius:10px;
        font-size:13px;
        font-weight:600;
        padding:7px 12px;
    }

    .cart-empty{
        padding:40px 20px;
        text-align:center;
        color:#6c757d;
        font-size:14px;
    }

    .alert{
        border:none;
        border-radius:12px;
        box-shadow:0 4px 14px rgba(0,0,0,0.05);
    }

    @media (max-width: 768px){
        .cart-wrapper{
            padding:16px;
        }

        .cart-page-title{
            font-size:20px;
        }

        .cart-main-header{
            align-items:flex-start;
        }

        .cart-table thead th,
        .cart-table tbody td{
            padding:12px;
        }
    }
</style>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'remove_item') {
            $itemId = (int)($_POST['item_id'] ?? 0);

            if ($itemId <= 0) {
                $errors[] = 'Invalid cart item selected';
            } else {
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
                $stmt->execute([$itemId]);
                $success = 'Cart item removed successfully';
            }
        }

        if ($action === 'clear_cart') {
            $cartId = (int)($_POST['cart_id'] ?? 0);

            if ($cartId <= 0) {
                $errors[] = 'Invalid cart selected';
            } else {
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                $stmt->execute([$cartId]);
                $success = 'Cart cleared successfully';
            }
        }

    } catch (Exception $e) {
        $errors[] = 'Action failed: ' . $e->getMessage();
    }
}

$sql = "
    SELECT
        c.id AS cart_id,
        c.user_id,
        c.created_at,
        c.updated_at,
        u.name AS user_name,
        u.email AS user_email,
        u.phone AS user_phone,
        COUNT(ci.id) AS total_items,
        COALESCE(SUM(ci.quantity * ci.price_at_purchase), 0) AS cart_total
    FROM carts c
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN cart_items ci ON ci.cart_id = c.id
    GROUP BY c.id, c.user_id, c.created_at, c.updated_at, u.name, u.email, u.phone
    ORDER BY c.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itemSql = "
    SELECT
        ci.id AS cart_item_id,
        ci.cart_id,
        ci.product_id,
        ci.quantity,
        ci.price_at_purchase,
        ci.created_at,
        p.name AS product_name,
        p.slug,
        p.sku,
        p.images,
        p.price AS current_price,
        u.name AS user_name,
        u.email AS user_email
    FROM cart_items ci
    INNER JOIN carts c ON c.id = ci.cart_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN products p ON p.id = ci.product_id
    ORDER BY ci.id DESC
";

$itemStmt = $conn->prepare($itemSql);
$itemStmt->execute();
$cartItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

function productImage($imagesJson) {
    $images = json_decode((string)$imagesJson, true);
    if (is_array($images) && !empty($images[0])) {
        return '../uploads/' . $images[0];
    }
    return 'https://via.placeholder.com/60x60?text=No+Image';
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid cart-wrapper">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="cart-page-title">🛒 User Carts</h4>
                <p class="cart-page-subtitle">View all user cart data, products, totals and manage cart items</p>
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

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="cart-main-card">
                    <div class="cart-main-header">
                        <h5 class="cart-main-title">Cart Summary</h5>
                        <span class="cart-badge-count"><?= count($carts) ?> Carts</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th>Cart</th>
                                    <th>User</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($carts)): ?>
                                    <?php foreach ($carts as $cart): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?= (int)$cart['cart_id'] ?></strong><br>
                                                <span class="cart-small-text">User ID: <?= (int)$cart['user_id'] ?></span>
                                            </td>
                                            <td>
                                                <div class="cart-user-name"><?= e($cart['user_name'] ?: 'N/A') ?></div>
                                                <span class="cart-small-text"><?= e($cart['user_email'] ?: 'No email') ?></span>
                                            </td>
                                            <td>
                                                <strong><?= (int)$cart['total_items'] ?></strong>
                                            </td>
                                            <td>
                                                <span class="cart-price">₹<?= number_format((float)$cart['cart_total'], 2) ?></span>
                                            </td>
                                            <td>
                                                <form method="POST" onsubmit="return confirm('Clear all items from this cart?');" class="d-inline-block">
                                                    <input type="hidden" name="action" value="clear_cart">
                                                    <input type="hidden" name="cart_id" value="<?= (int)$cart['cart_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger cart-action-btn">
                                                        <i class="fas fa-trash me-1"></i>Clear
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="cart-empty">No carts found</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="cart-main-card">
                    <div class="cart-main-header">
                        <h5 class="cart-main-title">Cart Items Details</h5>
                        <span class="cart-badge-count"><?= count($cartItems) ?> Items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>User</th>
                                    <th>Cart</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Added</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cartItems)): ?>
                                    <?php foreach ($cartItems as $item): ?>
                                        <?php $lineTotal = (float)$item['quantity'] * (float)$item['price_at_purchase']; ?>
                                        <tr>
                                            <td>
                                                <img src="<?= e(productImage($item['images'])) ?>" 
                                                     alt="<?= e($item['product_name'] ?? 'Product') ?>" 
                                                     class="cart-product-img">
                                            </td>
                                            <td>
                                                <div class="cart-product-name"><?= e($item['product_name'] ?: 'Deleted Product') ?></div>
                                                <p class="cart-small-text mb-1">SKU: <?= e($item['sku'] ?: 'N/A') ?></p>
                                                <p class="cart-small-text mb-0">Current: ₹<?= number_format((float)($item['current_price'] ?? 0), 2) ?></p>
                                            </td>
                                            <td>
                                                <div class="cart-user-name"><?= e($item['user_name'] ?: 'N/A') ?></div>
                                                <span class="cart-small-text"><?= e($item['user_email'] ?: '') ?></span>
                                            </td>
                                            <td>
                                                <strong>#<?= (int)$item['cart_id'] ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= (int)$item['quantity'] ?></span>
                                            </td>
                                            <td>
                                                <span class="cart-price">₹<?= number_format((float)$item['price_at_purchase'], 2) ?></span>
                                            </td>
                                            <td>
                                                <span class="cart-total">₹<?= number_format($lineTotal, 2) ?></span>
                                            </td>
                                            <td>
                                                <span class="cart-date"><?= date('d M, Y', strtotime($item['created_at'])) ?></span>
                                            </td>
                                            <td>
                                                <form method="POST" onsubmit="return confirm('Remove this cart item?');" class="d-inline-block">
                                                    <input type="hidden" name="action" value="remove_item">
                                                    <input type="hidden" name="item_id" value="<?= (int)$item['cart_item_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger cart-action-btn">
                                                        <i class="fas fa-trash me-1"></i>Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="cart-empty">No cart items found</div>
                                        </td>
                                    </tr>
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