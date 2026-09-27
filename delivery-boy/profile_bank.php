<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$success = '';
$error = '';

// Auto-add columns if not exists
try {
    $conn->query("SELECT bank_name, account_name, account_number, ifsc_code FROM delivery_boys LIMIT 1");
} catch (Exception $e) {
    try { 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN bank_name VARCHAR(100) NULL"); 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN account_name VARCHAR(100) NULL"); 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN account_number VARCHAR(50) NULL"); 
        $conn->exec("ALTER TABLE delivery_boys ADD COLUMN ifsc_code VARCHAR(20) NULL"); 
    } catch (Exception $e2) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = $_POST['bank_name'] ?? '';
    $account_name = $_POST['account_name'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $ifsc_code = $_POST['ifsc_code'] ?? '';
    
    $stmt = $conn->prepare("UPDATE delivery_boys SET bank_name = ?, account_name = ?, account_number = ?, ifsc_code = ? WHERE id = ?");
    if ($stmt->execute([$bank_name, $account_name, $account_number, $ifsc_code, $delivery_id])) {
        $success = "Bank details updated successfully.";
    } else {
        $error = "Failed to update bank details.";
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
    
    .btn-save { background: var(--mandal-green); color: white; border: none; border-radius: 12px; padding: 16px; width: 100%; font-size: 1rem; font-weight: 700; margin-top: 10px; transition: 0.2s; }
    .btn-save:active { transform: scale(0.98); }
</style>

<div class="page-container">
    <div class="page-header">
        <a href="profile.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">Bank Details</h4>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="<?= e($profile['bank_name'] ?? '') ?>" placeholder="e.g. State Bank of India">
            
            <label class="form-label">Account Holder Name</label>
            <input type="text" name="account_name" class="form-control" value="<?= e($profile['account_name'] ?? '') ?>" placeholder="As per bank records">
            
            <label class="form-label">Account Number</label>
            <input type="text" name="account_number" class="form-control" value="<?= e($profile['account_number'] ?? '') ?>" placeholder="Enter account number">
            
            <label class="form-label">IFSC Code</label>
            <input type="text" name="ifsc_code" class="form-control" value="<?= e($profile['ifsc_code'] ?? '') ?>" placeholder="e.g. SBIN0001234" style="text-transform:uppercase;">
            
            <button type="submit" class="btn-save">Save Bank Details</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
