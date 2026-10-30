<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// Auto-create table if not exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS `terms_and_conditions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `content` longtext NOT NULL,
      `section_order` int(11) NOT NULL DEFAULT 0,
      `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `idx_terms_slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch (Exception $e) {}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Filters
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$where = " WHERE 1 ";
$params = [];

if ($search !== '') {
    $where .= " AND (title LIKE ? OR slug LIKE ? OR content LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($statusFilter !== '' && in_array($statusFilter, ['published', 'draft', 'archived', 'active', 'inactive'])) {
    if ($statusFilter === 'active') {
        $where .= " AND is_active = 1";
    } elseif ($statusFilter === 'inactive') {
        $where .= " AND is_active = 0";
    } else {
        $where .= " AND status = ?";
        $params[] = $statusFilter;
    }
}

$terms = [];
try {
    $sql = "SELECT * FROM terms_and_conditions {$where} ORDER BY section_order ASC, id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $flashError = "Database error: " . $e->getMessage();
}

// Summary stats
$summary = ['total' => 0, 'published' => 0, 'draft' => 0, 'active' => 0];
try {
    $summary['total'] = (int)$conn->query("SELECT COUNT(*) FROM terms_and_conditions")->fetchColumn();
    $summary['published'] = (int)$conn->query("SELECT COUNT(*) FROM terms_and_conditions WHERE status = 'published'")->fetchColumn();
    $summary['draft'] = (int)$conn->query("SELECT COUNT(*) FROM terms_and_conditions WHERE status = 'draft'")->fetchColumn();
    $summary['active'] = (int)$conn->query("SELECT COUNT(*) FROM terms_and_conditions WHERE is_active = 1")->fetchColumn();
} catch (Exception $e) {}
?>

