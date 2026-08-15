<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic mock save
    $success = "Settings saved successfully.";
}

require_once '../includes/header.php';
?>

<div class="custom-card p-3">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <h6 class="fw-bold mb-3 border-bottom pb-2">Store Information</h6>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Store Name</label>
            <input type="text" name="store_name" class="form-control" value="<?= htmlspecialchars(SITE_NAME) ?>">
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Currency Symbol</label>
            <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars(CURRENCY) ?>">
        </div>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Store Address</label>
            <textarea class="form-control" rows="3">123 Super Market, Main Road, City - 400001</textarea>
        </div>
        
        <div class="mb-4">
            <label class="form-label text-muted small">GST Number</label>
            <input type="text" class="form-control" value="22AAAAA0000A1Z5">
        </div>
        
        <h6 class="fw-bold mb-3 border-bottom pb-2">Receipt Settings</h6>
        <div class="mb-3">
            <label class="form-label text-muted small">Footer Message</label>
            <input type="text" class="form-control" value="Thank you for shopping with us! Visit Again ❤">
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Save Settings</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
