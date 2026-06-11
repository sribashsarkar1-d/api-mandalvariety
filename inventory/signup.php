<?php
require_once 'includes/config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM inventory_users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO inventory_users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $success = "Account created! You can now login.";
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card text-center">
        <h3 class="mb-4 fw-bold" style="color: var(--primary);">
            <i class="fas fa-user-plus me-2"></i> Create Account
        </h3>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger p-2 mb-4" style="border-radius: 8px; font-size: 0.95rem;"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success p-2 mb-4" style="border-radius: 8px; font-size: 0.95rem;">
                <?= e($success) ?> <br> <a href="login.php" class="fw-bold mt-1 d-inline-block">Login here</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4 text-start">
                <label class="form-label fw-medium text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Choose a username" required>
                </div>
            </div>
            <div class="mb-4 text-start">
                <label class="form-label fw-medium text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Create a password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100 py-2 fs-5 mt-2 shadow-sm">
                Sign Up
            </button>
        </form>
        
        <div class="mt-4">
            <a href="login.php" class="text-decoration-none text-muted fw-medium">Already have an account? Login</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
