<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
            $updateStmt->execute([$token, $expires_at, $admin['id']]);

            $resetLink = BASE_URL . "reset-password.php?token=" . urlencode($token);
            
            // If BASE_URL is not set or empty in local development, construct dynamically
            if (empty(BASE_URL) || BASE_URL === '/') {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $resetLink = $protocol . '://' . $host . $uri . "/reset-password.php?token=" . urlencode($token);
            }

            $mailResult = sendAdminPasswordResetLink($email, $resetLink);

            if ($mailResult['status']) {
                $success = 'If your email is registered as an admin, a password reset link has been sent to it.';
            } else {
                $error = 'Failed to send reset email. Please try again later.';
            }
        } else {
            // We use the same message for security reasons
            $success = 'If your email is registered as an admin, a password reset link has been sent to it.';
        }
    }
}

function old($key) {
    return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Admin</title>
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
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="fas fa-envelope-open-text"></i>
        <h2>Forgot Password</h2>
        <p class="text-muted mb-0">Enter your email to receive a reset link</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-outline-success w-100">Back to Login</a>
        </div>
    <?php else: ?>
        <form method="POST" autocomplete="off">
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Send Reset Link</button>

            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none text-secondary">Back to Login</a>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
