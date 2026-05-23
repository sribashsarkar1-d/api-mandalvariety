<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || strlen($name) < 3) {
        $error = 'Name must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($phone === '' || !preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone must be 10 digits.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $role              = 'admin';
            $address           = null;
            $pincode           = null;
            $is_active         = 1;
            $otp               = null;
            $otp_expires_at    = null;
            $is_verified       = 1;
            $email_verified_at = date('Y-m-d H:i:s');

            $insertStmt = $pdo->prepare("
                INSERT INTO users (
                    name,
                    email,
                    phone,
                    password,
                    role,
                    address,
                    pincode,
                    is_active,
                    created_at,
                    updated_at,
                    otp,
                    otp_expires_at,
                    is_verified,
                    email_verified_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?
                )
            ");

            $inserted = $insertStmt->execute([
                $name,
                $email,
                $phone,
                $hashed_password,
                $role,
                $address,
                $pincode,
                $is_active,
                $otp,
                $otp_expires_at,
                $is_verified,
                $email_verified_at
            ]);

            if ($inserted) {
                $success = 'Admin account created successfully! <a href="index.php">Login now</a>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Admin Signup - Ecommerce</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #e4efe9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .signup-card {
            width: 100%;
            max-width: 700px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .signup-header i {
            font-size: 48px;
            color: #198754;
            margin-bottom: 10px;
        }

        .signup-header h2 {
            margin-bottom: 5px;
            font-weight: 700;
        }

        .input-group-text {
            background: #f8f9fa;
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

<div class="signup-card">
    <div class="signup-header">
        <i class="fas fa-user-shield"></i>
        <h2>Create Admin Account</h2>
        <p class="text-muted mb-0">Only admin role will be created from this page</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php else: ?>
        <form method="POST" autocomplete="off">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="name" class="form-control" value="<?php echo old('name'); ?>" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="<?php echo old('email'); ?>" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="phone" class="form-control" value="<?php echo old('phone'); ?>" maxlength="10" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        <input type="text" class="form-control" value="admin" readonly>
                    </div>
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

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm_password" class="form-control password-field" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">
                Create Admin Account
            </button>

            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
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