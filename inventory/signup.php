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
        // Check if username exists
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
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card mt-5">
            <div class="card-header bg-dark text-white text-center">
                <h4>Create Inventory Account</h4>
            </div>
            <div class="card-body">
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if(!empty($success)): ?>
                    <div class="alert alert-success"><?= e($success) ?> <a href="login.php">Login here</a></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Sign Up</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="login.php">Already have an account? Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
