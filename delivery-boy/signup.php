<?php
require_once 'includes/config.php';

if (isset($_SESSION['delivery_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $vehicle_type = trim($_POST['vehicle_type'] ?? '');
    $vehicle_number = trim($_POST['vehicle_number'] ?? '');

    if ($name === '' || $email === '' || $phone === '' || $password === '' || $vehicle_type === '' || $vehicle_number === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email or phone already exists
        $stmt = $conn->prepare("SELECT id FROM delivery_boys WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $error = 'Email or Phone number is already registered.';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO delivery_boys (name, email, phone, password, vehicle_type, vehicle_number)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            try {
                $stmt->execute([$name, $email, $phone, $hashedPassword, $vehicle_type, $vehicle_number]);
                $success = 'Registration successful! You can now login.';
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
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
        max-width: 450px;
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
            <h4 class="fw-bold">Partner Signup</h4>
            <p class="text-muted small">Join our delivery fleet today</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-3 small"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success rounded-3 small"><i class="fa-solid fa-circle-check me-2"></i><?= e($success) ?></div>
        <?php else: ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">FULL NAME</label>
                <input type="text" name="name" class="form-control form-control-premium" value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                <input type="email" name="email" class="form-control form-control-premium" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">PHONE NUMBER</label>
                <input type="text" name="phone" class="form-control form-control-premium" value="<?= isset($_POST['phone']) ? e($_POST['phone']) : '' ?>" required>
            </div>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold text-muted">VEHICLE TYPE</label>
                    <select name="vehicle_type" class="form-control form-control-premium" required>
                        <option value="">Select...</option>
                        <option value="Bike">Bike</option>
                        <option value="Scooter">Scooter</option>
                        <option value="Bicycle">Bicycle</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold text-muted">VEHICLE NO.</label>
                    <input type="text" name="vehicle_number" class="form-control form-control-premium" placeholder="e.g. WB74A1234" value="<?= isset($_POST['vehicle_number']) ? e($_POST['vehicle_number']) : '' ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">PASSWORD</label>
                <input type="password" name="password" class="form-control form-control-premium" required>
            </div>

            <button type="submit" class="btn-premium mb-3">Create Account</button>
            
            <div class="text-center small">
                Already have an account? <a href="login.php" class="text-success text-decoration-none fw-bold">Login here</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
