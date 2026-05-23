<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    :root{
        --rv-primary:#0d6efd;
        --rv-success:#198754;
        --rv-danger:#dc3545;
        --rv-warning:#f59e0b;
        --rv-bg:#f4f7fb;
        --rv-card:#ffffff;
        --rv-text:#1f2937;
        --rv-muted:#6b7280;
        --rv-border:#e5e7eb;
        --rv-shadow:0 8px 24px rgba(15,23,42,.06);
        --rv-radius:16px;
    }

    body{
        background:var(--rv-bg);
    }

    .review-page{
        padding:24px;
    }

    .review-title{
        font-size:24px;
        font-weight:800;
        color:var(--rv-text);
        margin-bottom:4px;
    }

    .review-subtitle{
        font-size:14px;
        color:var(--rv-muted);
        margin:0;
    }

    .review-card,
    .review-stat-card,
    .review-toolbar{
        background:var(--rv-card);
        border:1px solid var(--rv-border);
        border-radius:var(--rv-radius);
        box-shadow:var(--rv-shadow);
    }

    .review-stat-card{
        padding:18px;
        height:100%;
        transition:all .2s ease;
    }

    .review-stat-card:hover{
        transform:translateY(-2px);
    }

    .review-stat-label{
        display:block;
        font-size:13px;
        color:var(--rv-muted);
        margin-bottom:8px;
    }

    .review-stat-value{
        font-size:28px;
        font-weight:800;
        color:var(--rv-text);
        margin:0;
        line-height:1.1;
    }

    .review-toolbar{
        padding:18px;
        margin:20px 0;
    }

    .review-search-wrap{
        position:relative;
    }

    .review-search-icon{
        position:absolute;
        top:50%;
        left:14px;
        transform:translateY(-50%);
        color:#9ca3af;
        font-size:14px;
        pointer-events:none;
    }

    .review-search-input,
    .review-select{
        height:46px;
        border-radius:12px;
        border:1px solid var(--rv-border);
        box-shadow:none;
        transition:.2s ease;
    }

    .review-search-input{
        padding-left:40px;
    }

    .review-search-input:focus,
    .review-select:focus{
        border-color:var(--rv-primary);
        box-shadow:0 0 0 4px rgba(13,110,253,.10);
    }

    .review-btn{
        height:46px;
        border-radius:12px;
        font-weight:700;
        padding:0 16px;
    }

    .review-card-header{
        padding:18px 20px;
        border-bottom:1px solid var(--rv-border);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .review-card-title{
        margin:0;
        font-size:16px;
        font-weight:700;
        color:var(--rv-text);
    }

    .review-badge-count{
        background:rgba(13,110,253,.10);
        color:var(--rv-primary);
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
    }

    .review-table{
        margin:0;
    }

    .review-table thead th{
        background:#f8fafc;
        color:#667085;
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.04em;
        font-weight:700;
        padding:14px 16px;
        border-bottom:1px solid var(--rv-border);
        white-space:nowrap;
    }

    .review-table tbody td{
        padding:16px;
        vertical-align:middle;
        border-top:1px solid #f1f5f9;
    }

    .review-table tbody tr:hover{
        background:#fafcff;
    }

    .review-product{
        min-width:220px;
    }

    .review-product-name{
        font-size:14px;
        font-weight:700;
        color:var(--rv-text);
        margin-bottom:4px;
    }

    .review-small{
        font-size:12px;
        color:var(--rv-muted);
    }

    .review-user-name{
        font-size:14px;
        font-weight:700;
        color:var(--rv-text);
        margin-bottom:4px;
    }

    .review-rating{
        display:inline-flex;
        gap:2px;
        color:#f59e0b;
        font-size:14px;
        letter-spacing:1px;
        margin-bottom:4px;
    }

    .review-comment{
        max-width:320px;
        font-size:13px;
        color:#374151;
        line-height:1.5;
    }

    .review-status{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        text-transform:capitalize;
    }

    .status-pending{
        background:rgba(245,158,11,.12);
        color:#b45309;
    }

    .status-approved{
        background:rgba(25,135,84,.12);
        color:#146c43;
    }

    .status-rejected{
        background:rgba(220,53,69,.12);
        color:#b02a37;
    }

    .review-date{
        font-size:13px;
        color:var(--rv-muted);
        white-space:nowrap;
    }

    .review-action-group{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
    }

    .review-empty{
        text-align:center;
        padding:40px 20px;
        color:var(--rv-muted);
        font-size:14px;
    }

    .alert{
        border:none;
        border-radius:12px;
        box-shadow:var(--rv-shadow);
    }

    @media (max-width: 767.98px){
        .review-page{
            padding:16px;
        }

        .review-title{
            font-size:20px;
        }

        .review-table thead th,
        .review-table tbody td{
            padding:12px;
        }

        .review-comment{
            max-width:220px;
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

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewId = (int)($_POST['review_id'] ?? 0);

    if ($reviewId <= 0) {
        $errors[] = 'Invalid review selected.';
    } else {
        try {
            if ($action === 'approve_review') {
                $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
                $stmt->execute([$reviewId]);
                $success = 'Review approved successfully.';
            } elseif ($action === 'reject_review') {
                $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$reviewId]);
                $success = 'Review rejected successfully.';
            } elseif ($action === 'delete_review') {
                $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$reviewId]);
                $success = 'Review deleted successfully.';
            }
        } catch (Exception $ex) {
            $errors[] = 'Action failed: ' . $ex->getMessage();
        }
    }
}

$params = [];
$sql = "
    SELECT
        r.id AS review_id,
        r.product_id,
        r.user_id,
        r.rating,
        r.title,
        r.comment,
        r.status,
        r.created_at,
        p.name AS product_name,
        p.sku,
        u.name AS user_name,
        u.email AS user_email
    FROM reviews r
    LEFT JOIN products p ON p.id = r.product_id
    LEFT JOIN users u ON u.id = r.user_id
    WHERE 1
