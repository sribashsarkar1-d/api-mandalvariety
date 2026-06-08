<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search   = trim($_GET['search'] ?? '');
$status   = trim($_GET['status'] ?? '');
$sort     = trim($_GET['sort'] ?? 'latest');

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE :search OR slug LIKE :search OR description LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if ($status === 'active') {
    $where[] = "is_active = 1";
} elseif ($status === 'inactive') {
    $where[] = "is_active = 0";
}

$whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

switch ($sort) {
    case 'name_az':
        $orderBy = "name ASC";
        break;
    case 'name_za':
        $orderBy = "name DESC";
        break;
    case 'oldest':
        $orderBy = "id ASC";
        break;
    default:
        $orderBy = "id DESC";
        break;
}

$countSql = "SELECT COUNT(id) FROM categories $whereSql";
$countStmt = $conn->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));

$sql = "SELECT c.*, (SELECT COUNT(id) FROM products WHERE category_id = c.id) as product_count FROM categories c $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    @media (max-width: 768px) {
        .page-header-premium {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
        }
        .page-header-premium .btn {
            width: 100%;
            justify-content: center;
        }
    }

    .premium-card {
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .premium-card-body {
        padding: 25px;
    }

    .form-control-premium, .form-select-premium {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 10px 16px;
        font-size: 0.9rem;
        background: #ffffff;
        transition: all 0.3s ease;
    }

    .form-control-premium:focus, .form-select-premium:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
        outline: none;
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
        width: 50px; 
        height: 50px; 
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        background: #fff;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>
    <div class="container-fluid mt-4 mb-5 px-4">
        
        <div class="page-header-premium">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-layer-group me-2"></i> Categories</h3>
                <p class="mb-0 text-white-50">Manage product categories to keep your store organized.</p>
            </div>
            <a href="create.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #6d28d9;">
                <i class="fa-solid fa-plus me-2"></i> Add Category
            </a>
        </div>

        <!-- Filters -->
        <div class="premium-card mb-4">
            <div class="premium-card-body pb-3">
                <form method="GET" class="row g-3">
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-magnifying-glass me-1"></i> SEARCH</label>
                        <input type="text" name="search" value="<?= e($search) ?>" class="form-control-premium" placeholder="Name, slug, description...">
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-box-open me-1"></i> STATUS</label>
                        <select name="status" class="form-select-premium">
                            <option value="">All</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <label class="form-label text-muted small fw-bold"><i class="fa-solid fa-sort me-1"></i> SORT</label>
                        <select name="sort" class="form-select-premium">
                            <option value="latest" <?= ($sort === 'latest') ? 'selected' : '' ?>>Latest</option>
                            <option value="name_az" <?= ($sort === 'name_az') ? 'selected' : '' ?>>Name A-Z</option>
                            <option value="name_za" <?= ($sort === 'name_za') ? 'selected' : '' ?>>Name Z-A</option>
                            <option value="oldest" <?= ($sort === 'oldest') ? 'selected' : '' ?>>Oldest</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="list.php" class="btn btn-light rounded-pill px-4 fw-bold text-muted border">Reset</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: var(--primary-gradient); border:none;">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $row): ?>
                                <tr id="row-<?= (int)$row['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($row['image'])): ?>
                                                <div class="position-relative">
                                                    <img src="../uploads/<?= e($row['image']) ?>" alt="img" class="cat-img-thumb">
                                                </div>
                                            <?php else: ?>
                                                <div class="cat-img-thumb d-flex align-items-center justify-content-center text-muted" style="background: #f1f5f9; font-size: 0.8rem;">
                                                    <i class="fa-solid fa-image-slash"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark mb-1" style="font-size: 1.05rem;"><?= e($row['name']) ?></div>
                                                <div class="small text-muted d-flex gap-2 align-items-center">
                                                    <span>Slug: <?= e($row['slug']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="fa-solid fa-cube me-1"></i><?= (int)$row['product_count'] ?> items
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ((int)$row['is_active'] === 1): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1"><i class="fa-solid fa-eye me-1"></i>Active</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1"><i class="fa-solid fa-eye-slash me-1"></i>Hidden</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="text-muted small">
                                            <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn-action btn-edit" title="Edit Category">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button class="btn-action btn-delete deleteBtn" data-id="<?= (int)$row['id'] ?>" title="Delete Category">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="fa-solid fa-layer-group fa-3x mb-3 text-light"></i><br>
                                        No categories found matching your criteria.
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
            
            <?php if ($pages > 1): ?>
                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Showing page <?= $page ?> of <?= $pages ?></span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        if (confirm('Are you absolutely sure you want to delete this category? Products within this category will not be deleted but they will lose their category association.')) {
            fetch('delete.php?id=' + id)
                .then(res => res.text())
                .then(() => {
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.style.transition = "opacity 0.3s";
                        row.style.opacity = 0;
                        setTimeout(() => row.remove(), 300);
                    }
                })
                .catch(() => {
                    alert('Delete failed! Please try again.');
                });
        }
    });
});
</script>