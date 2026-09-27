<?php
require_once 'includes/config.php';
checkDeliveryLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save settings to session (in a real app, save to DB or localStorage)
    $_SESSION['app_notifications'] = isset($_POST['notifications']) ? '1' : '0';
    $_SESSION['app_dark_mode'] = isset($_POST['dark_mode']) ? '1' : '0';
    
    header("Location: profile_settings.php?saved=1");
    exit;
}

$notif_checked = ($_SESSION['app_notifications'] ?? '1') === '1' ? 'checked' : '';
$dark_checked = ($_SESSION['app_dark_mode'] ?? '0') === '1' ? 'checked' : '';
$saved = isset($_GET['saved']);

?>
<?php include 'includes/header.php'; ?>

<style>
    body { background-color: #f8fafc; }
    .page-container { padding: 24px 20px; padding-bottom: 100px; }
    .page-header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
    .back-btn { color: var(--text-dark); text-decoration: none; font-size: 1.2rem; }
    .page-title { font-weight: 800; font-size: 1.25rem; margin: 0; color: var(--text-dark); }
    
    .settings-list { background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); overflow: hidden; }
    
    .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #f1f5f9; }
    .setting-item:last-child { border-bottom: none; }
    
    .setting-info { display: flex; align-items: center; gap: 15px; }
    .setting-icon { width: 40px; height: 40px; border-radius: 12px; background: #f8fafc; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 1.1rem; }
    
    .setting-text { font-weight: 700; color: var(--text-dark); font-size: 1rem; }
    .setting-desc { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; margin-top: 2px; }

    /* Switch */
    .switch { position: relative; display: inline-block; width: 50px; height: 28px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    input:checked + .slider { background-color: var(--mandal-green); }
    input:checked + .slider:before { transform: translateX(22px); }

    .btn-save { background: var(--mandal-green); color: white; border: none; border-radius: 12px; padding: 16px; width: 100%; font-size: 1rem; font-weight: 700; margin-top: 24px; transition: 0.2s; }
    .btn-save:active { transform: scale(0.98); }
</style>

<div class="page-container">
    <div class="page-header">
        <a href="profile.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">App Settings</h4>
    </div>

    <?php if ($saved): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4 fw-bold"><i class="fa-solid fa-check-circle me-2"></i>Settings saved successfully!</div>
    <?php endif; ?>

    <form method="POST">
        <div class="settings-list">
            <div class="setting-item">
                <div class="setting-info">
                    <div class="setting-icon" style="color: #3b82f6; background: #eff6ff;"><i class="fa-solid fa-bell"></i></div>
                    <div>
                        <div class="setting-text">Push Notifications</div>
                        <div class="setting-desc">Get alerts for new orders</div>
                    </div>
                </div>
                <label class="switch">
                    <input type="checkbox" name="notifications" <?= $notif_checked ?>>
                    <span class="slider"></span>
                </label>
            </div>
            
            <div class="setting-item">
                <div class="setting-info">
                    <div class="setting-icon" style="color: #6366f1; background: #eef2ff;"><i class="fa-solid fa-moon"></i></div>
                    <div>
                        <div class="setting-text">Dark Mode</div>
                        <div class="setting-desc">Eye-soothing dark theme</div>
                    </div>
                </div>
                <label class="switch">
                    <input type="checkbox" name="dark_mode" <?= $dark_checked ?>>
                    <span class="slider"></span>
                </label>
            </div>
            
            <div class="setting-item">
                <div class="setting-info">
                    <div class="setting-icon" style="color: #f59e0b; background: #fef3c7;"><i class="fa-solid fa-location-arrow"></i></div>
                    <div>
                        <div class="setting-text">Location Tracking</div>
                        <div class="setting-desc">Always on while active</div>
                    </div>
                </div>
                <!-- Forced active since it's a delivery app -->
                <label class="switch" style="opacity: 0.6; pointer-events: none;">
                    <input type="checkbox" checked disabled>
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn-save">Save Settings</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