";

if ($search !== '') {
    $sql .= " AND (
        p.name LIKE ?
        OR p.sku LIKE ?
        OR u.name LIKE ?
        OR u.email LIKE ?
        OR r.title LIKE ?
        OR r.comment LIKE ?
    )";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($statusFilter !== '' && in_array($statusFilter, ['pending','approved','rejected'], true)) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY r.id DESC";

$reviews = [];
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $ex) {
    $errors[] = 'Could not load reviews: ' . $ex->getMessage();
}

$summary = [
    'total_reviews' => 0,
    'pending_reviews' => 0,
    'approved_reviews' => 0,
    'rejected_reviews' => 0,
];

try {
    $summaryStmt = $conn->query("
        SELECT
            COUNT(*) AS total_reviews,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_reviews,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_reviews,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_reviews
        FROM reviews
    ");
    $summaryData = $summaryStmt->fetch(PDO::FETCH_ASSOC);
    if ($summaryData) {
        $summary = $summaryData;
    }
} catch (Exception $ex) {
    $errors[] = 'Could not load review summary.';
}

function renderStars($rating) {
    $rating = (int)$rating;
    $rating = max(1, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

function reviewStatusClass($status) {
    if ($status === 'approved') return 'status-approved';
    if ($status === 'rejected') return 'status-rejected';
    return 'status-pending';
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid review-page">
        <div class="mb-4">
            <h4 class="review-title">⭐ Review Management</h4>
            <p class="review-subtitle">Manage product reviews, reviewer users, moderation status, and feedback history.</p>
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

        <div class="row g-3">
            <div class="col-md-3">
                <div class="review-stat-card">
                    <span class="review-stat-label">Total Reviews</span>
                    <h3 class="review-stat-value"><?= (int)($summary['total_reviews'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="review-stat-card">
                    <span class="review-stat-label">Pending</span>
                    <h3 class="review-stat-value"><?= (int)($summary['pending_reviews'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="review-stat-card">
                    <span class="review-stat-label">Approved</span>
                    <h3 class="review-stat-value"><?= (int)($summary['approved_reviews'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="review-stat-card">
                    <span class="review-stat-label">Rejected</span>
                    <h3 class="review-stat-value"><?= (int)($summary['rejected_reviews'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <div class="review-toolbar">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-lg-7">
                    <div class="review-search-wrap">
                        <span class="review-search-icon"><i class="fa fa-search"></i></span>
                        <input
                            type="text"
                            name="search"
                            class="form-control review-search-input"
                            value="<?= e($search) ?>"
                            placeholder="Search by product, SKU, user, email, title, or comment..."
                        >
                    </div>
                </div>

                <div class="col-lg-3">
                    <select name="status" class="form-select review-select">
                        <option value="">All Status</option>
                        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="col-lg-2 d-grid d-md-flex gap-2">
                    <button type="submit" class="btn btn-primary review-btn flex-fill">Filter</button>
                    <a href="list.php" class="btn btn-light review-btn flex-fill">Reset</a>
                </div>
            </form>
        </div>

        <div class="review-card">
            <div class="review-card-header">
                <h5 class="review-card-title">All Reviews</h5>
                <span class="review-badge-count"><?= count($reviews) ?> Records</span>
            </div>

            <div class="table-responsive">
                <table class="table review-table align-middle">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Product</th>
                            <th>Reviewer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= (int)$review['review_id'] ?></strong>
                                    </td>

                                    <td class="review-product">
                                        <div class="review-product-name">
                                            <?= e($review['product_name'] ?: 'Deleted Product') ?>
                                        </div>
                                        <div class="review-small">
                                            SKU: <?= e($review['sku'] ?: 'N/A') ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="review-user-name">
                                            <?= e($review['user_name'] ?: 'Unknown User') ?>
                                        </div>
                                        <div class="review-small">
                                            <?= e($review['user_email'] ?: 'No email') ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="review-rating"><?= e(renderStars($review['rating'])) ?></div>
                                        <div class="review-small"><?= (int)$review['rating'] ?>/5</div>
                                    </td>

                                    <td>
                                        <?php if (!empty($review['title'])): ?>
                                            <div class="review-user-name mb-1"><?= e($review['title']) ?></div>
                                        <?php endif; ?>
                                        <div class="review-comment">
                                            <?= e($review['comment'] ?: 'No comment provided.') ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="review-status <?= e(reviewStatusClass($review['status'])) ?>">
                                            <?= e($review['status'] ?: 'pending') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="review-date">
                                            <?= !empty($review['created_at']) ? date('d M, Y', strtotime($review['created_at'])) : 'N/A' ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="review-action-group justify-content-center">
                                            <?php if (($review['status'] ?? '') !== 'approved'): ?>
                                                <form method="POST" onsubmit="return confirm('Approve this review?');">
                                                    <input type="hidden" name="action" value="approve_review">
                                                    <input type="hidden" name="review_id" value="<?= (int)$review['review_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (($review['status'] ?? '') !== 'rejected'): ?>
                                                <form method="POST" onsubmit="return confirm('Reject this review?');">
                                                    <input type="hidden" name="action" value="reject_review">
                                                    <input type="hidden" name="review_id" value="<?= (int)$review['review_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Reject</button>
                                                </form>
                                            <?php endif; ?>

                                            <form method="POST" onsubmit="return confirm('Delete this review permanently?');">
                                                <input type="hidden" name="action" value="delete_review">
                                                <input type="hidden" name="review_id" value="<?= (int)$review['review_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="review-empty">No reviews found.</div>
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