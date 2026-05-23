<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function userStatusBadge($isActive)
{
    return ((int)$isActive === 1)
        ? '<span class="badge text-bg-success">Active</span>'
        : '<span class="badge text-bg-danger">Inactive</span>';
}

function verifyBadge($isVerified)
{
    return ((int)$isVerified === 1)
        ? '<span class="badge text-bg-primary">Verified</span>'
        : '<span class="badge bg-light text-dark border">Not Verified</span>';
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$verify = trim($_GET['verify'] ?? '');
$limit = (int)($_GET['limit'] ?? 20);

if (!in_array($limit, [10, 20, 50, 100], true)) {
    $limit = 20;
}

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR u.pincode LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($status !== '') {
    $where[] = "u.is_active = :is_active";
    $params[':is_active'] = $status;
}

if ($verify !== '') {
    $where[] = "u.is_verified = :is_verified";
    $params[':is_verified'] = $verify;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$statsSql = "
    SELECT
        COUNT(*) AS total_customers,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_customers,
        SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) AS verified_customers,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_customers
    FROM users
    WHERE role = 'customer' OR role IS NULL OR role = ''
";
$stats = $conn->query($statsSql)->fetch(PDO::FETCH_ASSOC);

$sql = "
    SELECT
        u.*,
        COUNT(o.id) AS total_orders,
        COALESCE(SUM(o.grand_total), 0) AS total_spent,
        MAX(o.created_at) AS last_order_date
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $whereSql
    " . (empty($where) ? "WHERE" : "AND") . " (u.role = 'customer' OR u.role IS NULL OR u.role = '')
    GROUP BY u.id
    ORDER BY u.id DESC
    LIMIT :limit_rows
";

$stmt = $conn->prepare($sql);

foreach ($params as $key => $val) {
    if ($key === ':is_active' || $key === ':is_verified') {
        $stmt->bindValue($key, (int)$val, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
}

$stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

<style>
.customers-page {
    background: #f8fafc;
    min-height: 100vh;
    overflow-x: hidden;
}

.customers-page .page-title {
    font-weight: 800;
    font-size: 1.75rem;
    line-height: 1.2;
    color: #0f172a;
    margin-bottom: 4px;
}

.customers-page .page-subtitle {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 0;
}

.customers-page .stats-card,
.customers-page .filter-card,
.customers-page .table-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(2, 6, 23, 0.06);
    overflow: hidden;
    background: #fff;
}

.customers-page .stats-card .card-body,
.customers-page .filter-card .card-body,
.customers-page .table-card .card-body {
    padding: 18px;
}

/* Stats card content layout */
.customers-page .stats-card .card-body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 120px;
}

.customers-page .stats-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 6px;
    font-weight: 700;
}

.customers-page .stats-value {
    font-size: 1.8rem;
    line-height: 1.1;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
}

.customers-page .stats-note {
    font-size: 0.88rem;
    color: #94a3b8;
}

/* Gradients for the stats cards */
.customers-page .gradient-card-1 {
    background: linear-gradient(135deg, #ffffff 0%, #eef6ff 100%);
}

.customers-page .gradient-card-2 {
    background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
}

.customers-page .gradient-card-3 {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
}

.customers-page .gradient-card-4 {
    background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
}

/* Form controls */
.customers-page .form-label {
    font-weight: 600;
    color: #334155;
    font-size: 0.88rem;
    margin-bottom: 6px;
}

.customers-page .form-control,
.customers-page .form-select {
    min-height: 42px;
    padding: 0.4rem 0.8rem;
    border-radius: 10px;
    border: 1px solid #dbe2ea;
    box-shadow: none;
    background: #fff;
}

.customers-page .form-control:focus,
.customers-page .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
}

/* Buttons */
.customers-page .btn {
    border-radius: 10px;
    min-height: 40px;
    font-weight: 600;
    font-size: 0.88rem;
    white-space: nowrap;
}

.customers-page .btn-sm {
    min-height: 32px;
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}

/* Table header area */
.customers-page .table-card-header {
    padding: 18px;
}

.customers-page .table-title {
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
    font-size: 1.1rem;
}

.customers-page .table-note {
    color: #64748b;
    font-size: 0.88rem;
}

/* Scrollable table area */
.customers-page .table-scroll-area {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    max-height: 68vh;
    border-top: 1px solid #eef2f7;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #e2e8f0;
}

.customers-page .table-scroll-area::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.customers-page .table-scroll-area::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 16px;
}

.customers-page .table-scroll-area::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 16px;
}

.customers-page .table-scroll-area::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Table itself — no fixed min‑width, more compact */
.customers-page .table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: auto; /* important for mobile */
    table-layout: auto; /* let browser auto‑adjust */
}

.customers-page .table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #0f172a !important;
    color: #fff !important;
    border: none !important;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
    padding: 12px 10px;
    text-align: left;
}

.customers-page .table tbody td {
    vertical-align: middle;
    padding: 12px 10px;
    border-top: 1px solid #eef2f7;
    border-right: none;
    border-left: none;
    background: #fff;
    word-break: break-word;
    max-width: 200px;
}

.customers-page .table tbody tr:hover td {
    background: #f8fbff;
}

/* Text styles */
.customers-page .customer-name {
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
}

.customers-page .sub-text {
    color: #64748b;
    font-size: 0.84rem;
    line-height: 1.45;
    white-space: normal;
}

