<?php
require_once 'includes/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM inventory_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['inventory_user_id'] = $user['id'];
            $_SESSION['inventory_username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card text-center">
        <h3 class="mb-4 fw-bold" style="color: var(--primary);">
            <i class="fas fa-boxes me-2"></i> Inventory Pro
        </h3>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger p-2 mb-4" style="border-radius: 8px; font-size: 0.95rem;"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4 text-start">
                <label class="form-label fw-medium text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Enter your username" required>
                </div>
            </div>
            <div class="mb-4 text-start">
                <label class="form-label fw-medium text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fs-5 mt-2 shadow-sm">
                Login
            </button>
        </form>
        
        <!-- <div class="mt-4">
            <a href="signup.php" class="text-decoration-none text-muted fw-medium">Create an Account</a>
        </div> -->
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
