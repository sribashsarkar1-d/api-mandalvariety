<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = (int)$_SESSION['delivery_id'];

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
$phone = $profile['phone'] ?? 'Not provided';
$joined = !empty($profile['created_at']) ? date('M Y', strtotime($profile['created_at'])) : 'Recent';
$partner_code = 'DB' . str_pad($delivery_id, 4, '0', STR_PAD_LEFT);
?>

<?php include 'includes/header.php'; ?>

<style>
    .profile-page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .btn-back-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-text);
        text-decoration: none;
        font-size: 14px;
        transition: background 0.15s;
    }

    .btn-back-circle:active {
        background: var(--light-green);
    }

    .profile-page-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
    }

    /* Profile Card Hero */
    .profile-hero-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 24px 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .avatar-wrapper {
        position: relative;
        width: 86px;
        height: 86px;
        margin: 0 auto 14px;
    }

    .avatar-circle-hero {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: var(--light-green);
        border: 3px solid #FFFFFF;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .avatar-circle-hero svg {
        width: 100%;
        height: 100%;
    }

    .verified-badge {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--primary-green);
        color: #FFFFFF;
        border: 2.5px solid #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    .profile-name {
        font-size: 19px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .profile-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--light-green);
        color: var(--primary-green);
        font-size: 12px;
        font-weight: 700;
        padding: 3px 12px;
        border-radius: var(--pill-radius);
        margin-bottom: 12px;
    }

    .contact-info-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        border-top: 1px solid var(--divider-color);
        padding-top: 14px;
        font-size: 13px;
        color: var(--secondary-text);
    }

    .contact-info-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .contact-info-item i {
        color: var(--primary-green);
        font-size: 13px;
        width: 16px;
        text-align: center;
    }

    .contact-info-item span {
        color: var(--primary-text);
        font-weight: 600;
    }

    /* Menu List */
    .menu-card-list {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .menu-item-row {
        display: flex;
        align-items: center;
        padding: 15px 18px;
        border-bottom: 1px solid var(--divider-color);
        text-decoration: none;
        color: var(--primary-text);
        transition: background 0.15s ease;
    }

    .menu-item-row:last-child {
        border-bottom: none;
    }

    .menu-item-row:active {
        background: #F8FAFC;
    }

    .menu-item-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--light-green);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        margin-right: 14px;
        flex-shrink: 0;
    }

    .menu-item-text {
        flex: 1;
        font-size: 14px;
        font-weight: 600;
    }

    .menu-item-chevron {
        color: #CBD5E1;
        font-size: 13px;
    }

    /* Logout Button */
    .btn-logout-card {
        background: #FFFFFF;
        border: 1px solid #FEE2E2;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        padding: 15px 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #DC2626;
        font-size: 14.5px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .btn-logout-card:active {
        background: #FEF2F2;
    }
</style>

<div class="page-content">

    <div class="profile-page-header">
        <a href="index.php" class="btn-back-circle">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="profile-page-title">My Profile</h2>
    </div>

    <!-- Hero Card -->
    <div class="profile-hero-card">
        <div class="avatar-wrapper">
            <div class="avatar-circle-hero">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="24" cy="24" r="24" fill="#E8F7EF"/>
                    <circle cx="24" cy="18" r="7.5" fill="#FED7AA"/>
                    <path d="M16.5 16C16.5 12 19.8 9.5 24 9.5C28.2 9.5 31.5 12 31.5 16L34 17C34 17 31 18.5 26 18.5C21 18.5 14 17 14 17L16.5 16Z" fill="#008044"/>
                    <path d="M14 17C18 18 23 18.2 27 17.5L28 18.5C23 19.5 17 19 13.5 18L14 17Z" fill="#005A30"/>
                    <path d="M12.5 38C13 30.5 18 26 24 26C30 26 35 30.5 35.5 38H12.5Z" fill="#009B55"/>
                    <path d="M21 26L24 29L27 26H21Z" fill="#FFFFFF"/>
                </svg>
            </div>
            <div class="verified-badge"><i class="fa-solid fa-check"></i></div>
        </div>

        <h1 class="profile-name"><?= e($name) ?></h1>
        <div class="profile-role-badge">
            <i class="fa-solid fa-motorcycle"></i>
            <span>Delivery Partner &bull; <?= e($partner_code) ?></span>
        </div>

        <div class="contact-info-list">
            <div class="contact-info-item">
                <i class="fa-solid fa-phone"></i>
                <span><?= e($phone) ?></span>
            </div>
            <div class="contact-info-item">
                <i class="fa-solid fa-envelope"></i>
                <span><?= e($email) ?></span>
            </div>
        </div>
    </div>

    <!-- Profile Options Menu -->
    <div class="menu-card-list">
        <a href="profile_personal.php" class="menu-item-row">
            <div class="menu-item-icon-box">
                <i class="fa-regular fa-user"></i>
            </div>
            <span class="menu-item-text">Personal Information</span>
            <i class="fa-solid fa-chevron-right menu-item-chevron"></i>
        </a>
        <a href="profile_documents.php" class="menu-item-row">
            <div class="menu-item-icon-box">
                <i class="fa-regular fa-file-lines"></i>
            </div>
            <span class="menu-item-text">Documents</span>
            <i class="fa-solid fa-chevron-right menu-item-chevron"></i>
        </a>
        <a href="profile_bank.php" class="menu-item-row">
            <div class="menu-item-icon-box">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <span class="menu-item-text">Bank Information</span>
            <i class="fa-solid fa-chevron-right menu-item-chevron"></i>
        </a>
        <a href="profile_settings.php" class="menu-item-row">
            <div class="menu-item-icon-box">
                <i class="fa-solid fa-gear"></i>
            </div>
            <span class="menu-item-text">Profile Settings</span>
            <i class="fa-solid fa-chevron-right menu-item-chevron"></i>
        </a>
        <a href="profile_help.php" class="menu-item-row">
            <div class="menu-item-icon-box">
                <i class="fa-regular fa-circle-question"></i>
            </div>
            <span class="menu-item-text">Help & Support</span>
            <i class="fa-solid fa-chevron-right menu-item-chevron"></i>
        </a>
    </div>

    <!-- Logout Button -->
    <a href="logout.php" class="btn-logout-card" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        <span>Log Out</span>
    </a>

</div>

<?php include 'includes/footer.php'; ?>