.customers-page .mini-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    color: #334155;
    font-size: 0.78rem;
    padding: 4px 8px;
    border-radius: 999px;
    font-weight: 600;
    white-space: nowrap;
}

.customers-page .address-box {
    min-width: 200px;
    max-width: 260px;
}

.customers-page .money-text {
    font-weight: 800;
    color: #111827;
}

/* Responsive behavior */

@media (max-width: 991.98px) {
    .customers-page .page-title {
        font-size: 1.6rem;
    }

    .customers-page .stats-value {
        font-size: 1.5rem;
    }

    .customers-page .table-scroll-area {
        max-height: 60vh;
    }

    .customers-page .table {
        font-size: 0.88rem;
    }
}

@media (max-width: 767.98px) {
    .customers-page .container-fluid {
        padding-left: 12px;
        padding-right: 12px;
    }

    .customers-page .page-title {
        font-size: 1.4rem;
    }

    .customers-page .stats-card .card-body,
    .customers-page .filter-card .card-body,
    .customers-page .table-card .card-body {
        padding: 14px;
    }

    .customers-page .table-card-header {
        padding: 14px 14px 8px;
    }

    .customers-page .table-card {
        font-size: 0.84rem;
    }

    .customers-page .table-scroll-area {
        max-height: 58vh;
    }

    .customers-page .mini-chip {
        font-size: 0.76rem;
        padding: 3px 6px;
    }

    .customers-page .money-text {
        font-size: 0.92rem;
    }
}
</style>

    <div class="container-fluid mt-4 mb-5 customers-page">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="page-title">👤 Customer Management</h4>
                <div class="page-subtitle">View customer details, orders, spending, verification status, and activity.</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="card stats-card gradient-card-1 h-100">
                            <div class="card-body">
                                <div class="stats-label">Total Customers</div>
                                <div class="stats-value"><?= (int)($stats['total_customers'] ?? 0) ?></div>
                                <div class="stats-note">All customer accounts</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="card stats-card gradient-card-2 h-100">
                            <div class="card-body">
                                <div class="stats-label">Active Customers</div>
                                <div class="stats-value"><?= (int)($stats['active_customers'] ?? 0) ?></div>
                                <div class="stats-note">Currently active users</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="card stats-card gradient-card-3 h-100">
                            <div class="card-body">
                                <div class="stats-label">Verified Customers</div>
                                <div class="stats-value"><?= (int)($stats['verified_customers'] ?? 0) ?></div>
                                <div class="stats-note">Email verified customers</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="card stats-card gradient-card-4 h-100">
                            <div class="card-body">
                                <div class="stats-label">Today Joined</div>
                                <div class="stats-value"><?= (int)($stats['today_customers'] ?? 0) ?></div>
                                <div class="stats-note">New customers added today</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Name, email, phone, pincode">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label">Verification</label>
                            <select name="verify" class="form-select">
                                <option value="">All</option>
                                <option value="1" <?= $verify === '1' ? 'selected' : '' ?>>Verified</option>
                                <option value="0" <?= $verify === '0' ? 'selected' : '' ?>>Not Verified</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label">Show</label>
                            <select name="limit" class="form-select">
                                <option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10</option>
                                <option value="20" <?= $limit === 20 ? 'selected' : '' ?>>20</option>
                                <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-10">
                            <div class="d-grid d-sm-flex gap-2">
                                <button type="submit" class="btn btn-dark px-4">Apply Filter</button>
                                <a href="list.php" class="btn btn-outline-secondary px-4">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="table-title">Customers List</h6>
                        <div class="table-note">Showing <?= count($customers) ?> customer(s) based on selected filters.</div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-3">
                <div class="table-scroll-area">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Pincode</th>
                                <th>Total Orders</th>
                                <th>Total Spent</th>
                                <th>Status</th>
                                <th>Verify</th>
                                <th>Joined</th>
                                <th>Last Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($customers)): ?>
                                <?php foreach ($customers as $row): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['id'] ?></strong></td>

                                        <td>
                                            <div class="customer-name"><?= e($row['name'] ?: 'N/A') ?></div>
                                            <div class="sub-text"><?= e($row['email'] ?: 'N/A') ?></div>
                                        </td>

                                        <td><?= e($row['phone'] ?: 'N/A') ?></td>

                                        <td class="address-box">
                                            <div class="sub-text"><?= !empty($row['address']) ? nl2br(e($row['address'])) : 'N/A' ?></div>
                                        </td>

                                        <td><?= e($row['pincode'] ?: 'N/A') ?></td>

                                        <td>
                                            <span class="mini-chip"><?= (int)$row['total_orders'] ?> Orders</span>
                                        </td>

                                        <td>
                                            <span class="money-text">₹<?= number_format((float)$row['total_spent'], 2) ?></span>
                                        </td>

                                        <td><?= userStatusBadge($row['is_active']) ?></td>
                                        <td><?= verifyBadge($row['is_verified']) ?></td>

                                        <td>
                                            <?= !empty($row['created_at']) ? e(date('d M Y', strtotime($row['created_at']))) : 'N/A' ?>
                                        </td>

                                        <td>
                                            <?= !empty($row['last_order_date']) ? e(date('d M Y h:i A', strtotime($row['last_order_date']))) : 'No order yet' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">No customers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>