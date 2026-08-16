<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];

// Fetch profile data
$stmt = $conn->prepare("SELECT * FROM delivery_boys WHERE id = ?");
$stmt->execute([$delivery_id]);
$profile = $stmt->fetch();

if (!$profile) {
    header("Location: index.php");
    exit;
}

$name = $profile['name'] ?? 'Partner';
$email = $profile['email'] ?? 'Not provided';
// Using isset because phone might not exist in the DB schema, though usually it does for delivery boys.
$phone = $profile['phone'] ?? 'Not provided';
$joined = !empty($profile['created_at']) ? date('M Y', strtotime($profile['created_at'])) : 'Unknown';

?>

<?php include 'includes/header.php'; ?>

<style>
    .profile-container {
        padding: 24px 20px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    
    .page-title {
        font-weight: 700;
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
    }

    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--mandal-green-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        margin: 0 auto 15px;
        box-shadow: 0 10px 20px rgba(7, 161, 88, 0.2);
        border: 4px solid white;
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    
    .profile-badge {
        display: inline-block;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 99px;
        margin-bottom: 8px;
    }

    .info-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.02);
        margin-bottom: 24px;
    }
    
    .info-row {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 1.1rem;
        margin-right: 15px;
    }
    
    .info-content {
        flex: 1;
    }
    .info-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .info-value {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 1rem;
    }

    .btn-logout {
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
        border-radius: 16px;
        padding: 16px;
        font-weight: 700;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.2s;
        text-decoration: none;
    }
    .btn-logout:hover {
        transform: scale(0.98);
        color: #ef4444;
    }
</style>

<div class="profile-container">
    <div class="page-header">
        <h4 class="page-title">Profile</h4>
    </div>

    <div class="profile-header">
        <div class="avatar-large">
            <i class="fa-solid fa-user-astronaut"></i>
        </div>
        <div class="profile-badge"><i class="fa-solid fa-star me-1"></i> Delivery Partner</div>
        <h1 class="profile-name"><?= e($name) ?></h1>
        <div class="text-muted small">Joined <?= e($joined) ?></div>
    </div>

    <div class="info-card">
        <div class="info-row">
            <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
            <div class="info-content">
                <div class="info-label">Email Address</div>
                <div class="info-value"><?= e($email) ?></div>
            </div>
        </div>
        <?php if ($phone !== 'Not provided'): ?>
        <div class="info-row">
            <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
            <div class="info-content">
                <div class="info-label">Phone Number</div>
                <div class="info-value"><?= e($phone) ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-card p-0 overflow-hidden">
        <a href="javascript:void(0)" class="info-row px-4 text-decoration-none">
            <div class="info-icon" style="background: #eef2ff; color: #6366f1;"><i class="fa-solid fa-circle-question"></i></div>
            <div class="info-content">
                <div class="info-value text-dark">Help & Support</div>
            </div>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
        </a>
        <a href="javascript:void(0)" class="info-row px-4 text-decoration-none">
            <div class="info-icon" style="background: #fdf4ff; color: #d946ef;"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="info-content">
                <div class="info-value text-dark">Privacy Policy</div>
            </div>
            <i class="fa-solid fa-chevron-right text-muted small"></i>
        </a>
    </div>

    <a href="logout.php" class="btn-logout mt-4">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>

</div>

<?php include 'includes/footer.php'; ?>
