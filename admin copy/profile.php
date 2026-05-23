<?php
require_once 'includes/config.php';

if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

$adminId = (int) $_SESSION['admin_id'];
$error = '';
$success = '';

try {
    $stmt = $conn->prepare("
        SELECT id, name, email, phone, role, is_active, created_at
        FROM users
        WHERE id = ? AND role = 'admin'
        LIMIT 1
    ");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die('Error fetching profile: ' . $e->getMessage());
}

$formName = $admin['name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_name') {
    $formName = trim($_POST['name'] ?? '');

    if ($formName === '' || mb_strlen($formName) < 3) {
        $error = 'Name must be at least 3 characters.';
    } else {
        try {
            $updateStmt = $conn->prepare("
                UPDATE users
                SET name = ?, updated_at = NOW()
                WHERE id = ? AND role = 'admin'
            ");
            $updateStmt->execute([$formName, $adminId]);

            $_SESSION['admin_name'] = $formName;
            $success = 'Name updated successfully.';

            $stmt = $conn->prepare("
                SELECT id, name, email, phone, role, is_active, created_at
                FROM users
                WHERE id = ? AND role = 'admin'
                LIMIT 1
            ");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $formName = $admin['name'] ?? '';
        } catch (PDOException $e) {
            $error = 'Failed to update name.';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<style>
    .profile-page {
        background: linear-gradient(180deg, #f8fafc 0%, #eef4ff 100%);
        min-height: 100vh;
    }

    .profile-hero-card,
    .profile-info-card,
    .profile-side-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .profile-hero-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
        color: #fff;
    }

    .profile-hero-card .card-body {
        padding: 28px;
    }

    .profile-avatar-wrap {
        width: 108px;
        height: 108px;
        border-radius: 50%;
        padding: 4px;
        background: rgba(255,255,255,0.18);
        margin-bottom: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        background: #fff;
    }

    .profile-name {
        font-size: 1.7rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .profile-email {
        color: rgba(255,255,255,0.82);
        margin-bottom: 16px;
    }

    .profile-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.16);
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 600;
        font-size: .9rem;
    }

    .profile-info-card .card-header,
    .profile-side-card .card-header {
        background: #fff;
        border-bottom: 1px solid #eef2f7;
        padding: 18px 22px;
    }

    .profile-info-card .card-body,
    .profile-side-card .card-body {
        padding: 22px;
    }

    .profile-section-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .profile-item {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 16px;
        height: 100%;
    }

    .profile-label {
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 8px;
    }

    .profile-value {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
        word-break: break-word;
    }

    .profile-muted {
        color: #64748b;
        font-weight: 500;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: .88rem;
        font-weight: 700;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .role-pill {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .profile-side-card .mini-stat {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 16px;
    }

    .profile-side-card .mini-stat + .mini-stat {
        margin-top: 14px;
    }

    .mini-stat-title {
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
    }

    .mini-stat-value {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .btn-soft-primary {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .btn-soft-primary:hover {
        background: #dbeafe;
        color: #1e40af;
    }

    .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
        color: #fff;
        border-bottom: 0;
        padding: 18px 22px;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .modal-body {
        padding: 24px 22px;
    }

    .modal-footer {
        border-top: 1px solid #eef2f7;
        padding: 16px 22px;
    }

    .form-label {
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .form-control {
        height: 48px;
        border-radius: 14px;
        border: 1px solid #dbe2ea;
        box-shadow: none;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    @media (max-width: 991.98px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .profile-hero-card .card-body,
        .profile-info-card .card-body,
        .profile-side-card .card-body {
            padding: 16px;
        }

        .profile-name {
            font-size: 1.35rem;
        }

        .profile-avatar-wrap {
            width: 92px;
            height: 92px;
        }

        .profile-avatar {
            width: 84px;
            height: 84px;
        }
    }
</style>

<div class="main-content w-100 profile-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="card profile-hero-card mb-4">
            <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="profile-avatar-wrap">
                        <img src="uploads/profile/avtar.jpg" alt="Admin User" class="profile-avatar">
                    </div>

                    <div>
                        <div class="profile-name"><?= e($admin['name'] ?? 'Admin') ?></div>
                        <div class="profile-email"><?= e($admin['email'] ?? 'N/A') ?></div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="profile-badge">
                                <i class="fa-solid fa-user-shield"></i>
                                <?= e(ucfirst($admin['role'] ?? 'admin')) ?>
                            </span>

                            <?php if ((int)($admin['is_active'] ?? 0) === 1): ?>
                                <span class="profile-badge">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Active Account
                                </span>
                            <?php else: ?>
                                <span class="profile-badge">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    Inactive Account
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div>
                    <button class="btn btn-light px-4" data-bs-toggle="modal" data-bs-target="#editNameModal">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Edit Name
                    </button>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger border-0 shadow-sm"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success border-0 shadow-sm"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card profile-info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="profile-section-title">Admin Information</h5>
                        <button class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#editNameModal">
                            <i class="fa-solid fa-pen me-1"></i> Edit Name
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="profile-grid">
                            <div class="profile-item">
                                <div class="profile-label">Full Name</div>
                                <div class="profile-value"><?= e($admin['name'] ?? 'N/A') ?></div>
                            </div>

                            <div class="profile-item">
                                <div class="profile-label">Email Address</div>
                                <div class="profile-value"><?= e($admin['email'] ?? 'N/A') ?></div>
                            </div>

                            <div class="profile-item">
                                <div class="profile-label">Phone Number</div>
                                <div class="profile-value"><?= e($admin['phone'] ?: 'N/A') ?></div>
                            </div>

                            <div class="profile-item">
                                <div class="profile-label">Role</div>
                                <div class="profile-value">
                                    <span class="status-pill role-pill"><?= e(ucfirst($admin['role'] ?? 'admin')) ?></span>
                                </div>
                            </div>

                            <div class="profile-item">
                                <div class="profile-label">Account Status</div>
                                <div class="profile-value">
                                    <?php if ((int)($admin['is_active'] ?? 0) === 1): ?>
                                        <span class="status-pill status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-pill status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="profile-item">
                                <div class="profile-label">Joined Date</div>
                                <div class="profile-value">
                                    <?= !empty($admin['created_at']) ? e(date('d M Y', strtotime($admin['created_at']))) : 'N/A' ?>
                                </div>
                            </div>

                            <div class="profile-item" style="grid-column: 1 / -1;">
                                <div class="profile-label">Account Access</div>
                                <div class="profile-value profile-muted">
                                    This is a read-only admin profile view. Only the full name can be updated from the edit button.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card profile-side-card">
                    <div class="card-header">
                        <h5 class="profile-section-title">Quick Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="mini-stat">
                            <div class="mini-stat-title">Admin ID</div>
                            <div class="mini-stat-value">#<?= (int)($admin['id'] ?? 0) ?></div>
                        </div>

                        <div class="mini-stat">
                            <div class="mini-stat-title">Login Role</div>
                            <div class="mini-stat-value"><?= e(ucfirst($admin['role'] ?? 'admin')) ?></div>
                        </div>

                        <div class="mini-stat">
                            <div class="mini-stat-title">Registered Email</div>
                            <div class="mini-stat-value"><?= e($admin['email'] ?? 'N/A') ?></div>
                        </div>

                        <div class="mini-stat">
                            <div class="mini-stat-title">Contact Number</div>
                            <div class="mini-stat-value"><?= e($admin['phone'] ?: 'N/A') ?></div>
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editNameModal">
                                <i class="fa-solid fa-user-pen me-2"></i>Change Admin Name
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editNameModal" tabindex="-1" aria-labelledby="editNameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="editNameModalLabel">Edit Admin Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_name">

                    <div class="mb-3">
                        abel class="form-label">Current Name</label>
                        <input type="text" class="form-control" value="<?= e($admin['name'] ?? '') ?>" readonly>
                    </div>

                    <div class="mb-0">
                        abel class="form-label">New Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?= e($formName) ?>"
                            placeholder="Enter new admin name"
                            required
                        >
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Name</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($error): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editNameModal = new bootstrap.Modal(document.getElementById('editNameModal'));
    editNameModal.show();
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>