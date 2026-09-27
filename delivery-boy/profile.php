<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_name = $_SESSION['delivery_name'];
$delivery_id = $_SESSION['delivery_id'];
$driver_id_formatted = "DB" . str_pad($delivery_id, 4, '0', STR_PAD_LEFT);
?>
<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f8fafc;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 20px 10px 20px;
        background: #fff;
    }
    
    .back-btn, .header-action {
        color: var(--text-dark);
        font-size: 1.2rem;
        text-decoration: none;
    }
    
    .page-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .profile-header {
        background: #ffffff;
        padding: 30px 20px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 10px;
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 15px;
    }

    .avatar-circle {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #a7f3d0;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .edit-avatar {
        position: absolute;
        bottom: 0;
        right: 0;
        background: var(--mandal-green);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #ffffff;
        font-size: 12px;
        cursor: pointer;
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0 0 4px 0;
    }

    .profile-role {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: 2px;
    }

    .profile-id {
        font-size: 13px;
        color: #94a3b8;
    }

    .menu-list {
        background: #ffffff;
        padding: 0 20px;
        margin-bottom: 20px;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 18px 0;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none;
        color: var(--text-dark);
    }
    .menu-item:last-child {
        border-bottom: none;
    }

    .menu-icon {
        width: 24px;
        font-size: 18px;
        color: #64748b;
        margin-right: 15px;
        text-align: center;
    }

    .menu-text {
        flex: 1;
        font-size: 15px;
        font-weight: 600;
    }

    .menu-arrow {
        color: #cbd5e1;
        font-size: 14px;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        padding: 20px;
        text-decoration: none;
        color: #ef4444;
        font-weight: 700;
        font-size: 16px;
        background: #ffffff;
    }
    .logout-btn i {
        margin-right: 15px;
        font-size: 18px;
    }
</style>

<div class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
    <h1 class="page-title">My Profile</h1>
    <a href="#" class="header-action"><i class="fa-solid fa-ellipsis-vertical"></i></a>
</div>

<div class="profile-header">
    <div class="avatar-wrapper">
        <div class="avatar-circle">
            <svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="50" fill="#a7f3d0"/>
                <circle cx="50" cy="40" r="20" fill="#fcd34d"/>
                <path d="M 25 90 Q 50 60 75 90 Z" fill="#059669"/>
                <path d="M 35 25 Q 50 10 65 25 Z" fill="#064e3b"/>
            </svg>
        </div>
        <div class="edit-avatar">
            <i class="fa-solid fa-camera"></i>
        </div>
    </div>
    <h2 class="profile-name"><?= e($delivery_name) ?></h2>
    <div class="profile-role">Delivery Partner</div>
    <div class="profile-id">ID: <?= $driver_id_formatted ?></div>
</div>

<div class="menu-list">
    <a href="#" class="menu-item">
        <i class="fa-regular fa-user menu-icon"></i>
        <span class="menu-text">Personal Information</span>
        <i class="fa-solid fa-chevron-right menu-arrow"></i>
    </a>
    <a href="#" class="menu-item">
        <i class="fa-regular fa-file-lines menu-icon"></i>
        <span class="menu-text">Documents</span>
        <i class="fa-solid fa-chevron-right menu-arrow"></i>
    </a>
    <a href="#" class="menu-item">
        <i class="fa-solid fa-building-columns menu-icon"></i>
        <span class="menu-text">Bank Details</span>
        <i class="fa-solid fa-chevron-right menu-arrow"></i>
    </a>
    <a href="#" class="menu-item">
        <i class="fa-solid fa-gear menu-icon"></i>
        <span class="menu-text">App Settings</span>
        <i class="fa-solid fa-chevron-right menu-arrow"></i>
    </a>
    <a href="#" class="menu-item">
        <i class="fa-regular fa-circle-question menu-icon"></i>
        <span class="menu-text">Help & Support</span>
        <i class="fa-solid fa-chevron-right menu-arrow"></i>
    </a>
</div>

<a href="logout.php" class="logout-btn">
    <i class="fa-solid fa-arrow-right-from-bracket"></i>
    <span>Logout</span>
</a>

<?php include 'includes/footer.php'; ?>
