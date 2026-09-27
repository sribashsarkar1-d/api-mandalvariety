<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_available'])) {
    $new_status = (int)$_POST['is_available'];
    $stmt = $conn->prepare("UPDATE delivery_boys SET is_available = ? WHERE id = ?");
    $stmt->execute([$new_status, $delivery_id]);
    header("Location: availability.php");
    exit;
}

$stmt = $conn->prepare("SELECT is_available FROM delivery_boys WHERE id = ?");
$stmt->execute([$delivery_id]);
$boy = $stmt->fetch();
$is_available = (int)$boy['is_available'] === 1;

?>
<?php include 'includes/header.php'; ?>

<style>
    body { background-color: #f8fafc; }
    
    .page-header {
        display: flex;
        align-items: center;
        padding: 20px;
        background: #fff;
    }
    .back-btn { color: var(--text-dark); font-size: 1.2rem; margin-right: 15px; text-decoration: none; }
    .page-title { font-size: 18px; font-weight: 700; margin: 0; }

    .availability-container {
        padding: 40px 20px;
        text-align: center;
    }

    .illustration-box {
        margin-bottom: 30px;
    }
    
    .illustration-box i {
        font-size: 120px;
        color: var(--mandal-green);
    }

    .status-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .status-sub {
        font-size: 15px;
        color: #64748b;
        margin-bottom: 40px;
        line-height: 1.5;
    }

    .action-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        cursor: pointer;
    }

    .action-card.active {
        background: var(--mandal-green);
        color: white;
    }
    
    .action-card.active .card-text { color: white; }

    .card-text {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
    }

    /* Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: rgba(255,255,255,0.4);
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    
    .action-card:not(.active) .slider {
        background-color: transparent;
    }
    .action-card:not(.active) .slider:before {
        display: none;
    }

</style>

<div class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
    <h1 class="page-title">Availability</h1>
</div>

<div class="availability-container">
    <div class="illustration-box">
        <i class="fa-solid fa-motorcycle"></i>
    </div>
    
    <div class="status-title"><?= $is_available ? 'You are Online' : 'You are Offline' ?></div>
    <div class="status-sub">
        <?= $is_available ? 'You will receive new delivery assignments' : 'Go online to start receiving delivery assignments' ?>
    </div>
    
    <form method="POST" id="formOnline">
        <input type="hidden" name="is_available" value="1">
        <div class="action-card <?= $is_available ? 'active' : '' ?>" onclick="document.getElementById('formOnline').submit()">
            <div class="card-text">Online</div>
            <?php if ($is_available): ?>
            <label class="switch">
                <input type="checkbox" checked disabled>
                <span class="slider"></span>
            </label>
            <?php endif; ?>
        </div>
    </form>
    
    <form method="POST" id="formOffline">
        <input type="hidden" name="is_available" value="0">
        <div class="action-card <?= !$is_available ? 'active' : '' ?>" style="<?= !$is_available ? 'background:#64748b; color:white;' : '' ?>" onclick="document.getElementById('formOffline').submit()">
            <div class="card-text" style="<?= !$is_available ? 'color:white;' : '' ?>">Go Offline</div>
            <?php if (!$is_available): ?>
            <label class="switch">
                <input type="checkbox" checked disabled>
                <span class="slider" style="background:rgba(255,255,255,0.4);"></span>
            </label>
            <?php endif; ?>
        </div>
    </form>

</div>

<?php include 'includes/footer.php'; ?>
