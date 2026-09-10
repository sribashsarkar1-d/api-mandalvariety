<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid Banner ID');
}

$stmt = $conn->prepare("SELECT * FROM home_banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banner) {
    die('Banner not found');
}

$errors = [];
$success = '';

$data = [
    'title' => $banner['title'] ?? '',
    'subtitle' => $banner['subtitle'] ?? '',
    'description' => $banner['description'] ?? '',
    'button_text' => $banner['button_text'] ?? '',
    'button_link' => $banner['button_link'] ?? '',
    'is_active' => (string)($banner['is_active'] ?? '1'),
    'sort_order' => (string)($banner['sort_order'] ?? '0'),
    'start_date' => $banner['start_date'] ? date('Y-m-d\TH:i', strtotime($banner['start_date'])) : '',
    'end_date' => $banner['end_date'] ? date('Y-m-d\TH:i', strtotime($banner['end_date'])) : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title'] = trim($_POST['title'] ?? '');
    $data['subtitle'] = trim($_POST['subtitle'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['button_text'] = trim($_POST['button_text'] ?? '');
    $data['button_link'] = trim($_POST['button_link'] ?? '');
    $data['is_active'] = (string)($_POST['is_active'] ?? '1');
    $data['sort_order'] = (int)($_POST['sort_order'] ?? 0);
    $data['start_date'] = trim($_POST['start_date'] ?? '');
    $data['end_date'] = trim($_POST['end_date'] ?? '');

    if ($data['title'] === '') {
        $errors[] = 'Title is required';
    }

    if ($data['start_date'] !== '' && $data['end_date'] !== '') {
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $errors[] = 'End date cannot be before start date';
        }
    }

    $uploadedImage = $banner['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/banners/';
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
                $uploadedImage = 'banners/' . $newName;
                
                // Only delete old image if it exists
                if (!empty($banner['image']) && is_file(__DIR__ . '/../uploads/' . $banner['image'])) {
                    @unlink(__DIR__ . '/../uploads/' . $banner['image']);
                }
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE home_banners SET title = :title, subtitle = :subtitle, description = :description, image = :image, button_text = :button_text, button_link = :button_link, is_active = :is_active, sort_order = :sort_order, start_date = :start_date, end_date = :end_date WHERE id = :id");
            
            $stmt->execute([
                ':title' => $data['title'],
                ':subtitle' => $data['subtitle'] !== '' ? $data['subtitle'] : null,
                ':description' => $data['description'] !== '' ? $data['description'] : null,
                ':image' => $uploadedImage,
                ':button_text' => $data['button_text'] !== '' ? $data['button_text'] : null,
                ':button_link' => $data['button_link'] !== '' ? $data['button_link'] : null,
                ':is_active' => (int)$data['is_active'],
                ':sort_order' => $data['sort_order'],
                ':start_date' => $data['start_date'] !== '' ? $data['start_date'] : null,
                ':end_date' => $data['end_date'] !== '' ? $data['end_date'] : null,
                ':id' => $id
            ]);

            $success = 'Banner updated successfully';

            // Refresh banner data
            $stmt = $conn->prepare("SELECT * FROM home_banners WHERE id = ?");
            $stmt->execute([$id]);
            $banner = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $errors[] = 'Failed to update banner. ' . $e->getMessage();
        }
    }
}
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
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
        box-shadow: 0 15px 30px rgba(2, 132, 199, 0.2);
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
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
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
        border-color: #0ea5e9;
    }

    .image-upload-zone i {
        font-size: 40px;
        color: #64748b;
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    .image-upload-zone:hover i {
        color: #0ea5e9;
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
        box-shadow: 0 10px 20px rgba(2, 132, 199, 0.3);
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
                <h3 class="mb-2 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Home Banner</h3>
                <p class="mb-0 text-white-50">Update promotional banner information.</p>
            </div>
            <a href="list.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" style="color: #0284c7;">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Banners
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
                            <i class="fa-solid fa-info-circle text-primary"></i> Banner Details
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                
                                <div class="col-md-6">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" class="form-control-premium" value="<?= e($data['title']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" class="form-control-premium" value="<?= e($data['subtitle']) ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control-premium" rows="3"><?= e($data['description']) ?></textarea>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Button Text</label>
                                    <input type="text" name="button_text" class="form-control-premium" value="<?= e($data['button_text']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Button Link</label>
                                    <input type="text" name="button_link" class="form-control-premium" value="<?= e($data['button_link']) ?>">
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-gear text-secondary"></i> Settings & Schedule
                        </div>
                        <div class="premium-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select-premium">
                                        <option value="1" <?= $data['is_active'] === '1' ? 'selected' : '' ?>>Active (Visible)</option>
                                        <option value="0" <?= $data['is_active'] === '0' ? 'selected' : '' ?>>Inactive (Hidden)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="sort_order" class="form-control-premium" value="<?= e($data['sort_order']) ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Start Date (Optional)</label>
                                    <input type="datetime-local" name="start_date" class="form-control-premium" value="<?= e($data['start_date']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date (Optional)</label>
                                    <input type="datetime-local" name="end_date" class="form-control-premium" value="<?= e($data['end_date']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="premium-card mb-4">
                        <div class="premium-card-header">
                            <i class="fa-solid fa-image text-info"></i> Banner Image
                        </div>
                        <div class="premium-card-body text-center">
                            
                            <?php if (!empty($banner['image'])): ?>
                                <div class="img-preview-card mt-0 mb-3" style="display:block;">
                                    <img src="../uploads/<?= e($banner['image']) ?>" alt="Current Image">
                                </div>
                            <?php endif; ?>

                            <label for="image" class="image-upload-zone w-100 d-block">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <h6 class="fw-bold text-dark mb-1">Click to upload new image</h6>
                                <p class="small text-muted mb-0">Leave blank to keep existing</p>
                            </label>
                            <input type="file" name="image" id="image" class="d-none" accept=".jpg,.jpeg,.png,.webp">
                            
                            <div id="imagePreviewContainer" class="img-preview-card" style="display:none;">
                                <img src="" id="imagePreview" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <div class="premium-card mb-4" style="background: transparent; border: none; box-shadow: none;">
                        <button type="submit" class="btn-premium-primary mb-3">
                            <i class="fa-solid fa-check-circle me-2"></i> Update Banner
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
