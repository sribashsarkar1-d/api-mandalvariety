<?php
require_once 'includes/config.php';

if (isset($_SESSION['delivery_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, is_active FROM delivery_boys WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $delivery_boy = $stmt->fetch();

        if (!$delivery_boy) {
            $error = 'Invalid email or password.';
        } elseif ((int)$delivery_boy['is_active'] !== 1) {
            $error = 'Your account is inactive. Contact admin.';
        } else {
            if (password_verify($password, $delivery_boy['password'])) {
                session_regenerate_id(true);
                $_SESSION['delivery_id'] = $delivery_boy['id'];
                $_SESSION['delivery_name'] = $delivery_boy['name'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
    .auth-container {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .auth-card {
        width: 100%;
        max-width: 400px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        padding: 30px;
        border: 1px solid #e2e8f0;
    }
    
    .auth-title {
        text-align: center;
        margin-bottom: 25px;
    }
    
    .auth-title i {
        font-size: 40px;
        color: #10b981;
        margin-bottom: 10px;
    }
    
    .form-control-premium {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 12px 15px;
        background: #f8fafc;
    }
    
    .form-control-premium:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        background: white;
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-title">
            <i class="fa-solid fa-motorcycle"></i>
            <h4 class="fw-bold">Partner Login</h4>
            <p class="text-muted small">Welcome back! Please login to continue.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-3 small"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                <input type="email" name="email" class="form-control form-control-premium" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">PASSWORD</label>
                <input type="password" name="password" class="form-control form-control-premium" required>
            </div>

            <button type="submit" class="btn-premium mb-3">Login</button>
            
            <div class="text-center small">
                Don't have an account? <a href="signup.php" class="text-success text-decoration-none fw-bold">Sign up here</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
