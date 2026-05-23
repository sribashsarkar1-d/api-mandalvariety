<?php
require_once 'includes/config.php';

if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || strlen($name) < 3) {
        $error = 'Name must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $checkStmt = $conn->prepare("
                SELECT id
                FROM users
                WHERE email = ? AND id != ?
                LIMIT 1
            ");
            $checkStmt->execute([$email, $adminId]);

            if ($checkStmt->fetch()) {
                $error = 'This email is already used by another account.';
            } else {
                if ($newPassword !== '' || $confirmPassword !== '') {
                    if ($newPassword !== $confirmPassword) {
                        $error = 'New password and confirm password do not match.';
                    } elseif (strlen($newPassword) < 6) {
                        $error = 'New password must be at least 6 characters.';
                    }
                }

                if ($error === '') {
                    if ($newPassword !== '') {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        // If your signup/login uses pepper, use:
                        // $hashedPassword = password_hash($newPassword . PASSWORD_PEPPER, PASSWORD_DEFAULT);

                        $updateStmt = $conn->prepare("
                            UPDATE users
                            SET name = ?, email = ?, phone = ?, password = ?
                            WHERE id = ? AND role = 'admin'
                        ");
                        $updateStmt->execute([$name, $email, $phone, $hashedPassword, $adminId]);
                    } else {
                        $updateStmt = $conn->prepare("
                            UPDATE users
                            SET name = ?, email = ?, phone = ?
                            WHERE id = ? AND role = 'admin'
                        ");
                        $updateStmt->execute([$name, $email, $phone, $adminId]);
                    }

                    $_SESSION['admin_name'] = $name;
                    $_SESSION['admin_email'] = $email;

                    $success = 'Profile updated successfully.';

                    $stmt = $conn->prepare("
                        SELECT id, name, email, phone, role, is_active, created_at
                        FROM users
                        WHERE id = ? AND role = 'admin'
                        LIMIT 1
                    ");
                    $stmt->execute([$adminId]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            $error = 'Failed to update profile. ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content w-100">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">My Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= e($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= e($success) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    abel class="form-label">Full Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        class="form-control"
                                        value="<?= e($admin['name'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    abel class="form-label">Email Address</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        value="<?= e($admin['email'] ?? '') ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                abel class="form-label">Phone Number</label>
                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="<?= e($admin['phone'] ?? '') ?>"
                                >
                            </div>

                            <hr>

                            <h5 class="mb-3">Change Password</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    abel class="form-label">New Password</label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        class="form-control"
                                        placeholder="Leave blank if no change"
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    abel class="form-label">Confirm Password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        class="form-control"
                                        placeholder="Confirm new password"
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <img src="assets/img/user.png" alt="Admin User" class="rounded-circle mb-3" width="90" height="90">
                        <h5 class="mb-1"><?= e($admin['name'] ?? 'Admin') ?></h5>
                        <p class="text-muted mb-2"><?= e($admin['email'] ?? '') ?></p>
                        <span class="badge bg-success"><?= e(ucfirst($admin['role'] ?? 'admin')) ?></span>

                        <hr>

                        <div class="text-start">
                            <p class="mb-2"><strong>Phone:</strong> <?= e($admin['phone'] ?? 'N/A') ?></p>
                            <p class="mb-2"><strong>Status:</strong> <?= ((int)($admin['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?></p>
                            <p class="mb-0"><strong>Joined:</strong> <?= !empty($admin['created_at']) ? date('d M Y', strtotime($admin['created_at'])) : 'N/A' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>