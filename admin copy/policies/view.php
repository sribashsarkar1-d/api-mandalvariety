<?php
include '../includes/header.php';
include '../includes/sidebar.php';

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$policyId = (int)($_GET['id'] ?? 0);

if ($policyId <= 0) {
    echo '<div class="w-100"><div class="container-fluid p-4"><div class="alert alert-danger">Invalid policy ID.</div></div></div>';
    include '../includes/footer.php';
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            id,
            title,
            slug,
            type,
            short_description,
            content,
            status,
            visibility,
            is_featured,
            display_order,
            meta_title,
            meta_keywords,
            meta_description,
            created_at,
            updated_at
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
    echo '<div class="w-100"><div class="container-fluid p-4"><div class="alert alert-danger">Unable to load policy details.</div></div></div>';
    include '../includes/footer.php';
    exit;
}

$typeLabels = [
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

$typeLabel = $typeLabels[$policy['type'] ?? 'custom'] ?? 'Custom';

$status = $policy['status'] ?? 'draft';
$visibility = $policy['visibility'] ?? 'public';
$isFeatured = !empty($policy['is_featured']);
?>

<style>
    :root{
        --pv-primary:#0d6efd;
        --pv-primary-soft:rgba(13,110,253,.08);
        --pv-success:#198754;
        --pv-success-soft:rgba(25,135,84,.10);
        --pv-warning:#f59e0b;
        --pv-warning-soft:rgba(245,158,11,.12);
        --pv-danger:#dc3545;
        --pv-info:#0ea5e9;
        --pv-info-soft:rgba(14,165,233,.10);
        --pv-text:#1f2937;
        --pv-muted:#6b7280;
        --pv-border:#e5e7eb;
        --pv-bg:#f4f7fb;
        --pv-card:#ffffff;
        --pv-shadow:0 10px 30px rgba(15,23,42,.06);
        --pv-radius:18px;
    }

    body{
        background:var(--pv-bg);
    }

    .policy-view-page{
        padding:24px;
    }

    .policy-view-header{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        flex-wrap:wrap;
        margin-bottom:20px;
    }

    .policy-view-title{
        font-size:28px;
        font-weight:800;
        color:var(--pv-text);
        margin-bottom:8px;
    }

    .policy-view-subtitle{
        font-size:14px;
        color:var(--pv-muted);
        margin:0;
    }

    .policy-card,
    .policy-side-card,
    .policy-stat-card{
        background:var(--pv-card);
        border:1px solid var(--pv-border);
        border-radius:var(--pv-radius);
        box-shadow:var(--pv-shadow);
    }

    .policy-card{
        overflow:hidden;
        margin-bottom:20px;
    }

    .policy-card-header{
        padding:18px 22px;
        border-bottom:1px solid var(--pv-border);
        background:#fcfdff;
    }

    .policy-card-title{
        margin:0;
        font-size:17px;
        font-weight:800;
        color:var(--pv-text);
    }

    .policy-card-body{
        padding:22px;
    }

    .policy-side-card{
        padding:18px;
        position:sticky;
        top:20px;
    }

    .policy-side-title{
        font-size:15px;
        font-weight:800;
        color:var(--pv-text);
        margin-bottom:14px;
    }

    .policy-side-item{
        padding:12px 0;
        border-bottom:1px dashed #e5e7eb;
    }

    .policy-side-item:last-child{
        border-bottom:none;
        padding-bottom:0;
    }

    .policy-side-label{
        display:block;
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#94a3b8;
        margin-bottom:4px;
        font-weight:700;
    }

    .policy-side-value{
        font-size:14px;
        color:var(--pv-text);
        font-weight:700;
        word-break:break-word;
    }

    .policy-badges{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        margin-top:8px;
    }

    .policy-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:7px 11px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
        white-space:nowrap;
    }

    .badge-type{ background:#eef2ff; color:#4338ca; }
    .badge-published{ background:var(--pv-success-soft); color:var(--pv-success); }
    .badge-draft{ background:var(--pv-warning-soft); color:#b45309; }
    .badge-archived{ background:#e5e7eb; color:#4b5563; }
    .badge-public{ background:#ecfeff; color:#0f766e; }
    .badge-private{ background:#fdf2f8; color:#be185d; }
    .badge-featured{ background:#fff7ed; color:#c2410c; }
    .badge-normal{ background:#f3f4f6; color:#4b5563; }

    .policy-highlight-box{
        background:linear-gradient(180deg,#ffffff 0%, #f8fbff 100%);
        border:1px solid #e8eef8;
        border-radius:18px;
        padding:20px;
    }

    .policy-slug-box,
    .policy-seo-preview,
    .policy-meta-box{
        background:#f8fafc;
        border:1px solid #e7edf5;
        border-radius:14px;
        padding:16px;
    }

    .policy-label{
        display:block;
        font-size:12px;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#94a3b8;
        margin-bottom:8px;
        font-weight:700;
    }

    .policy-value{
        font-size:14px;
        color:var(--pv-text);
        line-height:1.7;
        word-break:break-word;
    }

    .policy-main-text{
        font-size:15px;
        color:var(--pv-text);
        line-height:1.85;
        white-space:pre-wrap;
        word-break:break-word;
    }

    .policy-short-desc{
        font-size:15px;
        color:#475467;
        line-height:1.8;
        margin:0;
    }

    .policy-seo-title{
        color:#1a0dab;
        font-size:18px;
        margin-bottom:4px;
        font-weight:500;
        line-height:1.3;
    }

    .policy-seo-url{
        font-size:13px;
        color:#188038;
        margin-bottom:6px;
        word-break:break-all;
    }

    .policy-seo-desc{
        font-size:13px;
        color:#4b5563;
        line-height:1.6;
    }

    .btn-policy{
        min-height:46px;
        border-radius:12px;
        font-weight:700;
        padding:0 16px;
    }

    .action-group{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .empty-data{
        color:var(--pv-muted);
        font-style:italic;
    }

    .alert{
        border:none;
        border-radius:14px;
        box-shadow:var(--pv-shadow);
    }

    @media (max-width:991.98px){
        .policy-side-card{
            position:static;
        }
    }

    @media (max-width:767.98px){
        .policy-view-page{
            padding:16px;
        }

        .policy-view-title{
            font-size:22px;
        }

        .policy-card-body{
            padding:16px;
        }
    }
</style>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <div class="container-fluid policy-view-page">
        <div class="policy-view-header">
            <div>
                <h4 class="policy-view-title">📄 View Policy</h4>
                <p class="policy-view-subtitle">Review policy content, publishing settings, visibility, and SEO metadata in one place.</p>

                <div class="policy-badges">
                    <span class="policy-badge badge-type"><?= e($typeLabel) ?></span>

                    <?php if ($status === 'published'): ?>
                        <span class="policy-badge badge-published">Published</span>
                    <?php elseif ($status === 'archived'): ?>
                        <span class="policy-badge badge-archived">Archived</span>
                    <?php else: ?>
                        <span class="policy-badge badge-draft">Draft</span>
                    <?php endif; ?>

                    <?php if ($visibility === 'private'): ?>
                        <span class="policy-badge badge-private">Private</span>
                    <?php else: ?>
                        <span class="policy-badge badge-public">Public</span>
                    <?php endif; ?>

                    <?php if ($isFeatured): ?>
                        <span class="policy-badge badge-featured">Featured</span>
                    <?php else: ?>
                        <span class="policy-badge badge-normal">Normal</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="action-group">
                <a href="list.php" class="btn btn-light btn-policy">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                <a href="edit.php?id=<?= (int)$policy['id'] ?>" class="btn btn-primary btn-policy">
                    <i class="fa fa-edit me-1"></i> Edit
                </a>
                <a href="../../policy.php?slug=<?= urlencode($policy['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-policy">
                    <i class="fa fa-eye me-1"></i> Frontend Preview
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="policy-card">
                    <div class="policy-card-body">
                        <div class="policy-highlight-box">
                            <span class="policy-label">Policy Title</span>
                            <h2 class="mb-2" style="font-size:28px; font-weight:800; color:#111827;">
                                <?= e($policy['title']) ?>
                            </h2>

                            <?php if (!empty($policy['short_description'])): ?>
                                <p class="policy-short-desc"><?= e($policy['short_description']) ?></p>
                            <?php else: ?>
                                <p class="policy-short-desc empty-data">No short description added.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="policy-card">
                    <div class="policy-card-header">
                        <h5 class="policy-card-title">Content</h5>
                    </div>
                    <div class="policy-card-body">
                        <?php if (!empty($policy['content'])): ?>
                            <div class="policy-main-text"><?= nl2br(e($policy['content'])) ?></div>
                        <?php else: ?>
                            <div class="empty-data">No policy content available.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="policy-card">
                    <div class="policy-card-header">
                        <h5 class="policy-card-title">SEO Preview</h5>
                    </div>
                    <div class="policy-card-body">
                        <div class="policy-seo-preview">
                            <div class="policy-seo-title">
                                <?= e($policy['meta_title'] ?: $policy['title']) ?>
                            </div>
                            <div class="policy-seo-url">
                                <?= e('https://yourdomain.com/policy.php?slug=' . ($policy['slug'] ?: 'policy-page')) ?>
                            </div>
                            <div class="policy-seo-desc">
                                <?= e($policy['meta_description'] ?: ($policy['short_description'] ?: 'No SEO description added.')) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="policy-card">
                    <div class="policy-card-header">
                        <h5 class="policy-card-title">SEO Metadata</h5>
                    </div>
                    <div class="policy-card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="policy-meta-box">
                                    <span class="policy-label">Meta Title</span>
                                    <div class="policy-value">
                                        <?= $policy['meta_title'] !== '' ? e($policy['meta_title']) : '<span class="empty-data">Not added</span>' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="policy-meta-box">
                                    <span class="policy-label">Meta Keywords</span>
                                    <div class="policy-value">
                                        <?= $policy['meta_keywords'] !== '' ? e($policy['meta_keywords']) : '<span class="empty-data">Not added</span>' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="policy-meta-box">
                                    <span class="policy-label">Meta Description</span>
                                    <div class="policy-value">
                                        <?= $policy['meta_description'] !== '' ? e($policy['meta_description']) : '<span class="empty-data">Not added</span>' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="policy-side-card">
                    <div class="policy-side-title">Policy Details</div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Policy ID</span>
                        <div class="policy-side-value">#<?= (int)$policy['id'] ?></div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Slug</span>
                        <div class="policy-side-value"><?= e($policy['slug'] ?: 'N/A') ?></div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Frontend URL</span>
                        <div class="policy-side-value">
                            <a href="../../policy.php?slug=<?= urlencode($policy['slug']) ?>" target="_blank" class="text-decoration-none">
                                <?= e('policy.php?slug=' . ($policy['slug'] ?: 'policy-page')) ?>
                            </a>
                        </div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Type</span>
                        <div class="policy-side-value"><?= e($typeLabel) ?></div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Status</span>
                        <div class="policy-side-value">
                            <?php if ($status === 'published'): ?>
                                <span class="policy-badge badge-published">Published</span>
                            <?php elseif ($status === 'archived'): ?>
                                <span class="policy-badge badge-archived">Archived</span>
                            <?php else: ?>
                                <span class="policy-badge badge-draft">Draft</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Visibility</span>
                        <div class="policy-side-value">
                            <?php if ($visibility === 'private'): ?>
                                <span class="policy-badge badge-private">Private</span>
                            <?php else: ?>
                                <span class="policy-badge badge-public">Public</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Featured</span>
                        <div class="policy-side-value">
                            <?php if ($isFeatured): ?>
                                <span class="policy-badge badge-featured">Yes, Featured</span>
                            <?php else: ?>
                                <span class="policy-badge badge-normal">Normal</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Display Order</span>
                        <div class="policy-side-value"><?= (int)($policy['display_order'] ?? 0) ?></div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Created At</span>
                        <div class="policy-side-value">
                            <?= !empty($policy['created_at']) ? date('d M, Y h:i A', strtotime($policy['created_at'])) : 'N/A' ?>
                        </div>
                    </div>

                    <div class="policy-side-item">
                        <span class="policy-side-label">Last Updated</span>
                        <div class="policy-side-value">
                            <?= !empty($policy['updated_at']) ? date('d M, Y h:i A', strtotime($policy['updated_at'])) : 'N/A' ?>
                        </div>
                    </div>
                </div>

                <div class="policy-card mt-4">
                    <div class="policy-card-header">
                        <h5 class="policy-card-title">Quick URL Info</h5>
                    </div>
                    <div class="policy-card-body">
                        <div class="policy-slug-box">
                            <span class="policy-label">Permalink</span>
                            <div class="policy-value"><?= e('policy.php?slug=' . ($policy['slug'] ?: 'policy-page')) ?></div>
                        </div>
                    </div>
                </div>

                <div class="policy-card">
                    <div class="policy-card-header">
                        <h5 class="policy-card-title">Actions</h5>
                    </div>
                    <div class="policy-card-body">
                        <div class="d-grid gap-2">
                            <a href="edit.php?id=<?= (int)$policy['id'] ?>" class="btn btn-primary btn-policy">
                                <i class="fa fa-edit me-1"></i> Edit This Policy
                            </a>

                            <a href="../../policy.php?slug=<?= urlencode($policy['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-policy">
                                <i class="fa fa-eye me-1"></i> Open Frontend Page
                            </a>

                            <a href="list.php" class="btn btn-light btn-policy">
                                <i class="fa fa-list me-1"></i> Back to Policy List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>