<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$images = [];
try {
    $sql = "SELECT * FROM appbackimage ORDER BY created_at DESC";
    $stmt = $conn->query($sql);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMsg = "Database error: " . $e->getMessage();
}

?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        box-shadow: 0 15px 30px rgba(16, 185, 129, 0.2);
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
    
    .banner-preview-thumb {
        width: 60px; 
        height: 120px; 
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        background: #fff;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>
    <div class="container-fluid mt-4 mb-5 px-4">
        
        <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger rounded-4 shadow-sm border-0 mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= e($errorMsg) ?>
        </div>
        <?php endif; ?>

        <div class="page-header-premium">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-mobile-screen me-2"></i> App Background Image</h3>
                <p class="mb-0 text-white-50">Manage the background image for the mobile app.</p>
            </div>
            <div>
                <a href="create.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #059669;">
                    <i class="fa-solid fa-plus me-2"></i> Add Image
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-premium mb-0">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $img): ?>
                                <tr id="row-<?= (int)$img['id'] ?>">
                                    <td>
                                        <?php if (!empty($img['image'])): ?>
                                            <?php 
                                            $imgSrc = filter_var($img['image'], FILTER_VALIDATE_URL) ? $img['image'] : '../uploads/' . $img['image'];
                                            ?>
                                            <img src="<?= e($imgSrc) ?>" alt="img" class="banner-preview-thumb">
                                        <?php else: ?>
                                            <div class="banner-preview-thumb d-flex align-items-center justify-content-center text-muted" style="background: #e2e8f0; width: 60px; height: 120px;">
                                                <i class="fa-solid fa-image-slash"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 1.05rem;"><?= e($img['title']) ?></div>
                                        <?php if (!empty($img['image_url'])): ?>
                                            <div class="small text-muted"><a href="<?= e($img['image_url']) ?>" target="_blank">External URL</a></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)$img['status'] === 1): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1">Active</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y h:i A', strtotime($img['created_at'])) ?></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="edit.php?id=<?= (int)$img['id'] ?>" class="btn-action btn-edit" title="Edit Image">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button class="btn-action btn-delete deleteBtn" data-id="<?= (int)$img['id'] ?>" title="Delete Image">
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
                                        <i class="fa-solid fa-images fa-3x mb-3 text-light"></i><br>
                                        No background images found.
                                    </div>
                                    <a href="create.php" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: var(--primary-gradient); border:none;">
                                        Add Background Image
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
        if (confirm('Are you absolutely sure you want to delete this image?')) {
            fetch('delete.php?id=' + id)
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text) });
                    }
                    return res.text();
                })
                .then(() => {
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.style.transition = "opacity 0.3s";
                        row.style.opacity = 0;
                        setTimeout(() => row.remove(), 300);
                    }
                })
                .catch(err => {
                    alert(err.message || 'Delete failed! Please try again.');
                });
        }
    });
});
</script>
