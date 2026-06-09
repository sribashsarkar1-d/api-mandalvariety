<?php
require_once 'includes/config.php';

$error = '';

if (isset($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'admin') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("
            SELECT id, name, email, password, role, is_active
            FROM users
            WHERE email = ? AND role = 'admin'
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $error = 'Invalid email or password.';
        } elseif ((int)$admin['is_active'] !== 1) {
            $error = 'Your admin account is inactive.';
        } else {
            $loginPassword = $password;
            // If signup used pepper:
            // $loginPassword = $password . PASSWORD_PEPPER;

            if (password_verify($loginPassword, $admin['password'])) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

if (!function_exists('old')) {
    function old($key) {
        return htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Ecommerce</title>
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
        <i class="fas fa-user-shield"></i>
        <h2>Admin Login</h2>
        <p class="text-muted mb-0">Login only with admin account</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control password-field" required>
                <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100">Login</button>

        <div class="d-flex justify-content-between mt-3">
            <a href="forgot-password.php" class="text-decoration-none text-secondary">Forgot Password?</a>
            
        </div>
    </form>
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