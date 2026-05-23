<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    :root{
        --po-primary:#0d6efd;
        --po-primary-soft:rgba(13,110,253,.08);
        --po-success:#198754;
        --po-success-soft:rgba(25,135,84,.10);
        --po-warning:#f59e0b;
        --po-warning-soft:rgba(245,158,11,.12);
        --po-danger:#dc3545;
        --po-danger-soft:rgba(220,53,69,.10);
        --po-info:#0ea5e9;
        --po-info-soft:rgba(14,165,233,.10);
        --po-dark:#111827;
        --po-text:#1f2937;
        --po-muted:#6b7280;
        --po-border:#e5e7eb;
        --po-bg:#f4f7fb;
        --po-card:#ffffff;
        --po-shadow:0 10px 30px rgba(15,23,42,.06);
        --po-shadow-lg:0 20px 40px rgba(15,23,42,.12);
        --po-radius:18px;
    }

    body{
        background:var(--po-bg);
    }

    .policies-page{
        padding:24px;
    }

    .policies-heading{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:20px;
    }

    .policies-title{
        font-size:26px;
        font-weight:800;
        color:var(--po-text);
        margin-bottom:4px;
    }

    .policies-subtitle{
        font-size:14px;
        color:var(--po-muted);
        margin:0;
    }

    .policies-card,
    .policies-filter-card,
    .policy-stat-card{
        background:var(--po-card);
        border:1px solid var(--po-border);
        border-radius:var(--po-radius);
        box-shadow:var(--po-shadow);
    }

    .policy-stat-card{
        padding:18px;
        height:100%;
        transition:transform .2s ease;
    }

    .policy-stat-card:hover{
        transform:translateY(-2px);
    }

    .policy-stat-label{
        display:block;
        font-size:13px;
        color:var(--po-muted);
        margin-bottom:8px;
    }

    .policy-stat-value{
        margin:0;
        font-size:26px;
        line-height:1;
        font-weight:800;
        color:var(--po-text);
    }

    .policies-filter-card{
        padding:18px;
        margin-bottom:20px;
    }

    .form-label{
        font-size:13px;
        font-weight:700;
        color:var(--po-text);
        margin-bottom:8px;
    }

    .form-control,
    .form-select{
        min-height:46px;
        border-radius:12px;
        border:1px solid var(--po-border);
        box-shadow:none;
        font-size:14px;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:var(--po-primary);
        box-shadow:0 0 0 4px rgba(13,110,253,.10);
    }

    .policies-search-wrap{
        position:relative;
    }

    .policies-search-icon{
        position:absolute;
        top:50%;
        left:14px;
        transform:translateY(-50%);
        color:#9ca3af;
        font-size:14px;
        pointer-events:none;
    }

    .policies-search-input{
        padding-left:42px;
    }

    .btn-policy{
        min-height:46px;
        border-radius:12px;
        font-weight:700;
        padding:0 16px;
    }

    .policies-card-header{
        padding:18px 20px;
        border-bottom:1px solid var(--po-border);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .policies-card-title{
        margin:0;
        font-size:17px;
        font-weight:800;
        color:var(--po-text);
    }

    .policies-count-badge{
        display:inline-flex;
        align-items:center;
        padding:7px 10px;
        border-radius:999px;
        background:var(--po-primary-soft);
        color:var(--po-primary);
        font-size:12px;
        font-weight:700;
    }

    .table.policies-table{
        margin:0;
    }

    .policies-table thead th{
        background:#f8fafc;
        color:#667085;
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.04em;
        font-weight:800;
        padding:14px 16px;
        border-bottom:1px solid var(--po-border);
        white-space:nowrap;
    }

    .policies-table tbody td{
        padding:16px;
        vertical-align:middle;
        border-top:1px solid #f1f5f9;
    }

    .policies-table tbody tr{
        transition:background-color .2s ease;
    }

    .policies-table tbody tr:hover{
        background:#fbfdff;
    }

    .policy-title-cell{
        min-width:280px;
    }

    .policy-main-title{
        font-size:15px;
        font-weight:800;
        color:var(--po-text);
        margin-bottom:4px;
    }

    .policy-meta{
        font-size:12px;
        color:var(--po-muted);
        margin:0;
        line-height:1.5;
    }

    .policy-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .badge-type{ background:#eef2ff; color:#4338ca; }
    .badge-published{ background:var(--po-success-soft); color:var(--po-success); }
    .badge-draft{ background:var(--po-warning-soft); color:#b45309; }
    .badge-archived{ background:#e5e7eb; color:#4b5563; }
    .badge-public{ background:#ecfeff; color:#0f766e; }
    .badge-private{ background:#fdf2f8; color:#be185d; }
    .badge-featured{ background:#fff7ed; color:#c2410c; }
    .badge-order{ background:#f3f4f6; color:#374151; }

    .policy-actions{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        min-width:280px;
    }

    .action-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        border:none;
        border-radius:12px;
        font-size:13px;
        font-weight:700;
        padding:9px 12px;
        text-decoration:none;
        transition:all .2s ease;
        box-shadow:none;
    }

    .action-btn:hover{
        transform:translateY(-1px);
    }

    .action-view{
        background:var(--po-info-soft);
        color:var(--po-info);
    }

    .action-view:hover{
        background:rgba(14,165,233,.16);
        color:#0284c7;
    }

    .action-edit{
        background:var(--po-primary-soft);
        color:var(--po-primary);
    }

    .action-edit:hover{
        background:rgba(13,110,253,.14);
        color:#0b5ed7;
    }

    .action-status{
        background:var(--po-success-soft);
        color:var(--po-success);
    }

    .action-status:hover{
        background:rgba(25,135,84,.16);
        color:#157347;
    }

    .action-feature{
        background:var(--po-warning-soft);
        color:#b45309;
    }

    .action-feature:hover{
        background:rgba(245,158,11,.18);
        color:#92400e;
    }

    .action-visibility{
        background:#eef2ff;
        color:#4f46e5;
    }

    .action-visibility:hover{
        background:#e0e7ff;
        color:#4338ca;
    }

    .action-delete{
        background:var(--po-danger-soft);
        color:var(--po-danger);
    }

    .action-delete:hover{
        background:rgba(220,53,69,.16);
        color:#bb2d3b;
    }

    .action-btn i{
        font-size:12px;
    }

    .policies-empty{
        text-align:center;
        padding:42px 20px;
        color:var(--po-muted);
    }

    .alert{
        border:none;
        border-radius:14px;
        box-shadow:var(--po-shadow);
    }

    .modal-content.policy-modal{
        border:none;
        border-radius:22px;
        overflow:hidden;
        box-shadow:var(--po-shadow-lg);
    }

    .policy-modal .modal-header{
        border-bottom:1px solid #eef2f7;
        padding:20px 22px 14px;
        background:linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
    }

    .policy-modal .modal-title{
        font-size:18px;
        font-weight:800;
        color:var(--po-text);
        display:flex;
        align-items:center;
        gap:10px;
    }

    .policy-modal-icon{
        width:40px;
        height:40px;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:16px;
    }

    .policy-modal .modal-body{
        padding:20px 22px;
    }

    .policy-modal .modal-footer{
        border-top:1px solid #eef2f7;
        padding:16px 22px 22px;
        background:#fcfdff;
    }

    .policy-modal-text{
        font-size:14px;
        color:var(--po-muted);
        margin-bottom:14px;
        line-height:1.7;
    }

    .policy-modal-meta{
        background:#f8fafc;
        border:1px solid #eef2f7;
        border-radius:16px;
        padding:14px;
    }

    .policy-modal-meta .meta-label{
        display:block;
        font-size:12px;
        font-weight:700;
        color:#94a3b8;
        text-transform:uppercase;
        letter-spacing:.04em;
        margin-bottom:4px;
    }

    .policy-modal-meta .meta-value{
        font-size:14px;
        font-weight:700;
        color:var(--po-text);
        word-break:break-word;
    }

    .modal-btn{
        min-width:130px;
        min-height:44px;
        border-radius:12px;
        font-weight:700;
    }

    .modal-confirm-danger{
        background:var(--po-danger);
        border-color:var(--po-danger);
        color:#fff;
    }

    .modal-confirm-success{
        background:var(--po-success);
        border-color:var(--po-success);
        color:#fff;
    }

    .modal-confirm-warning{
        background:#d97706;
        border-color:#d97706;
        color:#fff;
    }

    .modal-confirm-dark{
        background:#1f2937;
        border-color:#1f2937;
        color:#fff;
    }

    .modal-theme-danger .policy-modal-icon{ background:var(--po-danger-soft); color:var(--po-danger); }
    .modal-theme-success .policy-modal-icon{ background:var(--po-success-soft); color:var(--po-success); }
    .modal-theme-warning .policy-modal-icon{ background:var(--po-warning-soft); color:#b45309; }
    .modal-theme-dark .policy-modal-icon{ background:#eef2ff; color:#4338ca; }

    @media (max-width:991.98px){
        .policies-page{
            padding:16px;
        }
    }

    @media (max-width:767.98px){
        .policies-title{
            font-size:22px;
        }

        .policies-table thead th,
        .policies-table tbody td{
            padding:12px;
        }

        .policy-title-cell{
            min-width:220px;
        }

        .policy-actions{
            min-width:220px;
        }

        .action-btn{
            width:100%;
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
$typeFilter = trim($_GET['type'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$visibilityFilter = trim($_GET['visibility'] ?? '');

function policyTypeLabel($type) {
    $map = [
        'privacy_policy' => 'Privacy Policy',
        'terms_conditions' => 'Terms & Conditions',
        'refund_policy' => 'Refund Policy',
        'shipping_policy' => 'Shipping Policy',
        'cancellation_policy' => 'Cancellation Policy',
        'about_us' => 'About Us',
        'contact_us' => 'Contact Us',
        'faq' => 'FAQ',
        'custom' => 'Custom',
    ];
    return $map[$type] ?? ucfirst(str_replace('_', ' ', (string)$type));
}

function policyStatusBadge($status) {
    switch ($status) {
        case 'published':
            return '<span class="policy-badge badge-published">Published</span>';
        case 'archived':
            return '<span class="policy-badge badge-archived">Archived</span>';
        default:
            return '<span class="policy-badge badge-draft">Draft</span>';
    }
}

function policyVisibilityBadge($visibility) {
    return $visibility === 'private'
        ? '<span class="policy-badge badge-private">Private</span>'
        : '<span class="policy-badge badge-public">Public</span>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $policyId = (int)($_POST['policy_id'] ?? 0);

    if ($policyId <= 0) {
        $errors[] = 'Invalid policy selected.';
    } else {
        try {
            if ($action === 'delete_policy') {
                $stmt = $conn->prepare("DELETE FROM policies WHERE id = ?");
                $stmt->execute([$policyId]);
                $success = 'Policy deleted successfully.';
            } elseif ($action === 'toggle_status') {
                $stmt = $conn->prepare("SELECT status FROM policies WHERE id = ? LIMIT 1");
                $stmt->execute([$policyId]);
                $currentStatus = $stmt->fetchColumn();

                if ($currentStatus !== false) {
                    $newStatus = ($currentStatus === 'published') ? 'draft' : 'published';
                    $update = $conn->prepare("UPDATE policies SET status = ? WHERE id = ?");
                    $update->execute([$newStatus, $policyId]);
                    $success = 'Policy status updated successfully.';
                } else {
                    $errors[] = 'Policy not found.';
                }
            } elseif ($action === 'toggle_featured') {
                $stmt = $conn->prepare("SELECT is_featured FROM policies WHERE id = ? LIMIT 1");
                $stmt->execute([$policyId]);
                $currentFeatured = $stmt->fetchColumn();

                if ($currentFeatured !== false) {
                    $newFeatured = ((int)$currentFeatured === 1) ? 0 : 1;
                    $update = $conn->prepare("UPDATE policies SET is_featured = ? WHERE id = ?");
                    $update->execute([$newFeatured, $policyId]);
                    $success = 'Featured flag updated successfully.';
                } else {
                    $errors[] = 'Policy not found.';
                }
            } elseif ($action === 'toggle_visibility') {
                $stmt = $conn->prepare("SELECT visibility FROM policies WHERE id = ? LIMIT 1");
                $stmt->execute([$policyId]);
                $currentVisibility = $stmt->fetchColumn();

                if ($currentVisibility !== false) {
                    $newVisibility = ($currentVisibility === 'private') ? 'public' : 'private';
                    $update = $conn->prepare("UPDATE policies SET visibility = ? WHERE id = ?");
                    $update->execute([$newVisibility, $policyId]);
                    $success = 'Policy visibility updated successfully.';
                } else {
                    $errors[] = 'Policy not found.';
                }
            }
        } catch (Exception $ex) {
            $errors[] = 'Action failed: ' . $ex->getMessage();
        }
    }
}

$where = " WHERE 1 ";
$params = [];

if ($search !== '') {
    $where .= " AND (title LIKE ? OR slug LIKE ? OR short_description LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($typeFilter !== '') {
    $where .= " AND type = ?";
    $params[] = $typeFilter;
}

if ($statusFilter !== '') {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($visibilityFilter !== '') {
    $where .= " AND visibility = ?";
    $params[] = $visibilityFilter;
}

$policies = [];
try {
    $sql = "
        SELECT 
            id, title, slug, type, short_description, status, visibility, 
            is_featured, display_order, created_at, updated_at
        FROM policies
        {$where}
        ORDER BY display_order ASC, id DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $ex) {
    $errors[] = 'Could not load policies. Make sure the policies table exists.';
}

$summary = [
    'total' => 0,
    'published' => 0,
    'draft' => 0,
    'featured' => 0,
];

try {
    $summary['total'] = (int)$conn->query("SELECT COUNT(*) FROM policies")->fetchColumn();
    $summary['published'] = (int)$conn->query("SELECT COUNT(*) FROM policies WHERE status = 'published'")->fetchColumn();
    $summary['draft'] = (int)$conn->query("SELECT COUNT(*) FROM policies WHERE status = 'draft'")->fetchColumn();
    $summary['featured'] = (int)$conn->query("SELECT COUNT(*) FROM policies WHERE is_featured = 1")->fetchColumn();
} catch (Exception $ex) {}

$typeOptions = [
    'privacy_policy' => 'Privacy Policy',
    'terms_conditions' => 'Terms & Conditions',
    'refund_policy' => 'Refund Policy',
    'shipping_policy' => 'Shipping Policy',
    'cancellation_policy' => 'Cancellation Policy',
    'about_us' => 'About Us',
    'contact_us' => 'Contact Us',
    'faq' => 'FAQ',
    'custom' => 'Custom',
];
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid policies-page">
        <div class="policies-heading">
            <div>
                <h4 class="policies-title">📄 Policies Management</h4>
                <p class="policies-subtitle">Manage legal pages, visibility, publish state, and featured content from one place.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="create.php" class="btn btn-primary btn-policy">
                    <i class="fa fa-plus me-1"></i> Add Policy
                </a>
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
            <div class="col-md-3">
                <div class="policy-stat-card">
                    <span class="policy-stat-label">Total Policies</span>
                    <h3 class="policy-stat-value"><?= (int)$summary['total'] ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="policy-stat-card">
                    <span class="policy-stat-label">Published</span>
                    <h3 class="policy-stat-value"><?= (int)$summary['published'] ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="policy-stat-card">
                    <span class="policy-stat-label">Draft</span>
                    <h3 class="policy-stat-value"><?= (int)$summary['draft'] ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="policy-stat-card">
                    <span class="policy-stat-label">Featured</span>
                    <h3 class="policy-stat-value"><?= (int)$summary['featured'] ?></h3>
                </div>
            </div>
        </div>

        <div class="policies-filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Search</label>
                    <div class="policies-search-wrap">
                        <span class="policies-search-icon"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" class="form-control policies-search-input" placeholder="Search by title, slug or summary..." value="<?= e($search) ?>">
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($typeOptions as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $typeFilter === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Visibility</label>
                    <select name="visibility" class="form-select">
                        <option value="">All</option>
                        <option value="public" <?= $visibilityFilter === 'public' ? 'selected' : '' ?>>Public</option>
                        <option value="private" <?= $visibilityFilter === 'private' ? 'selected' : '' ?>>Private</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary btn-policy flex-fill">Filter</button>
                        <a href="list.php" class="btn btn-light btn-policy flex-fill">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="policies-card">
            <div class="policies-card-header">
                <h5 class="policies-card-title">All Policies</h5>
                <span class="policies-count-badge"><?= count($policies) ?> Records</span>
            </div>

            <div class="table-responsive">
                <table class="table policies-table align-middle">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Policy</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Visibility</th>
                            <th>Featured</th>
                            <th>Order</th>
                            <th>Updated</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($policies)): ?>
                            <?php foreach ($policies as $policy): ?>
                                <?php
                                    $isPublished = ($policy['status'] === 'published');
                                    $isFeatured = ((int)$policy['is_featured'] === 1);
                                    $isPrivate = ($policy['visibility'] === 'private');

                                    $statusActionText = $isPublished ? 'Unpublish Policy' : 'Publish Policy';
                                    $statusActionMessage = $isPublished
                                        ? 'This policy will be moved back to draft and will no longer appear as published.'
                                        : 'This policy will be marked as published and can be shown live if publicly accessible.';

                                    $featureActionText = $isFeatured ? 'Remove Featured Flag' : 'Mark as Featured';
                                    $featureActionMessage = $isFeatured
                                        ? 'This policy will be removed from featured sections.'
                                        : 'This policy will be highlighted as a featured policy.';

                                    $visibilityActionText = $isPrivate ? 'Make Policy Public' : 'Make Policy Private';
                                    $visibilityActionMessage = $isPrivate
                                        ? 'This policy will become publicly accessible on the website.'
                                        : 'This policy will be hidden from public access.';
                                ?>
                                <tr>
                                    <td><strong>#<?= (int)$policy['id'] ?></strong></td>

                                    <td class="policy-title-cell">
                                        <div class="policy-main-title"><?= e($policy['title'] ?: 'Untitled Policy') ?></div>
                                        <p class="policy-meta mb-1">Slug: <?= e($policy['slug'] ?: 'n/a') ?></p>
                                        <p class="policy-meta mb-0"><?= e(mb_strimwidth((string)($policy['short_description'] ?? ''), 0, 90, '...')) ?></p>
                                    </td>

                                    <td>
                                        <span class="policy-badge badge-type"><?= e(policyTypeLabel($policy['type'])) ?></span>
                                    </td>

                                    <td><?= policyStatusBadge($policy['status']) ?></td>
                                    <td><?= policyVisibilityBadge($policy['visibility']) ?></td>

                                    <td>
                                        <?php if ($isFeatured): ?>
                                            <span class="policy-badge badge-featured">Featured</span>
                                        <?php else: ?>
                                            <span class="policy-badge badge-order">Normal</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="policy-badge badge-order"><?= (int)$policy['display_order'] ?></span>
                                    </td>

                                    <td>
                                        <div class="policy-meta">
                                            <?= !empty($policy['updated_at']) ? date('d M, Y', strtotime($policy['updated_at'])) : 'N/A' ?>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="policy-actions justify-content-center">
                                            <a href="edit.php?id=<?= (int)$policy['id'] ?>" class="action-btn action-edit">
                                                <i class="fa fa-pen"></i> Edit
                                            </a>

                                            <a href="view.php?id=<?= (int)$policy['id'] ?>" class="action-btn action-view" title="Open admin view">
    <i class="fa fa-eye"></i> View
</a>

                                            <button
                                                type="button"
                                                class="action-btn action-status js-policy-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#policyActionModal"
                                                data-action="toggle_status"
                                                data-policy-id="<?= (int)$policy['id'] ?>"
                                                data-title="<?= e($statusActionText) ?>"
                                                data-message="<?= e($statusActionMessage) ?>"
                                                data-policy-name="<?= e($policy['title']) ?>"
                                                data-policy-slug="<?= e($policy['slug']) ?>"
                                                data-confirm-text="<?= e($statusActionText) ?>"
                                                data-confirm-class="modal-confirm-success"
                                                data-theme-class="modal-theme-success"
                                                data-icon="fa-toggle-on"
                                            >
                                                <i class="fa fa-toggle-on"></i>
                                                <?= $isPublished ? 'Unpublish' : 'Publish' ?>
                                            </button>

                                            <button
                                                type="button"
                                                class="action-btn action-feature js-policy-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#policyActionModal"
                                                data-action="toggle_featured"
                                                data-policy-id="<?= (int)$policy['id'] ?>"
                                                data-title="<?= e($featureActionText) ?>"
                                                data-message="<?= e($featureActionMessage) ?>"
                                                data-policy-name="<?= e($policy['title']) ?>"
                                                data-policy-slug="<?= e($policy['slug']) ?>"
                                                data-confirm-text="<?= e($featureActionText) ?>"
                                                data-confirm-class="modal-confirm-warning"
                                                data-theme-class="modal-theme-warning"
                                                data-icon="fa-star"
                                            >
                                                <i class="fa fa-star"></i>
                                                <?= $isFeatured ? 'Unfeature' : 'Feature' ?>
                                            </button>

                                            <button
                                                type="button"
                                                class="action-btn action-visibility js-policy-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#policyActionModal"
                                                data-action="toggle_visibility"
                                                data-policy-id="<?= (int)$policy['id'] ?>"
                                                data-title="<?= e($visibilityActionText) ?>"
                                                data-message="<?= e($visibilityActionMessage) ?>"
                                                data-policy-name="<?= e($policy['title']) ?>"
                                                data-policy-slug="<?= e($policy['slug']) ?>"
                                                data-confirm-text="<?= e($visibilityActionText) ?>"
                                                data-confirm-class="modal-confirm-dark"
                                                data-theme-class="modal-theme-dark"
                                                data-icon="fa-lock"
                                            >
                                                <i class="fa fa-lock"></i>
                                                <?= $isPrivate ? 'Make Public' : 'Make Private' ?>
                                            </button>

                                            <button
                                                type="button"
                                                class="action-btn action-delete js-policy-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#policyActionModal"
                                                data-action="delete_policy"
                                                data-policy-id="<?= (int)$policy['id'] ?>"
                                                data-title="Delete Policy"
                                                data-message="This action will permanently remove this policy from the system. Please confirm before continuing."
                                                data-policy-name="<?= e($policy['title']) ?>"
                                                data-policy-slug="<?= e($policy['slug']) ?>"
                                                data-confirm-text="Delete Policy"
                                                data-confirm-class="modal-confirm-danger"
                                                data-theme-class="modal-theme-danger"
                                                data-icon="fa-trash"
                                            >
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">
                                    <div class="policies-empty">
                                        No policy records found. Try changing filters or add a new policy.
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="policyActionModal" tabindex="-1" aria-labelledby="policyActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content policy-modal modal-theme-danger" id="policyActionModalContent">
            <div class="modal-header">
                <h5 class="modal-title" id="policyActionModalLabel">
                    <span class="policy-modal-icon">
                        <i class="fa fa-trash" id="policyActionModalIcon"></i>
                    </span>
                    <span id="policyActionModalTitle">Confirm Action</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="policy-modal-text" id="policyActionModalMessage">
                    Please confirm that you want to continue.
                </p>

                <div class="policy-modal-meta">
                    <div class="row g-3">
                        <div class="col-12">
                            <span class="meta-label">Policy Name</span>
                            <div class="meta-value" id="policyActionModalPolicyName">-</div>
                        </div>
                        <div class="col-12">
                            <span class="meta-label">Slug</span>
                            <div class="meta-value" id="policyActionModalPolicySlug">-</div>
                        </div>
                    </div>
                </div>

                <form method="POST" id="policyActionForm" class="mt-3">
                    <input type="hidden" name="action" id="policyActionInputAction">
                    <input type="hidden" name="policy_id" id="policyActionInputPolicyId">
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light modal-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="policyActionForm" class="btn modal-btn modal-confirm-danger" id="policyActionConfirmBtn">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const policyActionModal = document.getElementById('policyActionModal');

    if (policyActionModal) {
        policyActionModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const action = button.getAttribute('data-action') || '';
            const policyId = button.getAttribute('data-policy-id') || '';
            const title = button.getAttribute('data-title') || 'Confirm Action';
            const message = button.getAttribute('data-message') || 'Please confirm this action.';
            const policyName = button.getAttribute('data-policy-name') || '-';
            const policySlug = button.getAttribute('data-policy-slug') || '-';
            const confirmText = button.getAttribute('data-confirm-text') || 'Confirm';
            const confirmClass = button.getAttribute('data-confirm-class') || 'modal-confirm-danger';
            const themeClass = button.getAttribute('data-theme-class') || 'modal-theme-danger';
            const icon = button.getAttribute('data-icon') || 'fa-trash';

            document.getElementById('policyActionModalTitle').textContent = title;
            document.getElementById('policyActionModalMessage').textContent = message;
            document.getElementById('policyActionModalPolicyName').textContent = policyName;
            document.getElementById('policyActionModalPolicySlug').textContent = policySlug;
            document.getElementById('policyActionInputAction').value = action;
            document.getElementById('policyActionInputPolicyId').value = policyId;

            const modalIcon = document.getElementById('policyActionModalIcon');
            modalIcon.className = 'fa ' + icon;

            const modalContent = document.getElementById('policyActionModalContent');
            modalContent.classList.remove('modal-theme-danger', 'modal-theme-success', 'modal-theme-warning', 'modal-theme-dark');
            modalContent.classList.add(themeClass);

            const confirmBtn = document.getElementById('policyActionConfirmBtn');
            confirmBtn.textContent = confirmText;
            confirmBtn.className = 'btn modal-btn ' + confirmClass;
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>