<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function makeSlug($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-\s]/', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(PDO $conn, $slug)
{
    $base = $slug ?: 'category';
    $newSlug = $base;
    $i = 1;

    while (true) {
        $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$newSlug]);
        if (!$stmt->fetch()) {
            return $newSlug;
        }
        $newSlug = $base . '-' . $i++;
    }
}

$errors = [];
$success = '';

$data = [
    'name' => '',
    'slug' => '',
    'description' => '',
    'is_active' => '1',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name'] = trim($_POST['name'] ?? '');
    $data['slug'] = trim($_POST['slug'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['is_active'] = (string)($_POST['is_active'] ?? '1');

    if ($data['name'] === '') {
        $errors[] = 'Category name is required';
    }

    $uploadedImage = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imgName = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Only JPG, JPEG, PNG and WEBP files are allowed';
        } else if (is_uploaded_file($tmp)) {
            $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $newName)) {
                $uploadedImage = 'categories/' . $newName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $slug = uniqueSlug($conn, makeSlug($data['slug'] ?: $data['name']));

            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, image, is_active) VALUES (:name, :slug, :description, :image, :is_active)");
            $stmt->execute([
                ':name' => $data['name'],
                ':slug' => $slug,
                ':description' => $data['description'] !== '' ? $data['description'] : null,
                ':image' => $uploadedImage,
                ':is_active' => (int)$data['is_active']
            ]);

            $success = 'Category created successfully';

            foreach ($data as $k => $v) $data[$k] = '';
            $data['is_active'] = '1';
        } catch (Exception $e) {
            $errors[] = 'Failed to create category. ' . $e->getMessage();
        }
    }
}
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
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
        box-shadow: 0 15px 30px rgba(225, 29, 72, 0.2);
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
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .premium-card-header {
        background: rgba(255,255,255,0.9);
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 25px;
        font-weight: 700;
        color: #1e293b;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .premium-card-body {
        padding: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .form-control-premium, .form-select-premium {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 12px 18px;
        font-size: 0.95rem;
        background: #ffffff;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control-premium:focus, .form-select-premium:focus {
        border-color: #e11d48;
        box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.15);
        outline: none;
    }

    .image-upload-zone {
        border: 2px dashed #94a3b8;
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .image-upload-zone:hover {
        background: #f1f5f9;
        border-color: #e11d48;
    }

    .image-upload-zone i {
        font-size: 40px;
        color: #64748b;
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    .image-upload-zone:hover i {
        color: #e11d48;
    }

    .btn-premium-primary {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 28px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        width: 100%;
        font-size: 1rem;
    }

    .btn-premium-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(225, 29, 72, 0.3);
        color: white;
    }

    .btn-premium-light {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 14px 28px;
        font-weight: 600;
        transition: all 0.3s;
        width: 100%;
        display: block;
        text-align: center;
        text-decoration: none;
    }

    .btn-premium-light:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .img-preview-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        background: white;
        padding: 5px;
        margin-top: 20px;
        display: none;
    }

    .img-preview-card img {
        width: 100%;
        height: auto;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid mt-4 mb-5 px-4">
        
        <div class="page-header-premium">
            <div>
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-folder-plus me-2"></i> Create Category</h3>
                <p class="mb-0 text-white-50">Add a new category to organize your products.</p>
            </div>
            <a href="list.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #e11d48;">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Categories
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-circle-check me-2"></i><?= e($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                
                <div class="col-lg-8">
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-info-circle text-danger"></i> Category Details
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Category Name *</label>
                                    <input type="text" name="name" class="form-control-premium" value="<?= e($data['name']) ?>" placeholder="e.g. Footwear, Electronics" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slug (Optional)</label>
                                    <input type="text" name="slug" class="form-control-premium" value="<?= e($data['slug']) ?>" placeholder="Auto-generated if left blank">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control-premium" rows="4" placeholder="Brief description of the category..."><?= e($data['description']) ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select-premium">
                                        <option value="1" <?= $data['is_active'] === '1' ? 'selected' : '' ?>>Active (Visible)</option>
                                        <option value="0" <?= $data['is_active'] === '0' ? 'selected' : '' ?>>Inactive (Hidden)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-image text-info"></i> Category Image
                        </div>
                        <div class="premium-card-body text-center">
                            <label for="image" class="image-upload-zone w-100 d-block">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <h6 class="fw-bold text-dark mb-1">Click to upload image</h6>
                                <p class="small text-muted mb-0">JPG, PNG, WEBP</p>
                            </label>
                            <input type="file" name="image" id="image" class="d-none" accept=".jpg,.jpeg,.png,.webp">
                            
                            <div id="imagePreviewContainer" class="img-preview-card">
                                <img src="" id="imagePreview" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <div class="premium-card mb-4" style="background: transparent; border: none; box-shadow: none;">
                        <button type="submit" class="btn-premium-primary mb-3">
                            <i class="fa-solid fa-check-circle me-2"></i> Save Category
                        </button>
                        <a href="list.php" class="btn-premium-light">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
