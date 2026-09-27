<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$success = '';
$error = '';

// Auto-add columns if not exists
try {
    $conn->query("SELECT aadhar_no, license_no, vehicle_reg FROM delivery_boys LIMIT 1");
} catch (Exception $e) {
    try { 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN aadhar_no VARCHAR(20) NULL"); 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN license_no VARCHAR(20) NULL"); 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN vehicle_reg VARCHAR(20) NULL"); 
    } catch (Exception $e2) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aadhar = $_POST['aadhar'] ?? '';
    $license = $_POST['license'] ?? '';
    $vehicle = $_POST['vehicle'] ?? '';
    
    $stmt = $conn->prepare("UPDATE delivery_boys SET aadhar_no = ?, license_no = ?, vehicle_reg = ? WHERE id = ?");
    if ($stmt->execute([$aadhar, $license, $vehicle, $delivery_id])) {
        $success = "Documents information updated successfully.";
    } else {
        $error = "Failed to update information.";
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
    .form-control { border-radius: 12px; padding: 14px 16px; border: 1px solid #cbd5e1; font-weight: 600; color: var(--text-dark); background: #f8fafc; margin-bottom: 20px; text-transform: uppercase; }
    .form-control:focus { border-color: var(--mandal-green); box-shadow: 0 0 0 4px rgba(7,161,88,0.1); background: white; }
    
    .btn-save { background: var(--mandal-green); color: white; border: none; border-radius: 12px; padding: 16px; width: 100%; font-size: 1rem; font-weight: 700; margin-top: 10px; transition: 0.2s; }
    .btn-save:active { transform: scale(0.98); }
    
    .info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; color: #1e3a8a; margin-bottom: 20px; font-weight: 600; }
</style>

<div class="page-container">
    <div class="page-header">
        <a href="profile.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">Documents</h4>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <i class="fa-solid fa-shield-halved me-2"></i> Your document details are securely stored.
    </div>

    <div class="form-card">
        <form method="POST">
            <label class="form-label">Aadhar Number</label>
            <input type="text" name="aadhar" class="form-control" value="<?= e($profile['aadhar_no'] ?? '') ?>" placeholder="XXXX XXXX XXXX">
            
            <label class="form-label">Driving License Number</label>
            <input type="text" name="license" class="form-control" value="<?= e($profile['license_no'] ?? '') ?>" placeholder="DL-XXXXXXXXXXXXX">
            
            <label class="form-label">Vehicle Registration (RC)</label>
            <input type="text" name="vehicle" class="form-control" value="<?= e($profile['vehicle_reg'] ?? '') ?>" placeholder="WB-XX-XXXX">
            
            <button type="submit" class="btn-save">Save Documents</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
