<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid Terms & Conditions ID.';
    header('Location: list.php');
    exit;
}

// Fetch existing record
$term = null;
try {
    $stmt = $conn->prepare("SELECT * FROM terms_and_conditions WHERE id = ?");
    $stmt->execute([$id]);
    $term = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

if (!$term) {
    $_SESSION['flash_error'] = 'Terms & Conditions section not found.';
    header('Location: list.php');
    exit;
}

$errors = [];
$data = [
    'title' => $term['title'],
    'slug' => $term['slug'],
    'content' => $term['content'],
    'section_order' => (string)$term['section_order'],
    'status' => $term['status'],
    'is_active' => (string)$term['is_active']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title'] = trim($_POST['title'] ?? '');
    $data['slug'] = trim($_POST['slug'] ?? '');
    $data['content'] = trim($_POST['content'] ?? '');
    $data['section_order'] = (int)($_POST['section_order'] ?? $term['section_order']);
    $data['status'] = in_array($_POST['status'] ?? '', ['published', 'draft', 'archived']) ? $_POST['status'] : 'published';
    $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if ($data['title'] === '') {
        $errors[] = 'Section title is required.';
    }

    if ($data['content'] === '') {
        $errors[] = 'Content is required.';
    }

    // Generate slug if empty
    if ($data['slug'] === '') {
        $slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']), '-'));
        $data['slug'] = $slugBase ?: 'terms-' . time();
    } else {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['slug']), '-'));
    }

    // Check slug uniqueness (excluding current id)
    try {
        $stmtSlug = $conn->prepare("SELECT COUNT(*) FROM terms_and_conditions WHERE slug = ? AND id != ?");
        $stmtSlug->execute([$data['slug'], $id]);
        if ($stmtSlug->fetchColumn() > 0) {
            $data['slug'] .= '-' . time();
        }
    } catch (Exception $e) {}

    if (empty($errors)) {
        try {
            $sql = "UPDATE terms_and_conditions 
                    SET title = :title, slug = :slug, content = :content, 
                        section_order = :section_order, status = :status, is_active = :is_active 
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':content' => $data['content'],
                ':section_order' => $data['section_order'],
                ':status' => $data['status'],
                ':is_active' => $data['is_active'],
                ':id' => $id
            ]);

            $_SESSION['flash_success'] = 'Terms & Conditions section updated successfully!';
            header("Location: list.php");
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to update terms: ' . $e->getMessage();
        }
    }
}
?>

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .form-header-premium {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border-radius: 20px;
        padding: 30px 40px;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 15px 35px rgba(13, 110, 253, 0.22);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    }

    .form-label-custom {
        font-weight: 700;
        font-size: 0.9rem;
        color: #334155;
        margin-bottom: 8px;
    }

    .form-control-custom, .form-select-custom {
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }

    .word-counter {
        font-size: 0.8rem;
        color: #94a3b8;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>
    <div class="container-fluid mt-4 mb-5 px-4">

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-4 shadow-sm border-0 mb-4">
                <div class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Please fix the following errors:</div>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="form-header-premium">
            <div>
                <h3 class="mb-1 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Terms & Conditions</h3>
                <p class="mb-0 text-white-50">Editing: <?= e($term['title']) ?></p>
            </div>
            <div>
                <a href="list.php" class="btn btn-light rounded-pill px-4 py-2 fw-bold shadow-sm" style="color: #0a58ca;">
                    <i class="fa-solid fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form method="POST" id="termsForm">
                <div class="row g-4">
                    <!-- Title -->
                    <div class="col-md-8">
                        <label class="form-label form-label-custom">
                            Section Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title" id="titleInput" class="form-control form-control-custom" 
                               placeholder="e.g. Account Registration & Privacy, Return Policy..." 
                               value="<?= e($data['title']) ?>" required>
                        <small class="text-muted">Descriptive heading for this terms section.</small>
                    </div>

                    <!-- Section Order -->
                    <div class="col-md-4">
                        <label class="form-label form-label-custom">
                            Display Order
                        </label>
                        <input type="number" name="section_order" class="form-control form-control-custom" 
                               value="<?= e($data['section_order']) ?>" min="0" step="1">
                        <small class="text-muted">Determines ordering in list and mobile app (1, 2, 3...).</small>
                    </div>

                    <!-- Slug -->
                    <div class="col-md-6">
                        <label class="form-label form-label-custom">
                            URL Slug
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;">#</span>
                            <input type="text" name="slug" id="slugInput" class="form-control form-control-custom border-start-0" 
                                   placeholder="unique-slug" value="<?= e($data['slug']) ?>">
                        </div>
                        <small class="text-muted">Unique identifier used by API endpoints.</small>
                    </div>

                    <!-- Status -->
                    <div class="col-md-3">
                        <label class="form-label form-label-custom">
                            Publish Status
                        </label>
                        <select name="status" class="form-select form-select-custom">
                            <option value="published" <?= ($data['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= ($data['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                            <option value="archived" <?= ($data['status'] === 'archived') ? 'selected' : '' ?>>Archived</option>
                        </select>
                        <small class="text-muted">Only published sections will show to customers.</small>
                    </div>

                    <!-- Active Switch -->
                    <div class="col-md-3">
                        <label class="form-label form-label-custom d-block">
                            Visibility in App
                        </label>
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActiveSwitch" value="1" 
                                   <?= ((int)$data['is_active'] === 1) ? 'checked' : '' ?> style="width: 2.5em; height: 1.3em;">
                            <label class="form-check-label ms-2 fw-semibold text-dark" for="isActiveSwitch">Active</label>
                        </div>
                        <small class="text-muted">Enable to display in app.</small>
                    </div>

                    <!-- Content -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label form-label-custom mb-0">
                                Detailed Terms & Rules Content <span class="text-danger">*</span>
                            </label>
                            <span class="word-counter" id="wordCountText">0 words | 0 characters</span>
                        </div>
                        <textarea name="content" id="contentText" class="form-control form-control-custom" rows="12" 
                                  placeholder="Write the full rules, policies, conditions, and guidelines here..." required><?= e($data['content']) ?></textarea>
                        <small class="text-muted">Supports clean multi-paragraph text, bullet lines, and markdown-style formatting.</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-12 pt-3 border-top d-flex gap-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none;">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Update Terms & Conditions
                        </button>
                        <a href="list.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Character & Word Counter
const contentText = document.getElementById('contentText');
const wordCountText = document.getElementById('wordCountText');

function updateCounter() {
    const text = contentText.value.trim();
    const chars = text.length;
    const words = text ? text.split(/\s+/).length : 0;
    wordCountText.textContent = words + ' words | ' + chars + ' characters';
}

contentText.addEventListener('input', updateCounter);
updateCounter();
</script>

