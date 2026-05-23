<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$success = '';

$search       = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$methodFilter = trim($_GET['method'] ?? '');
$dateFilter   = trim($_GET['date'] ?? '');

$validStatuses = ['pending', 'under_review', 'approved', 'rejected'];
$validMethods  = ['document', 'self_declaration', 'facial_estimation', 'manual_review', 'dob', 'checkbox', 'yes_no'];

function formatStatus($status) {
    return ucwords(str_replace('_', ' ', (string)$status));
}

function formatMethod($method) {
    return ucwords(str_replace('_', ' ', (string)$method));
}

function statusBadgeClass($status) {
    return match ((string)$status) {
        'approved'     => 'status-approved',
        'pending'      => 'status-pending',
        'rejected'     => 'status-rejected',
        'under_review' => 'status-review',
        default        => 'status-default'
    };
}

function getUploadPath($file) {
    $file = trim((string)$file);
    return $file !== '' ? '../uploads/' . ltrim($file, '/') : '';
}

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'update_age_verification') {
        $verifyId        = (int)($_POST['verify_id'] ?? 0);
        $reviewStatus    = trim($_POST['review_status'] ?? '');
        $reviewNotes     = trim($_POST['review_notes'] ?? '');
        $verifiedAge     = trim($_POST['verified_age'] ?? '');
        $confidenceScore = trim($_POST['confidence_score'] ?? '');

        if ($verifyId <= 0) {
            $errors[] = 'Invalid verification record selected.';
        }

        if (!in_array($reviewStatus, $validStatuses, true)) {
            $errors[] = 'Invalid review status selected.';
        }

        if ($verifiedAge !== '' && (!is_numeric($verifiedAge) || (int)$verifiedAge < 0 || (int)$verifiedAge > 120)) {
            $errors[] = 'Verified age must be between 0 and 120.';
        }

        if ($confidenceScore !== '' && (!is_numeric($confidenceScore) || (float)$confidenceScore < 0 || (float)$confidenceScore > 100)) {
            $errors[] = 'Confidence score must be between 0 and 100.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("
                UPDATE age_verifications
                SET
                    status = ?,
                    review_notes = ?,
                    verified_age = ?,
                    confidence_score = ?,
                    reviewed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $reviewStatus,
                $reviewNotes,
                $verifiedAge !== '' ? (int)$verifiedAge : null,
                $confidenceScore !== '' ? (float)$confidenceScore : null,
                $verifyId
            ]);

            $success = 'Age verification updated successfully.';
        }
    }

    if ($action === 'delete_age_verification') {
        $verifyId = (int)($_POST['verify_id'] ?? 0);

        if ($verifyId <= 0) {
            $errors[] = 'Invalid verification record selected.';
        } else {
            $stmt = $conn->prepare("DELETE FROM age_verifications WHERE id = ?");
            $stmt->execute([$verifyId]);
            $success = 'Age verification record deleted successfully.';
        }
    }

    if ($action === 'bulk_update_status') {
        $bulkIds    = $_POST['bulk_ids'] ?? [];
        $bulkStatus = trim($_POST['bulk_status'] ?? '');

        if (empty($bulkIds) || !is_array($bulkIds)) {
            $errors[] = 'Please select at least one record.';
        }

        if (!in_array($bulkStatus, $validStatuses, true)) {
            $errors[] = 'Please select a valid bulk status.';
        }

        if (empty($errors)) {
            $cleanIds = array_values(array_filter(array_map('intval', $bulkIds)));

            if (!empty($cleanIds)) {
                $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
                $params = array_merge([$bulkStatus], $cleanIds);

                $stmt = $conn->prepare("
                    UPDATE age_verifications
                    SET status = ?, reviewed_at = NOW()
                    WHERE id IN ($placeholders)
                ");
                $stmt->execute($params);

                $success = 'Selected records updated successfully.';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Filters + Listing
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        av.id,
        av.user_id,
        av.order_id,
        av.full_name,
        av.email,
        av.phone,
        av.date_of_birth,
        av.age_threshold,
        av.verified_age,
        av.method,
        av.document_type,
        av.document_number,
        av.document_front,
        av.document_back,
        av.selfie_image,
        av.status,
        av.confidence_score,
        av.review_notes,
        av.created_at,
        av.reviewed_at,
        u.name AS user_name,
        u.email AS user_email
    FROM age_verifications av
    LEFT JOIN users u ON u.id = av.user_id
    WHERE 1
";

$params = [];

if ($search !== '') {
    $sql .= " AND (
        av.full_name LIKE ?
        OR av.email LIKE ?
        OR av.phone LIKE ?
        OR av.document_number LIKE ?
        OR av.order_id LIKE ?
        OR u.name LIKE ?
        OR u.email LIKE ?
    )";
    $like = "%{$search}%";
    array_push($params, $like, $like, $like, $like, $like, $like, $like);
}

if ($statusFilter !== '' && in_array($statusFilter, $validStatuses, true)) {
    $sql .= " AND av.status = ?";
    $params[] = $statusFilter;
}

if ($methodFilter !== '' && in_array($methodFilter, $validMethods, true)) {
    $sql .= " AND av.method = ?";
    $params[] = $methodFilter;
}

if ($dateFilter !== '') {
    $sql .= " AND DATE(av.created_at) = ?";
    $params[] = $dateFilter;
}

$sql .= " ORDER BY av.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/
$summaryStmt = $conn->query("
    SELECT
        COUNT(*) AS total_rows,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
        SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) AS total_under_review,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS total_approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS total_rejected
    FROM age_verifications
");
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
?>

<style>
    :root{
        --av-primary:#0d6efd;
        --av-primary-hover:#0b5ed7;
        --av-success:#198754;
        --av-success-soft:rgba(25,135,84,.10);
        --av-warning:#fd7e14;
        --av-warning-soft:rgba(253,126,20,.12);
        --av-danger:#dc3545;
        --av-danger-soft:rgba(220,53,69,.12);
        --av-info:#0dcaf0;
        --av-info-soft:rgba(13,202,240,.12);
        --av-indigo:#4f46e5;
        --av-indigo-soft:rgba(79,70,229,.10);
        --av-bg:#f5f7fb;
        --av-card:#ffffff;
        --av-text:#1f2937;
        --av-muted:#6b7280;
        --av-border:#e9ecef;
        --av-head:#f8fafc;
        --av-shadow:0 10px 28px rgba(15,23,42,.06);
        --av-radius:16px;
    }

    body{
        background:var(--av-bg);
    }

    .age-page{
        padding:24px;
    }

    .age-header{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        flex-wrap:wrap;
        margin-bottom:24px;
    }

    .age-title{
        margin:0 0 6px;
        font-size:26px;
        font-weight:800;
        color:var(--av-text);
    }

    .age-subtitle{
        margin:0;
        font-size:14px;
        color:var(--av-muted);
    }

    .age-toolbar,
    .age-card,
    .age-stat-card{
        background:var(--av-card);
        border:1px solid var(--av-border);
        border-radius:var(--av-radius);
        box-shadow:var(--av-shadow);
    }

    .age-toolbar{
        padding:18px;
        margin-bottom:20px;
    }

    .age-search-wrap{
        position:relative;
    }

    .age-search-icon{
        position:absolute;
        left:14px;
        top:50%;
        transform:translateY(-50%);
        color:#98a2b3;
        font-size:14px;
        pointer-events:none;
    }

    .age-input,
    .age-select{
        min-height:46px;
        border-radius:12px;
        border:1px solid var(--av-border);
        font-size:14px;
        box-shadow:none;
    }

    .age-input{
        padding-left:42px;
    }

    .age-input:focus,
    .age-select:focus,
    .form-control:focus,
    .form-select:focus{
        border-color:var(--av-primary);
        box-shadow:0 0 0 4px rgba(13,110,253,.10);
    }

    .age-btn{
        min-height:46px;
        border-radius:12px;
        font-weight:700;
        padding:10px 16px;
    }

    .age-btn-primary{
        background:var(--av-primary);
        border:1px solid var(--av-primary);
        color:#fff;
    }

    .age-btn-primary:hover{
        background:var(--av-primary-hover);
        border-color:var(--av-primary-hover);
        color:#fff;
    }

    .age-btn-light{
        background:#fff;
        border:1px solid var(--av-border);
        color:var(--av-text);
    }

    .age-btn-light:hover{
        background:#f8fafc;
    }

    .age-stats{
        margin-bottom:20px;
    }

    .age-stat-card{
        padding:18px;
        height:100%;
        transition:transform .2s ease, box-shadow .2s ease;
    }

    .age-stat-card:hover{
        transform:translateY(-2px);
    }

    .age-stat-label{
        display:block;
        font-size:13px;
        color:var(--av-muted);
        margin-bottom:8px;
    }

    .age-stat-value{
        margin:0;
        font-size:28px;
        line-height:1;
        font-weight:800;
        color:var(--av-text);
    }

    .age-stat-meta{
        margin-top:10px;
        font-size:12px;
        color:var(--av-muted);
    }

    .age-card-header{
        padding:18px 20px;
        border-bottom:1px solid var(--av-border);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .age-card-title{
        margin:0;
        font-size:16px;
        font-weight:800;
        color:var(--av-text);
    }

    .age-badge-soft{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        background:rgba(13,110,253,.08);
        color:var(--av-primary);
        font-size:12px;
        font-weight:700;
    }

    .bulk-bar{
        padding:14px 18px;
        border-bottom:1px solid var(--av-border);
        background:#fcfcfd;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .bulk-left,
    .bulk-right{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .table.age-table{
        margin:0;
    }

    .age-table thead th{
        background:var(--av-head);
        color:#667085;
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.04em;
        font-weight:800;
        padding:14px 16px;
        border-bottom:1px solid var(--av-border);
        white-space:nowrap;
    }

    .age-table tbody td{
        padding:16px;
        vertical-align:middle;
        border-top:1px solid #f1f3f5;
    }

    .age-table tbody tr{
        transition:background-color .2s ease;
    }

    .age-table tbody tr:hover{
        background:#fbfdff;
    }

    .age-check{
        width:18px;
        height:18px;
    }

    .user-block{
        min-width:230px;
    }

    .user-name{
        font-size:14px;
        font-weight:700;
        color:var(--av-text);
        margin-bottom:4px;
    }

    .user-meta{
        margin:0;
        font-size:12px;
        line-height:1.5;
        color:var(--av-muted);
    }

    .pill{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .method-pill{
        background:var(--av-indigo-soft);
        color:var(--av-indigo);
    }

    .threshold-pill{
        background:#f3f4f6;
        color:#374151;
    }

    .status-pill{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .status-approved{
        background:var(--av-success-soft);
        color:var(--av-success);
    }

    .status-pending{
        background:var(--av-warning-soft);
        color:var(--av-warning);
    }

    .status-rejected{
        background:var(--av-danger-soft);
        color:var(--av-danger);
    }

    .status-review{
        background:var(--av-info-soft);
        color:#087990;
    }

    .status-default{
        background:#f1f5f9;
        color:#475569;
    }

    .confidence{
        font-weight:800;
        color:var(--av-text);
        font-size:14px;
    }

    .date-meta{
        font-size:12px;
        line-height:1.6;
        color:var(--av-muted);
        white-space:nowrap;
    }

    .action-group{
        display:flex;
        justify-content:center;
        gap:8px;
        flex-wrap:wrap;
    }

    .action-btn{
        border-radius:10px;
        font-size:13px;
        font-weight:700;
        padding:7px 12px;
    }

    .age-empty{
        text-align:center;
        padding:50px 24px;
        color:var(--av-muted);
    }

    .age-empty h6{
        margin-bottom:8px;
        font-size:18px;
        font-weight:800;
        color:var(--av-text);
    }

    .modal-content{
        border:none;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 20px 50px rgba(15,23,42,.18);
    }

    .modal-header{
        padding:16px 20px;
        border-bottom:1px solid var(--av-border);
    }

    .modal-title{
        font-weight:800;
        color:var(--av-text);
    }

    .modal-body{
        padding:20px;
    }

    .detail-list{
        display:grid;
        grid-template-columns:repeat(2, minmax(0,1fr));
        gap:14px;
    }

    .detail-item{
        background:#f8fafc;
        border:1px solid var(--av-border);
        border-radius:14px;
        padding:14px;
    }

    .detail-label{
        display:block;
        margin-bottom:6px;
        font-size:12px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.03em;
        color:var(--av-muted);
    }

    .detail-value{
        font-size:14px;
        font-weight:600;
        color:var(--av-text);
        word-break:break-word;
    }

    .doc-preview-wrap{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
        gap:16px;
    }

    .doc-preview{
        background:#fff;
        border:1px solid var(--av-border);
        border-radius:14px;
        padding:12px;
    }

    .doc-preview-title{
        margin-bottom:10px;
        font-size:13px;
        font-weight:700;
        color:var(--av-text);
    }

    .doc-preview img{
        width:100%;
        height:160px;
        object-fit:cover;
        border-radius:10px;
        border:1px solid var(--av-border);
        background:#f8f9fa;
    }

    .alert{
        border:none;
        border-radius:14px;
        box-shadow:var(--av-shadow);
    }

    @media (max-width: 991.98px){
        .age-page{
            padding:16px;
        }

        .detail-list{
            grid-template-columns:1fr;
        }
    }

    @media (max-width: 767.98px){
        .age-title{
            font-size:22px;
        }

        .age-btn,
        .age-input,
        .age-select{
            min-height:44px;
        }

        .age-table thead th,
        .age-table tbody td{
            padding:12px;
        }

        .bulk-bar{
            align-items:flex-start;
        }
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid age-page">
        <div class="age-header">
            <div>
                <h1 class="age-title">Age Verification Management</h1>
                <p class="age-subtitle">Review age checks, inspect submitted proofs, and approve or reject requests from one place.</p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <!-- <button type="button" class="btn age-btn age-btn-light" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> Print
                </button>
                <button type="button" class="btn age-btn age-btn-primary" onclick="alert('Export can be connected to CSV or PDF next.')">
                    <i class="fa fa-download me-1"></i> Export
                </button> -->
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

        <div class="row g-3 age-stats">
            <div class="col-md-6 col-xl-3">
                <div class="age-stat-card">
                    <span class="age-stat-label">Total Requests</span>
                    <h4 class="age-stat-value"><?= (int)($summary['total_rows'] ?? 0) ?></h4>
                    <div class="age-stat-meta">All verification submissions</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="age-stat-card">
                    <span class="age-stat-label">Pending</span>
                    <h4 class="age-stat-value"><?= (int)($summary['total_pending'] ?? 0) ?></h4>
                    <div class="age-stat-meta">Waiting for admin review</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="age-stat-card">
                    <span class="age-stat-label">Approved</span>
                    <h4 class="age-stat-value"><?= (int)($summary['total_approved'] ?? 0) ?></h4>
                    <div class="age-stat-meta">Passed verification</div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="age-stat-card">
                    <span class="age-stat-label">Rejected</span>
                    <h4 class="age-stat-value"><?= (int)($summary['total_rejected'] ?? 0) ?></h4>
                    <div class="age-stat-meta">Failed or underage</div>
                </div>
            </div>
        </div>

        <div class="age-toolbar">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-lg-4">
                    <div class="age-search-wrap">
                        <span class="age-search-icon"><i class="fa fa-search"></i></span>
                        <input
                            type="text"
                            name="search"
                            class="form-control age-input"
                            value="<?= e($search) ?>"
                            placeholder="Search name, email, phone, order ID, document no..."
                        >
                    </div>
                </div>

                <div class="col-lg-2">
                    <select name="status" class="form-select age-select">
                        <option value="">All Status</option>
                        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="under_review" <?= $statusFilter === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                        <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <select name="method" class="form-select age-select">
                        <option value="">All Methods</option>
                        <option value="document" <?= $methodFilter === 'document' ? 'selected' : '' ?>>Document</option>
                        <option value="self_declaration" <?= $methodFilter === 'self_declaration' ? 'selected' : '' ?>>Self Declaration</option>
                        <option value="facial_estimation" <?= $methodFilter === 'facial_estimation' ? 'selected' : '' ?>>Facial Estimation</option>
                        <option value="manual_review" <?= $methodFilter === 'manual_review' ? 'selected' : '' ?>>Manual Review</option>
                        <option value="dob" <?= $methodFilter === 'dob' ? 'selected' : '' ?>>DOB</option>
                        <option value="checkbox" <?= $methodFilter === 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                        <option value="yes_no" <?= $methodFilter === 'yes_no' ? 'selected' : '' ?>>Yes / No</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <input type="date" name="date" class="form-control age-select" value="<?= e($dateFilter) ?>">
                </div>

                <div class="col-lg-2 d-grid d-md-flex gap-2">
                    <button type="submit" class="btn age-btn age-btn-primary flex-fill">Search</button>
                    <a href="index.php" class="btn age-btn age-btn-light flex-fill">Reset</a>
                </div>
            </form>
        </div>

        <div class="age-card">
            <div class="age-card-header">
                <h5 class="age-card-title">Verification Requests</h5>
                <span class="age-badge-soft"><?= count($verifications) ?> Records</span>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="bulk_update_status">

                <div class="bulk-bar">
                    <div class="bulk-left">
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" id="checkAll" class="age-check">
                            <span class="fw-semibold text-dark">Select All</span>
                        </label>
                        <span class="text-muted small">Apply one status to multiple selected records.</span>
                    </div>

                    <div class="bulk-right">
                        <select name="bulk_status" class="form-select age-select" style="min-width:180px;">
                            <option value="">Choose status</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <button type="submit" class="btn age-btn age-btn-primary">Apply</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table age-table align-middle">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>#ID</th>
                                <th>User Info</th>
                                <th>Method</th>
                                <th>Threshold</th>
                                <th>Status</th>
                                <th>Confidence</th>
                                <th>Submitted</th>
                                <th>Reviewed</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($verifications)): ?>
                                <?php foreach ($verifications as $row): ?>
                                    <?php
                                        $frontImg  = getUploadPath($row['document_front'] ?? '');
                                        $backImg   = getUploadPath($row['document_back'] ?? '');
                                        $selfieImg = getUploadPath($row['selfie_image'] ?? '');
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="bulk_ids[]" value="<?= (int)$row['id'] ?>" class="record-checkbox age-check">
                                        </td>

                                        <td>
                                            <strong>#<?= (int)$row['id'] ?></strong><br>
                                            <small class="text-muted">Order: <?= e($row['order_id'] ?: 'N/A') ?></small>
                                        </td>

                                        <td>
                                            <div class="user-block">
                                                <div class="user-name"><?= e($row['full_name'] ?: $row['user_name'] ?: 'N/A') ?></div>
                                                <p class="user-meta"><?= e($row['email'] ?: $row['user_email'] ?: 'No email') ?></p>
                                                <p class="user-meta"><?= e($row['phone'] ?: 'No phone') ?></p>
                                                <p class="user-meta">DOB: <?= e($row['date_of_birth'] ?: 'N/A') ?></p>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="pill method-pill"><?= e(formatMethod($row['method'])) ?></span><br>
                                            <small class="text-muted"><?= e($row['document_type'] ?: 'No document type') ?></small>
                                        </td>

                                        <td>
                                            <span class="pill threshold-pill"><?= (int)($row['age_threshold'] ?? 18) ?>+</span><br>
                                            <small class="text-muted">Verified age: <?= e($row['verified_age'] !== null && $row['verified_age'] !== '' ? $row['verified_age'] : 'N/A') ?></small>
                                        </td>

                                        <td>
                                            <span class="status-pill <?= e(statusBadgeClass($row['status'])) ?>">
                                                <?= e(formatStatus($row['status'])) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="confidence">
                                                <?= $row['confidence_score'] !== null && $row['confidence_score'] !== '' ? e(number_format((float)$row['confidence_score'], 1)) . '%' : 'N/A' ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="date-meta">
                                                <?= !empty($row['created_at']) ? date('d M, Y', strtotime($row['created_at'])) : 'N/A' ?><br>
                                                <?= !empty($row['created_at']) ? date('h:i A', strtotime($row['created_at'])) : '' ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="date-meta">
                                                <?= !empty($row['reviewed_at']) ? date('d M, Y', strtotime($row['reviewed_at'])) : 'Not reviewed' ?><br>
                                                <?= !empty($row['reviewed_at']) ? date('h:i A', strtotime($row['reviewed_at'])) : '' ?>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <div class="action-group">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary action-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal<?= (int)$row['id'] ?>"
                                                >
                                                    <i class="fa fa-eye me-1"></i> View
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success action-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#reviewModal<?= (int)$row['id'] ?>"
                                                >
                                                    <i class="fa fa-check-circle me-1"></i> Review
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?= (int)$row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Verification Details #<?= (int)$row['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="detail-list mb-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Full Name</span>
                                                            <div class="detail-value"><?= e($row['full_name'] ?: $row['user_name'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Email</span>
                                                            <div class="detail-value"><?= e($row['email'] ?: $row['user_email'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Phone</span>
                                                            <div class="detail-value"><?= e($row['phone'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Date of Birth</span>
                                                            <div class="detail-value"><?= e($row['date_of_birth'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Method</span>
                                                            <div class="detail-value"><?= e(formatMethod($row['method'])) ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Status</span>
                                                            <div class="detail-value"><?= e(formatStatus($row['status'])) ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Age Threshold</span>
                                                            <div class="detail-value"><?= (int)($row['age_threshold'] ?? 18) ?>+</div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Verified Age</span>
                                                            <div class="detail-value"><?= e($row['verified_age'] !== null && $row['verified_age'] !== '' ? $row['verified_age'] : 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Confidence Score</span>
                                                            <div class="detail-value"><?= $row['confidence_score'] !== null && $row['confidence_score'] !== '' ? e(number_format((float)$row['confidence_score'], 1)) . '%' : 'N/A' ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Document Type</span>
                                                            <div class="detail-value"><?= e($row['document_type'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Document Number</span>
                                                            <div class="detail-value"><?= e($row['document_number'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Order ID</span>
                                                            <div class="detail-value"><?= e($row['order_id'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Submitted At</span>
                                                            <div class="detail-value"><?= !empty($row['created_at']) ? e(date('d M, Y h:i A', strtotime($row['created_at']))) : 'N/A' ?></div>
                                                        </div>

                                                        <div class="detail-item">
                                                            <span class="detail-label">Reviewed At</span>
                                                            <div class="detail-value"><?= !empty($row['reviewed_at']) ? e(date('d M, Y h:i A', strtotime($row['reviewed_at']))) : 'N/A' ?></div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-3">Review Notes</h6>
                                                        <div class="detail-item">
                                                            <div class="detail-value"><?= nl2br(e($row['review_notes'] ?: 'No review notes available.')) ?></div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-bold mb-3">Uploaded Proofs</h6>
                                                        <div class="doc-preview-wrap">
                                                            <div class="doc-preview">
                                                                <div class="doc-preview-title">Document Front</div>
                                                                <?php if ($frontImg): ?>
                                                                    <a href="<?= e($frontImg) ?>" target="_blank" rel="noopener noreferrer">
                                                                        <img src="<?= e($frontImg) ?>" alt="Document Front">
                                                                    </a>
                                                                <?php else: ?>
                                                                    <div class="text-muted small">No front image uploaded.</div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="doc-preview">
                                                                <div class="doc-preview-title">Document Back</div>
                                                                <?php if ($backImg): ?>
                                                                    <a href="<?= e($backImg) ?>" target="_blank" rel="noopener noreferrer">
                                                                        <img src="<?= e($backImg) ?>" alt="Document Back">
                                                                    </a>
                                                                <?php else: ?>
                                                                    <div class="text-muted small">No back image uploaded.</div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="doc-preview">
                                                                <div class="doc-preview-title">Selfie / Face Image</div>
                                                                <?php if ($selfieImg): ?>
                                                                    <a href="<?= e($selfieImg) ?>" target="_blank" rel="noopener noreferrer">
                                                                        <img src="<?= e($selfieImg) ?>" alt="Selfie Image">
                                                                    </a>
                                                                <?php else: ?>
                                                                    <div class="text-muted small">No selfie image uploaded.</div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Review Modal -->
                                    <div class="modal fade" id="reviewModal<?= (int)$row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update_age_verification">
                                                    <input type="hidden" name="verify_id" value="<?= (int)$row['id'] ?>">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Review Verification #<?= (int)$row['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Customer Name</label>
                                                                <input type="text" class="form-control" value="<?= e($row['full_name'] ?: $row['user_name'] ?: 'N/A') ?>" readonly>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Verification Method</label>
                                                                <input type="text" class="form-control" value="<?= e(formatMethod($row['method'])) ?>" readonly>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label fw-semibold">Review Status</label>
                                                                <select name="review_status" class="form-select" required>
                                                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                                    <option value="under_review" <?= $row['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                                                                    <option value="approved" <?= $row['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                                                    <option value="rejected" <?= $row['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <label class="form-label fw-semibold">Verified Age</label>
                                                                <input
                                                                    type="number"
                                                                    min="0"
                                                                    max="120"
                                                                    name="verified_age"
                                                                    class="form-control"
                                                                    value="<?= e($row['verified_age']) ?>"
                                                                    placeholder="18"
                                                                >
                                                            </div>

                                                            <div class="col-md-3">
                                                                <label class="form-label fw-semibold">Confidence %</label>
                                                                <input
                                                                    type="number"
                                                                    min="0"
                                                                    max="100"
                                                                    step="0.1"
                                                                    name="confidence_score"
                                                                    class="form-control"
                                                                    value="<?= e($row['confidence_score']) ?>"
                                                                    placeholder="96.5"
                                                                >
                                                            </div>

                                                            <div class="col-12">
                                                                <label class="form-label fw-semibold">Review Notes</label>
                                                                <textarea
                                                                    name="review_notes"
                                                                    rows="5"
                                                                    class="form-control"
                                                                    placeholder="Write admin notes, rejection reason, mismatch details, or manual review remarks..."
                                                                ><?= e($row['review_notes']) ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-danger delete-record-btn"
                                                            data-form-id="deleteForm<?= (int)$row['id'] ?>"
                                                        >
                                                            Delete
                                                        </button>

                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Review</button>
                                                        </div>
                                                    </div>
                                                </form>

                                                <form method="POST" id="deleteForm<?= (int)$row['id'] ?>" class="d-none">
                                                    <input type="hidden" name="action" value="delete_age_verification">
                                                    <input type="hidden" name="verify_id" value="<?= (int)$row['id'] ?>">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">
                                        <div class="age-empty">
                                            <h6>No verification records found</h6>
                                            <p class="mb-0">Try changing your search or filters to find matching records.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.record-checkbox');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const total = checkboxes.length;
            const checked = document.querySelectorAll('.record-checkbox:checked').length;
            if (checkAll) {
                checkAll.checked = total > 0 && checked === total;
            }
        });
    });

    document.querySelectorAll('.delete-record-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const formId = this.getAttribute('data-form-id');
            const form = document.getElementById(formId);

            if (form && confirm('Delete this verification record permanently?')) {
                form.submit();
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>