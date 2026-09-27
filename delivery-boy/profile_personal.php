<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$success = '';
$error = '';

// Auto-add column if not exists
try {
    $conn->query("SELECT address FROM delivery_boys LIMIT 1");
} catch (Exception $e) {
    try { $conn->exec("ALTER TABLE delivery_boys ADD COLUMN address TEXT NULL"); } catch (Exception $e2) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($name) || empty($phone)) {
        $error = "Name and Phone are required.";
    } else {
        $stmt = $conn->prepare("UPDATE delivery_boys SET name = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$name, $phone, $address, $delivery_id])) {
            $success = "Personal information updated successfully.";
            // Update session name if changed
            $_SESSION['delivery_name'] = $name;
        } else {
            $error = "Failed to update information.";
        }
    }
}

// Fetch current
$stmt = $conn->prepare("SELECT * FROM delivery_boys WHERE id = ?");
$stmt->execute([$delivery_id]);
$profile = $stmt->fetch();

?>
<?php include 'includes/header.php'; ?>

<style>
    body { background-color: #f8fafc; }
    .page-container { padding: 24px 20px; padding-bottom: 100px; }
    .page-header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
    .back-btn { color: var(--text-dark); text-decoration: none; font-size: 1.2rem; }
    .page-title { font-weight: 800; font-size: 1.25rem; margin: 0; color: var(--text-dark); }
    
    .form-card { background: white; border-radius: 20px; padding: 24px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); }
    .form-label { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 14px 16px; border: 1px solid #cbd5e1; font-weight: 600; color: var(--text-dark); background: #f8fafc; margin-bottom: 20px; }
    .form-control:focus { border-color: var(--mandal-green); box-shadow: 0 0 0 4px rgba(7,161,88,0.1); background: white; }
    .form-control[readonly] { background: #e2e8f0; color: #64748b; cursor: not-allowed; }
    
    .btn-save { background: var(--mandal-green); color: white; border: none; border-radius: 12px; padding: 16px; width: 100%; font-size: 1rem; font-weight: 700; margin-top: 10px; transition: 0.2s; }
    .btn-save:active { transform: scale(0.98); }
</style>

<div class="page-container">
    <div class="page-header">
        <a href="profile.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">Personal Information</h4>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($profile['name'] ?? '') ?>" required>
            
            <label class="form-label">Email Address (Login ID)</label>
            <input type="email" class="form-control" value="<?= e($profile['email'] ?? '') ?>" readonly title="Email cannot be changed">
            
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" value="<?= e($profile['phone'] ?? '') ?>" required>
            
            <label class="form-label">Full Address</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Enter your full address"><?= e($profile['address'] ?? '') ?></textarea>
            
            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