<style>
    :root {
        --tc-primary: #0d6efd;
        --tc-primary-grad: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        --tc-success: #198754;
        --tc-warning: #f59e0b;
        --tc-danger: #dc3545;
        --tc-card-bg: #ffffff;
        --tc-border: #e2e8f0;
        --tc-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        --tc-radius: 16px;
    }

    body {
        background-color: #f8fafc;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .tc-header {
        background: var(--tc-primary-grad);
        border-radius: 20px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 15px 35px rgba(13, 110, 253, 0.22);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
        position: relative;
        overflow: hidden;
    }

    .tc-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .stat-card-tc {
        background: var(--tc-card-bg);
        border: 1px solid var(--tc-border);
        border-radius: var(--tc-radius);
        padding: 20px;
        box-shadow: var(--tc-shadow);
        transition: transform .2s ease, box-shadow .2s ease;
        height: 100%;
    }

    .stat-card-tc:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.08);
    }

    .stat-number {
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.1;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .card-filter {
        background: var(--tc-card-bg);
        border: 1px solid var(--tc-border);
        border-radius: var(--tc-radius);
        padding: 20px;
        box-shadow: var(--tc-shadow);
        margin-bottom: 25px;
    }

    .table-tc {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-tc th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        padding: 16px 20px;
        white-space: nowrap;
    }

    .table-tc td {
        padding: 16px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table-tc tbody tr:hover {
        background-color: #fbfcfe;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all .2s ease;
        text-decoration: none;
    }

    .btn-preview {
        background: #eff6ff;
        color: #2563eb;
    }
    .btn-preview:hover {
        background: #2563eb;
        color: #fff;
    }

    .btn-edit {
        background: #f0fdf4;
        color: #16a34a;
    }
    .btn-edit:hover {
        background: #16a34a;
        color: #fff;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #dc2626;
        color: #fff;
    }

    .badge-status {
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-published {
        background: #dcfce7;
        color: #15803d;
    }
    .status-draft {
        background: #fef3c7;
        color: #b45309;
    }
    .status-archived {
        background: #f1f5f9;
        color: #64748b;
    }

    .order-badge {
        display: inline-block;
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .content-snippet {
        max-width: 340px;
        font-size: 0.875rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .switch-active {
        cursor: pointer;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>
    <div class="container-fluid mt-4 mb-5 px-4">

        <!-- Flash alerts -->
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= e($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= e($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="tc-header">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-file-contract me-2"></i> Terms & Conditions</h3>
                <p class="mb-0 text-white-50">Manage legal terms, usage policies, user rules, and section details.</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-light rounded-pill px-4 py-2 fw-bold shadow-sm" style="color: #0a58ca;">
                    <i class="fa-solid fa-plus me-2"></i> Add Terms & Conditions
                </a>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-tc d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Total Sections</span>
                        <div class="stat-number mt-1"><?= $summary['total'] ?></div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-tc d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Published</span>
                        <div class="stat-number mt-1 text-success"><?= $summary['published'] ?></div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-tc d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Drafts</span>
                        <div class="stat-number mt-1 text-warning"><?= $summary['draft'] ?></div>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-tc d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold">Active in App</span>
                        <div class="stat-number mt-1 text-info"><?= $summary['active'] ?></div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card-filter">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">Search Terms</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title, slug, or content..." value="<?= e($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="published" <?= ($statusFilter === 'published') ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= ($statusFilter === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= ($statusFilter === 'archived') ? 'selected' : '' ?>>Archived</option>
                        <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active Only</option>
                        <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    <?php if ($search !== '' || $statusFilter !== ''): ?>
                        <a href="list.php" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="card bg-white border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-tc mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Order</th>
                            <th>Section Title</th>
                            <th>Content Preview</th>
                            <th>Status</th>
                            <th>Active</th>
                            <th>Last Updated</th>
                            <th class="text-end" style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($terms)): ?>
                            <?php foreach ($terms as $t): ?>
                                <tr id="term-row-<?= (int)$t['id'] ?>">
                                    <td>
                                        <span class="order-badge" title="Display Order: <?= (int)$t['section_order'] ?>">
                                            #<?= (int)$t['section_order'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 1.02rem;">
                                            <?= e($t['title']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <code><?= e($t['slug']) ?></code>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="content-snippet" title="<?= e($t['content']) ?>">
                                            <?= e(mb_strimwidth(strip_tags($t['content']), 0, 90, '...')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($t['status'] === 'published'): ?>
                                            <span class="badge-status status-published">
                                                <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Published
                                            </span>
                                        <?php elseif ($t['status'] === 'draft'): ?>
                                            <span class="badge-status status-draft">
                                                <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Draft
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status status-archived">
                                                <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Archived
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input switch-active" type="checkbox" role="switch" 
                                                   data-id="<?= (int)$t['id'] ?>" 
                                                   <?= ((int)$t['is_active'] === 1) ? 'checked' : '' ?>
                                                   title="Toggle active status">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <?= date('d M Y, h:i A', strtotime($t['updated_at'] ?? $t['created_at'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="action-btn btn-preview preview-btn" 
                                                    data-title="<?= e($t['title']) ?>" 
                                                    data-slug="<?= e($t['slug']) ?>" 
                                                    data-content="<?= e($t['content']) ?>"
                                                    data-order="<?= (int)$t['section_order'] ?>"
                                                    data-status="<?= e($t['status']) ?>"
                                                    title="Preview Content">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <a href="edit.php?id=<?= (int)$t['id'] ?>" class="action-btn btn-edit" title="Edit Terms">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <button type="button" class="action-btn btn-delete delete-btn" 
                                                    data-id="<?= (int)$t['id'] ?>" 
                                                    data-title="<?= e($t['title']) ?>"
                                                    title="Delete Terms">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="fa-solid fa-file-contract fa-3x mb-3 text-secondary opacity-50"></i>
                                        <h5>No Terms & Conditions Found</h5>
                                        <p class="small text-muted mb-4">Start creating your legal sections and terms for your application.</p>
                                    </div>
                                    <a href="create.php" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-semibold">
                                        <i class="fa-solid fa-plus me-1"></i> Add First Section
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="previewModalTitle"></h5>
                    <span class="badge bg-light text-muted border" id="previewModalSlug"></span>
                    <span class="badge bg-secondary ms-1" id="previewModalOrder"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 border" style="max-height: 450px; overflow-y: auto; white-space: pre-wrap; font-size: 0.95rem; line-height: 1.7;" id="previewModalContent"></div>
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirm Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                </div>
                <h5 class="fw-bold mb-2">Delete Terms & Conditions?</h5>
                <p class="text-muted mb-4">Are you sure you want to delete <b id="deleteModalTitle"></b>? This action cannot be undone.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger rounded-pill px-4 fw-semibold">Yes, Delete</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Preview Modal Handler
const previewModalEl = new bootstrap.Modal(document.getElementById('previewModal'));
document.querySelectorAll('.preview-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('previewModalTitle').textContent = this.dataset.title;
        document.getElementById('previewModalSlug').textContent = this.dataset.slug;
        document.getElementById('previewModalOrder').textContent = 'Order #' + this.dataset.order;
        document.getElementById('previewModalContent').textContent = this.dataset.content;
        previewModalEl.show();
    });
});

// Delete Modal Handler
const deleteModalEl = new bootstrap.Modal(document.getElementById('deleteModal'));
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const title = this.dataset.title;
        document.getElementById('deleteModalTitle').textContent = '"' + title + '"';
        document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + id;
        deleteModalEl.show();
    });
});

// Toggle Active Switch via AJAX
document.querySelectorAll('.switch-active').forEach(sw => {
    sw.addEventListener('change', function() {
        const id = this.dataset.id;
        const isActive = this.checked ? 1 : 0;
        
        fetch('toggle_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id) + '&is_active=' + encodeURIComponent(isActive)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Failed to update status');
                sw.checked = !sw.checked;
            }
        })
        .catch(err => {
            alert('Error updating status: ' + err.message);
            sw.checked = !sw.checked;
        });
    });
});
</script>

