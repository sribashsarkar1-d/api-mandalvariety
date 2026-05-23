<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$success = '';

$policyId = (int)($_GET['id'] ?? 0);

if ($policyId <= 0) {
    echo '<div class="w-100"><div class="container-fluid p-4"><div class="alert alert-danger">Invalid policy ID.</div></div></div>';
    include '../includes/footer.php';
    exit;
}

function makeSlug($text) {
    $text = trim((string)$text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'policy-page';
}

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

$policy = null;

try {
    $stmt = $conn->prepare("
        SELECT 
            id, title, slug, type, short_description, content, status, visibility,
            is_featured, display_order, meta_title, meta_keywords, meta_description,
            created_at, updated_at
        FROM policies
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$policyId]);
    $policy = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$policy) {
        echo '<div class="w-100"><div class="container-fluid p-4"><div class="alert alert-danger">Policy not found.</div></div></div>';
        include '../includes/footer.php';
        exit;
    }
} catch (Exception $ex) {
    echo '<div class="w-100"><div class="container-fluid p-4"><div class="alert alert-danger">Could not load policy.</div></div></div>';
    include '../includes/footer.php';
    exit;
}

$form = [
    'title' => $policy['title'] ?? '',
    'slug' => $policy['slug'] ?? '',
    'type' => $policy['type'] ?? 'custom',
    'short_description' => $policy['short_description'] ?? '',
    'content' => $policy['content'] ?? '',
    'status' => $policy['status'] ?? 'draft',
    'visibility' => $policy['visibility'] ?? 'public',
    'is_featured' => (int)($policy['is_featured'] ?? 0),
    'display_order' => $policy['display_order'] ?? 0,
    'meta_title' => $policy['meta_title'] ?? '',
    'meta_keywords' => $policy['meta_keywords'] ?? '',
    'meta_description' => $policy['meta_description'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['title'] = trim($_POST['title'] ?? '');
    $form['slug'] = makeSlug($_POST['slug'] ?? '');
    $form['type'] = trim($_POST['type'] ?? 'custom');
    $form['short_description'] = trim($_POST['short_description'] ?? '');
    $form['content'] = trim($_POST['content'] ?? '');
    $form['status'] = trim($_POST['status'] ?? 'draft');
    $form['visibility'] = trim($_POST['visibility'] ?? 'public');
    $form['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $form['display_order'] = (int)($_POST['display_order'] ?? 0);
    $form['meta_title'] = trim($_POST['meta_title'] ?? '');
    $form['meta_keywords'] = trim($_POST['meta_keywords'] ?? '');
    $form['meta_description'] = trim($_POST['meta_description'] ?? '');

    if ($form['title'] === '') {
        $errors[] = 'Policy title is required.';
    }

    if ($form['slug'] === '') {
        $errors[] = 'Slug is required.';
    }

    if (!array_key_exists($form['type'], $typeOptions)) {
        $errors[] = 'Invalid policy type selected.';
    }

    if (!in_array($form['status'], ['draft', 'published', 'archived'], true)) {
        $errors[] = 'Invalid status selected.';
    }

    if (!in_array($form['visibility'], ['public', 'private'], true)) {
        $errors[] = 'Invalid visibility selected.';
    }

    try {
        $slugCheck = $conn->prepare("SELECT id FROM policies WHERE slug = ? AND id != ? LIMIT 1");
        $slugCheck->execute([$form['slug'], $policyId]);
        if ($slugCheck->fetchColumn()) {
            $errors[] = 'This slug is already in use by another policy.';
        }
    } catch (Exception $ex) {
        $errors[] = 'Could not validate slug uniqueness.';
    }

    if (empty($errors)) {
        try {
            $update = $conn->prepare("
                UPDATE policies SET
                    title = ?,
                    slug = ?,
                    type = ?,
                    short_description = ?,
                    content = ?,
                    status = ?,
                    visibility = ?,
                    is_featured = ?,
                    display_order = ?,
                    meta_title = ?,
                    meta_keywords = ?,
                    meta_description = ?
                WHERE id = ?
            ");

            $update->execute([
                $form['title'],
                $form['slug'],
                $form['type'],
                $form['short_description'],
                $form['content'],
                $form['status'],
                $form['visibility'],
                $form['is_featured'],
                $form['display_order'],
                $form['meta_title'],
                $form['meta_keywords'],
                $form['meta_description'],
                $policyId
            ]);

            $success = 'Policy updated successfully.';

            $reload = $conn->prepare("
                SELECT 
                    id, title, slug, type, short_description, content, status, visibility,
                    is_featured, display_order, meta_title, meta_keywords, meta_description,
                    created_at, updated_at
                FROM policies
                WHERE id = ?
                LIMIT 1
            ");
            $reload->execute([$policyId]);
            $policy = $reload->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $errors[] = 'Failed to update policy: ' . $ex->getMessage();
        }
    }
}
?>

<style>
    :root{
        --ep-primary:#0d6efd;
        --ep-primary-soft:rgba(13,110,253,.08);
        --ep-success:#198754;
        --ep-success-soft:rgba(25,135,84,.10);
        --ep-warning:#f59e0b;
        --ep-warning-soft:rgba(245,158,11,.12);
        --ep-danger:#dc3545;
        --ep-dark:#111827;
        --ep-text:#1f2937;
        --ep-muted:#6b7280;
        --ep-border:#e5e7eb;
        --ep-bg:#f4f7fb;
        --ep-card:#ffffff;
        --ep-shadow:0 10px 30px rgba(15,23,42,.06);
        --ep-radius:18px;
    }

    body{
        background:var(--ep-bg);
    }

    .policy-edit-page{
        padding:24px;
    }

    .policy-edit-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:20px;
    }

    .policy-edit-title{
        font-size:26px;
        font-weight:800;
        color:var(--ep-text);
        margin-bottom:4px;
    }

    .policy-edit-subtitle{
        font-size:14px;
        color:var(--ep-muted);
        margin:0;
    }

    .policy-edit-card,
    .policy-info-card,
    .policy-stat-card{
        background:var(--ep-card);
        border:1px solid var(--ep-border);
        border-radius:var(--ep-radius);
        box-shadow:var(--ep-shadow);
    }

    .policy-edit-card{
        margin-bottom:20px;
        overflow:hidden;
    }

    .policy-edit-card-header{
        padding:18px 22px;
        border-bottom:1px solid var(--ep-border);
        background:#fcfdff;
    }

    .policy-edit-card-title{
        margin:0;
        font-size:17px;
        font-weight:800;
        color:var(--ep-text);
    }

    .policy-edit-card-body{
        padding:22px;
    }

    .policy-info-card{
        padding:18px;
        position:sticky;
        top:20px;
    }

    .policy-info-title{
        font-size:15px;
        font-weight:800;
        color:var(--ep-text);
        margin-bottom:14px;
    }

    .policy-info-item{
        padding:12px 0;
        border-bottom:1px dashed #e5e7eb;
    }

    .policy-info-item:last-child{
        border-bottom:none;
        padding-bottom:0;
    }

    .policy-info-label{
        display:block;
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#94a3b8;
        margin-bottom:4px;
        font-weight:700;
    }

    .policy-info-value{
        font-size:14px;
        color:var(--ep-text);
        font-weight:700;
        word-break:break-word;
    }

    .form-label{
        font-size:13px;
        font-weight:700;
        color:var(--ep-text);
        margin-bottom:8px;
    }

    .form-control,
    .form-select,
    textarea.form-control{
        min-height:46px;
        border-radius:12px;
        border:1px solid var(--ep-border);
        box-shadow:none;
        font-size:14px;
    }

    textarea.form-control{
        min-height:120px;
    }

    .content-textarea{
        min-height:320px !important;
        line-height:1.7;
    }

    .form-control:focus,
    .form-select:focus,
    textarea.form-control:focus{
        border-color:var(--ep-primary);
        box-shadow:0 0 0 4px rgba(13,110,253,.10);
    }

    .form-text{
        color:var(--ep-muted);
        font-size:12px;
    }

    .switch-box{
        border:1px solid var(--ep-border);
        border-radius:14px;
        background:#fbfcfe;
        padding:14px 16px;
        height:100%;
    }

    .switch-title{
        font-size:14px;
        font-weight:700;
        color:var(--ep-text);
        margin-bottom:4px;
    }

    .switch-text{
        font-size:12px;
        color:var(--ep-muted);
        margin:0;
    }

    .status-badge{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
    }

    .status-published{ background:var(--ep-success-soft); color:var(--ep-success); }
    .status-draft{ background:var(--ep-warning-soft); color:#b45309; }
    .status-archived{ background:#e5e7eb; color:#4b5563; }
    .status-public{ background:#ecfeff; color:#0f766e; }
    .status-private{ background:#fdf2f8; color:#be185d; }
    .status-featured{ background:#fff7ed; color:#c2410c; }

    .btn-edit{
        min-height:46px;
        border-radius:12px;
        font-weight:700;
        padding:0 16px;
    }

    .slug-preview{
        background:#f8fafc;
        border:1px dashed #dbe3ef;
        border-radius:12px;
        padding:12px 14px;
        font-size:13px;
        color:var(--ep-muted);
    }

    .slug-preview strong{
        color:var(--ep-text);
    }

    .seo-preview{
        border:1px solid var(--ep-border);
        border-radius:16px;
        background:#fcfdff;
        padding:16px;
    }

    .seo-preview-title{
        color:#1a0dab;
        font-size:18px;
        margin-bottom:4px;
        font-weight:500;
        line-height:1.3;
    }

    .seo-preview-url{
        font-size:13px;
        color:#188038;
        margin-bottom:6px;
        word-break:break-all;
    }

    .seo-preview-desc{
        font-size:13px;
        color:#4b5563;
        line-height:1.6;
    }

    .alert{
        border:none;
        border-radius:14px;
        box-shadow:var(--ep-shadow);
    }

    .sticky-save-bar{
        position:sticky;
        bottom:14px;
        z-index:5;
        margin-top:20px;
    }

    .sticky-save-inner{
        background:#fff;
        border:1px solid var(--ep-border);
        border-radius:16px;
        box-shadow:var(--ep-shadow);
        padding:14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .sticky-save-note{
        font-size:13px;
        color:var(--ep-muted);
    }

    @media (max-width:991.98px){
        .policy-info-card{
            position:static;
        }
    }

    @media (max-width:767.98px){
        .policy-edit-page{
            padding:16px;
        }

        .policy-edit-title{
            font-size:22px;
        }

        .policy-edit-card-body{
            padding:16px;
        }
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid policy-edit-page">
        <div class="policy-edit-header">
            <div>
                <h4 class="policy-edit-title">✏️ Edit Policy</h4>
                <p class="policy-edit-subtitle">Update policy content, visibility, publishing, order, and SEO details carefully.</p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="list.php" class="btn btn-light btn-edit">
                    <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
                <a href="../../policy.php?slug=<?= urlencode($form['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-edit">
                    <i class="fa fa-eye me-1"></i> Preview
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

        <form method="POST" id="policyEditForm">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="policy-edit-card">
                        <div class="policy-edit-card-header">
                            <h5 class="policy-edit-card-title">Policy Content</h5>
                        </div>
                        <div class="policy-edit-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Policy Title *</label>
                                    <input
                                        type="text"
                                        name="title"
                                        id="title"
                                        class="form-control"
                                        value="<?= e($form['title']) ?>"
                                        placeholder="Enter policy title"
                                    >
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Slug *</label>
                                    <input
                                        type="text"
                                        name="slug"
                                        id="slug"
                                        class="form-control"
                                        value="<?= e($form['slug']) ?>"
                                        placeholder="policy-url-slug"
                                    >
                                    <div class="form-text">Use a short, readable slug. Avoid changing it unnecessarily after publishing.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Policy Type</label>
                                    <select name="type" class="form-select">
                                        <?php foreach ($typeOptions as $key => $label): ?>
                                            <option value="<?= e($key) ?>" <?= $form['type'] === $key ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="slug-preview">
                                        Preview URL:
                                        <strong id="slugPreviewText"><?= e('policy.php?slug=' . $form['slug']) ?></strong>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Short Description</label>
                                    <textarea
                                        name="short_description"
                                        class="form-control"
                                        placeholder="Write a short summary for this policy..."
                                    ><?= e($form['short_description']) ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Policy Content</label>
                                    <textarea
                                        name="content"
                                        class="form-control content-textarea"
                                        placeholder="Write the full policy content here..."
                                    ><?= e($form['content']) ?></textarea>
                                    <div class="form-text">You can later replace this textarea with CKEditor or TinyMCE if needed.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="policy-edit-card">
                        <div class="policy-edit-card-header">
                            <h5 class="policy-edit-card-title">SEO Settings</h5>
                        </div>
                        <div class="policy-edit-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meta Title</label>
                                    <input
                                        type="text"
                                        name="meta_title"
                                        id="meta_title"
                                        class="form-control"
                                        value="<?= e($form['meta_title']) ?>"
                                        placeholder="Meta title for search engines"
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Meta Keywords</label>
                                    <input
                                        type="text"
                                        name="meta_keywords"
                                        class="form-control"
                                        value="<?= e($form['meta_keywords']) ?>"
                                        placeholder="keyword1, keyword2, keyword3"
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea
                                        name="meta_description"
                                        id="meta_description"
                                        class="form-control"
                                        placeholder="Short SEO description..."
                                    ><?= e($form['meta_description']) ?></textarea>
                                </div>

                                <div class="col-12">
                                    <div class="seo-preview">
                                        <div class="seo-preview-title" id="seoPreviewTitle">
                                            <?= e($form['meta_title'] !== '' ? $form['meta_title'] : ($form['title'] !== '' ? $form['title'] : 'Policy Title Preview')) ?>
                                        </div>
                                        <div class="seo-preview-url" id="seoPreviewUrl">
                                            <?= e('https://yourdomain.com/policy.php?slug=' . $form['slug']) ?>
                                        </div>
                                        <div class="seo-preview-desc" id="seoPreviewDesc">
                                            <?= e($form['meta_description'] !== '' ? $form['meta_description'] : 'Your meta description preview will appear here.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sticky-save-bar">
                        <div class="sticky-save-inner">
                            <div class="sticky-save-note">
                                Save carefully. URL slug, publish state, and visibility can affect public access and SEO behavior.
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="list.php" class="btn btn-light btn-edit">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-edit">
                                    <i class="fa fa-save me-1"></i> Update Policy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="policy-info-card">
                        <div class="policy-info-title">Publish & Settings</div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft" <?= $form['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $form['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                    <option value="archived" <?= $form['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="public" <?= $form['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
                                    <option value="private" <?= $form['visibility'] === 'private' ? 'selected' : '' ?>>Private</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Display Order</label>
                                <input
                                    type="number"
                                    name="display_order"
                                    class="form-control"
                                    value="<?= e((string)$form['display_order']) ?>"
                                    min="0"
                                >
                                <div class="form-text">Lower number shows first.</div>
                            </div>
                        </div>

                        <div class="switch-box mb-3">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_featured"
                                    id="is_featured"
                                    <?= !empty($form['is_featured']) ? 'checked' : '' ?>
                                >
                                <label class="form-check-label switch-title" for="is_featured">Featured Policy</label>
                            </div>
                            <p class="switch-text">Highlight this policy in policy lists or important sections.</p>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Current Status</span>
                            <div class="policy-info-value">
                                <?php if ($form['status'] === 'published'): ?>
                                    <span class="status-badge status-published">Published</span>
                                <?php elseif ($form['status'] === 'archived'): ?>
                                    <span class="status-badge status-archived">Archived</span>
                                <?php else: ?>
                                    <span class="status-badge status-draft">Draft</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Current Visibility</span>
                            <div class="policy-info-value">
                                <?php if ($form['visibility'] === 'private'): ?>
                                    <span class="status-badge status-private">Private</span>
                                <?php else: ?>
                                    <span class="status-badge status-public">Public</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Featured</span>
                            <div class="policy-info-value">
                                <?php if (!empty($form['is_featured'])): ?>
                                    <span class="status-badge status-featured">Yes, Featured</span>
                                <?php else: ?>
                                    <span class="status-badge status-archived">Normal</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Created At</span>
                            <div class="policy-info-value">
                                <?= !empty($policy['created_at']) ? date('d M, Y h:i A', strtotime($policy['created_at'])) : 'N/A' ?>
                            </div>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Last Updated</span>
                            <div class="policy-info-value">
                                <?= !empty($policy['updated_at']) ? date('d M, Y h:i A', strtotime($policy['updated_at'])) : 'N/A' ?>
                            </div>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Policy ID</span>
                            <div class="policy-info-value">#<?= (int)$policy['id'] ?></div>
                        </div>

                        <div class="policy-info-item">
                            <span class="policy-info-label">Live Preview Link</span>
                            <div class="policy-info-value">
                                <a href="../../policy.php?slug=<?= urlencode($form['slug']) ?>" target="_blank" class="text-decoration-none">
                                    Open Frontend Preview
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const slugPreviewText = document.getElementById('slugPreviewText');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    const seoPreviewTitle = document.getElementById('seoPreviewTitle');
    const seoPreviewUrl = document.getElementById('seoPreviewUrl');
    const seoPreviewDesc = document.getElementById('seoPreviewDesc');

    function slugify(text) {
        return String(text)
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '') || 'policy-page';
    }

    if (titleInput && slugInput) {
        let slugTouched = false;

        slugInput.addEventListener('input', function () {
            slugTouched = true;
            updateSlugPreview();
            updateSeoPreview();
        });

        titleInput.addEventListener('input', function () {
            if (!slugTouched || slugInput.value.trim() === '') {
                slugInput.value = slugify(titleInput.value);
            }
            updateSlugPreview();
            updateSeoPreview();
        });
    }

    if (metaTitleInput) {
        metaTitleInput.addEventListener('input', updateSeoPreview);
    }

    if (metaDescriptionInput) {
        metaDescriptionInput.addEventListener('input', updateSeoPreview);
    }

    function updateSlugPreview() {
        const slug = slugInput && slugInput.value.trim() ? slugify(slugInput.value) : 'policy-page';
        if (slugPreviewText) {
            slugPreviewText.textContent = 'policy.php?slug=' + slug;
        }
        if (seoPreviewUrl) {
            seoPreviewUrl.textContent = 'https://yourdomain.com/policy.php?slug=' + slug;
        }
    }

    function updateSeoPreview() {
        const title = metaTitleInput && metaTitleInput.value.trim()
            ? metaTitleInput.value.trim()
            : (titleInput && titleInput.value.trim() ? titleInput.value.trim() : 'Policy Title Preview');

        const desc = metaDescriptionInput && metaDescriptionInput.value.trim()
            ? metaDescriptionInput.value.trim()
            : 'Your meta description preview will appear here.';

        if (seoPreviewTitle) seoPreviewTitle.textContent = title;
        if (seoPreviewDesc) seoPreviewDesc.textContent = desc;
        updateSlugPreview();
    }

    updateSeoPreview();
})();
</script>

<?php include '../includes/footer.php'; ?>