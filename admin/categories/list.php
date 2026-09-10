<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch Parent Categories with their Children
$sql = "SELECT * FROM parent_categories ORDER BY sort_order ASC, name ASC";
$stmt = $conn->query($sql);
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlChild = "SELECT * FROM child_categories ORDER BY sort_order ASC, name ASC";
$stmtChild = $conn->query($sqlChild);
$childrenResult = $stmtChild->fetchAll(PDO::FETCH_ASSOC);

// Group children by parent_id
$childrenByParent = [];
foreach ($childrenResult as $child) {
    $childrenByParent[$child['parent_category_id']][] = $child;
}

?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        --secondary-bg: #f8fafc;
        --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--secondary-bg);
        font-family: 'Inter', system-ui, sans-serif;
    }

    .page-header-premium {
        background: var(--primary-gradient);
        border-radius: 20px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 15px 30px rgba(109, 40, 217, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .page-header-premium::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .page-header-premium > * {
        z-index: 2;
    }

    .premium-card {
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .table-premium {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-premium th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 16px 20px;
        white-space: nowrap;
    }

    .table-premium td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table-premium tbody tr {
        transition: background-color 0.2s ease;
    }

    .table-premium tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .row-child td {
        background-color: #fcfcfd;
    }
    .row-child:hover td {
        background-color: #f1f5f9 !important;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: #f0fdf4;
        color: #16a34a;
    }
    .btn-edit:hover {
        background: #16a34a;
        color: white;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }
    
    .cat-img-thumb {
        width: 40px; 
        height: 40px; 
        border-radius: 10px;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        background: #fff;
    }

    .tree-indicator {
        color: #cbd5e1;
        margin-right: 10px;
        font-family: monospace;
        font-size: 1.2rem;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>
    <div class="container-fluid mt-4 mb-5 px-4">
        
        <div class="page-header-premium">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-layer-group me-2"></i> Categories</h3>
                <p class="mb-0 text-white-50">Manage parent and child categories.</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #6d28d9;">
                    <i class="fa-solid fa-plus me-2"></i> Add Parent / Child Category
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($parents)): ?>
                            <?php foreach ($parents as $parent): ?>
                                <tr id="row-parent-<?= (int)$parent['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($parent['image'])): ?>
                                                <img src="../uploads/<?= e($parent['image']) ?>" alt="img" class="cat-img-thumb">
                                            <?php else: ?>
                                                <div class="cat-img-thumb d-flex align-items-center justify-content-center text-muted" style="background: #e2e8f0;">
                                                    <i class="fa-solid fa-image-slash"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 1.05rem;"><?= e($parent['name']) ?></div>
                                                <div class="small text-muted">Slug: <?= e($parent['slug']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">Parent</span>
                                    </td>
                                    <td><?= (int)$parent['sort_order'] ?></td>
                                    <td>
                                        <?php if ((int)$parent['is_active'] === 1): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1">Active</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="edit.php?id=<?= (int)$parent['id'] ?>&type=parent" class="btn-action btn-edit" title="Edit Parent">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button class="btn-action btn-delete deleteBtn" data-id="<?= (int)$parent['id'] ?>" data-type="parent" title="Delete Parent">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <?php 
                                // Render children
                                if (isset($childrenByParent[$parent['id']])): 
                                    $children = $childrenByParent[$parent['id']];
                                    $childCount = count($children);
                                    foreach ($children as $index => $child):
                                        $isLast = ($index === $childCount - 1);
                                        $treePrefix = $isLast ? '└──' : '├──';
                                ?>
                                    <tr id="row-child-<?= (int)$child['id'] ?>" class="row-child">
                                        <td>
                                            <div class="d-flex align-items-center gap-2" style="padding-left: 30px;">
                                                <span class="tree-indicator"><?= $treePrefix ?></span>
                                                <?php if (!empty($child['image'])): ?>
                                                    <img src="../uploads/<?= e($child['image']) ?>" alt="img" class="cat-img-thumb" style="width: 32px; height: 32px;">
                                                <?php else: ?>
                                                    <div class="cat-img-thumb d-flex align-items-center justify-content-center text-muted" style="background: #e2e8f0; width: 32px; height: 32px; font-size: 0.7rem;">
                                                        <i class="fa-solid fa-image-slash"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="text-dark fw-medium"><?= e($child['name']) ?></div>
                                                    <div class="small text-muted">Slug: <?= e($child['slug']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill">Child</span>
                                        </td>
                                        <td><?= (int)$child['sort_order'] ?></td>
                                        <td>
                                            <?php if ((int)$child['is_active'] === 1): ?>
                                                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1">Active</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="edit.php?id=<?= (int)$child['id'] ?>&type=child" class="btn-action btn-edit" title="Edit Child">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <button class="btn-action btn-delete deleteBtn" data-id="<?= (int)$child['id'] ?>" data-type="child" title="Delete Child">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach; 
                                endif; 
                                ?>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="fa-solid fa-layer-group fa-3x mb-3 text-light"></i><br>
                                        No categories found.
                                    </div>
                                    <a href="create.php" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: var(--primary-gradient); border:none;">
                                        Create Your First Category
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

<?php include '../includes/footer.php'; ?>

<script>
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        const type = this.dataset.type;
        
        let confirmMsg = 'Are you sure you want to delete this category?';
        if (type === 'parent') {
            confirmMsg = 'Are you sure you want to delete this parent category? Note: You cannot delete a parent if it has child categories.';
        }

        if (confirm(confirmMsg)) {
            fetch(`delete.php?id=${id}&type=${type}`)
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text) });
                    }
                    return res.text();
                })
                .then(() => {
                    const row = document.getElementById(`row-${type}-${id}`);
                    if (row) {
                        row.style.transition = "opacity 0.3s";
                        row.style.opacity = 0;
                        setTimeout(() => row.remove(), 300);
                    }
                    // For parent, reload page to safely remove child rows from view
                    if (type === 'parent') {
                        setTimeout(() => window.location.reload(), 300);
                    }
                })
                .catch(err => {
                    alert(err.message || 'Delete failed! Please try again.');
                });
        }
    });
});
</script>