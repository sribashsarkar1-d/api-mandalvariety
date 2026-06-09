<?php
require_once 'includes/config.php';

$error = '';
$success = '';
$validToken = false;
$adminId = null;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $admin = $stmt->fetch();
    } catch (PDOException $e) {
        $admin = false;
        // If columns are missing, ignore and let it fall through to invalid token
    }

    if ($admin) {
        $validToken = true;
        $adminId = $admin['id'];
    } else {
        $error = 'The password reset link is invalid or has expired.';
    }
}

if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $updated = $updateStmt->execute([$hashed_password, $adminId]);

        if ($updated) {
            $success = 'Your password has been reset successfully. You can now login.';
            $validToken = false; // Hide the form
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2f3, #dfe9f3);
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            padding: 32px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-header i {
            font-size: 48px;
            color: #198754;
            margin-bottom: 12px;
        }

        .login-header h2 {
            margin-bottom: 6px;
            font-weight: 700;
        }

        .input-group-text {
            background: #f8f9fa;
        }

        .form-control {
            height: 44px;
        }

        .btn-success {
            padding: 12px;
            font-weight: 600;
        }

        .toggle-password {
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="fas fa-key"></i>
        <h2>Reset Password</h2>
        <p class="text-muted mb-0">Create a new password for your admin account</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-outline-success w-100">Go to Login</a>
        </div>
    <?php elseif ($validToken): ?>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control password-field" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm_password" class="form-control password-field" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>
    <?php else: ?>
        <div class="text-center mt-3">
            <a href="forgot-password.php" class="btn btn-outline-secondary w-100">Request New Link</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(function(button) {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.password-field');
        const icon = this.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
</script>

</body>
</html>
