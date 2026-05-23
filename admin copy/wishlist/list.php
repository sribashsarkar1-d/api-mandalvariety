<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    body{
        background:#f4f7fb;
    }

    .wishlist-wrapper{
        padding:24px;
    }

    .wishlist-page-title{
        font-size:24px;
        font-weight:700;
        color:#212529;
        margin-bottom:4px;
    }

    .wishlist-page-subtitle{
        font-size:14px;
        color:#6c757d;
        margin-bottom:0;
    }

    .wishlist-stat-card,
    .wishlist-main-card{
        background:#fff;
        border:none;
        border-radius:16px;
        box-shadow:0 4px 20px rgba(0,0,0,0.06);
    }

    .wishlist-stat-card{
        padding:20px;
        height:100%;
        transition:all .2s ease-in-out;
    }

    .wishlist-stat-card:hover{
        transform:translateY(-2px);
    }

    .wishlist-stat-label{
        font-size:13px;
        color:#6c757d;
        margin-bottom:8px;
        display:block;
    }

    .wishlist-stat-value{
        font-size:28px;
        font-weight:700;
        color:#0d6efd;
        margin:0;
        line-height:1.1;
    }

    .wishlist-main-header{
        padding:18px 22px;
        border-bottom:1px solid #eef1f5;
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:12px;
    }

    .wishlist-main-title{
        font-size:17px;
        font-weight:700;
        color:#212529;
        margin:0;
    }

    .wishlist-badge-count{
        background:#e8f1ff;
        color:#0d6efd;
        padding:7px 12px;
        border-radius:50px;
        font-size:12px;
        font-weight:700;
    }

    .wishlist-table{
        margin-bottom:0;
    }

    .wishlist-table thead th{
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

    .wishlist-table tbody td{
        padding:16px;
        vertical-align:middle;
        border-top:1px solid #f1f3f5;
    }

    .wishlist-table tbody tr{
        transition:background .2s ease-in-out;
    }

    .wishlist-table tbody tr:hover{
        background:#f9fbff;
    }

    .wishlist-product-box{
        display:flex;
        align-items:center;
        gap:12px;
        min-width:220px;
    }

    .wishlist-product-img{
        width:64px;
        height:64px;
        object-fit:cover;
        border-radius:12px;
        border:1px solid #e9ecef;
        background:#fff;
    }

    .wishlist-product-name{
        font-size:14px;
        font-weight:700;
        color:#212529;
        margin-bottom:4px;
    }

    .wishlist-small-text{
        font-size:12px;
        color:#6c757d;
        margin:0;
    }

    .wishlist-user-name{
        font-size:14px;
        font-weight:600;
        color:#212529;
        margin-bottom:3px;
    }

    .wishlist-price{
        font-size:14px;
        font-weight:700;
        color:#198754;
    }

    .wishlist-category{
        display:inline-block;
        background:#f1f3f5;
        color:#495057;
        padding:6px 10px;
        border-radius:30px;
        font-size:12px;
        font-weight:600;
    }

    .wishlist-date{
        font-size:13px;
        color:#6c757d;
        white-space:nowrap;
    }

    .wishlist-action-btn{
        border-radius:10px;
        font-size:13px;
        font-weight:600;
        padding:7px 12px;
    }

    .wishlist-empty{
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
        .wishlist-wrapper{
            padding:16px;
        }

        .wishlist-page-title{
            font-size:20px;
        }

        .wishlist-main-header{
            align-items:flex-start;
        }

        .wishlist-table thead th,
        .wishlist-table tbody td{
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove_wishlist_item') {
    $wishlistId = (int)($_POST['wishlist_id'] ?? 0);

    if ($wishlistId > 0) {
        $stmt = $conn->prepare("DELETE FROM wishlists WHERE id = ?");
        $stmt->execute([$wishlistId]);
        $success = 'Item removed from wishlist successfully.';
    } else {
        $errors[] = 'Invalid wishlist item selected.';
    }
}

$sql = "
    SELECT
        w.id AS wishlist_id,
        w.user_id,
        u.name AS user_name,
        u.email AS user_email,
        p.name AS product_name,
        p.sku,
        p.price,
        p.images,
        c.name AS category_name,
        w.created_at
    FROM wishlists w
    LEFT JOIN users u ON u.id = w.user_id
    LEFT JOIN products p ON p.id = w.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY w.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$summaryStmt = $conn->query("
    SELECT 
        COUNT(*) AS total_rows,
        COUNT(DISTINCT user_id) AS total_users,
        COUNT(DISTINCT product_id) AS total_products
    FROM wishlists
");
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

function getProductImage($imagesJson) {
    $images = json_decode((string)$imagesJson, true);
    return (!empty($images[0])) ? '../uploads/' . $images[0] : 'https://via.placeholder.com/64x64?text=No+Img';
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid wishlist-wrapper">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h4 class="wishlist-page-title">❤️ User Wishlist</h4>
                <p class="wishlist-page-subtitle">View all products saved by users from the website.</p>
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

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="wishlist-stat-card">
                    <span class="wishlist-stat-label">Total Wishlist Items</span>
                    <h4 class="wishlist-stat-value"><?= (int)($summary['total_rows'] ?? 0) ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wishlist-stat-card">
                    <span class="wishlist-stat-label">Users With Wishlist</span>
                    <h4 class="wishlist-stat-value"><?= (int)($summary['total_users'] ?? 0) ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wishlist-stat-card">
                    <span class="wishlist-stat-label">Saved Products</span>
                    <h4 class="wishlist-stat-value"><?= (int)($summary['total_products'] ?? 0) ?></h4>
                </div>
            </div>
        </div>

        <div class="wishlist-main-card">
            <div class="wishlist-main-header">
                <h5 class="wishlist-main-title">Wishlist Records</h5>
                <span class="wishlist-badge-count"><?= count($wishlistItems) ?> Items</span>
            </div>

            <div class="table-responsive">
                <table class="table wishlist-table align-middle">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>User Info</th>
                            <th>Price</th>
                            <th>Added On</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($wishlistItems)): ?>
                            <?php foreach ($wishlistItems as $item): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= (int)$item['wishlist_id'] ?></strong>
                                    </td>

                                    <td>
                                        <div class="wishlist-product-box">
                                            <img src="<?= e(getProductImage($item['images'])) ?>" alt="product" class="wishlist-product-img">
                                            <div>
                                                <div class="wishlist-product-name"><?= e($item['product_name'] ?: 'Deleted Product') ?></div>
                                                <p class="wishlist-small-text">SKU: <?= e($item['sku'] ?: 'N/A') ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="wishlist-category"><?= e($item['category_name'] ?: 'N/A') ?></span>
                                    </td>

                                    <td>
                                        <div class="wishlist-user-name"><?= e($item['user_name'] ?: 'N/A') ?></div>
                                        <p class="wishlist-small-text"><?= e($item['user_email'] ?: 'No email') ?></p>
                                    </td>

                                    <td>
                                        <span class="wishlist-price">₹<?= number_format((float)($item['price'] ?? 0), 2) ?></span>
                                    </td>

                                    <td>
                                        <span class="wishlist-date">
                                            <?= !empty($item['created_at']) ? date('d M, Y', strtotime($item['created_at'])) : 'N/A' ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <form method="POST" onsubmit="return confirm('Remove this wishlist item?');" class="d-inline-block">
                                            <input type="hidden" name="action" value="remove_wishlist_item">
                                            <input type="hidden" name="wishlist_id" value="<?= (int)$item['wishlist_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger wishlist-action-btn">
                                                <i class="fa fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="wishlist-empty">No wishlist data found.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>