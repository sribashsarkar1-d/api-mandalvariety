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
$phone = $profile['phone'] ?? 'Not provided';
$joined = !empty($profile['created_at']) ? date('M Y', strtotime($profile['created_at'])) : 'Unknown';

?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f8fafc;
    }
    .profile-container {
        padding: 24px 20px;
        padding-bottom: 100px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-btn {
        color: var(--text-dark);
        text-decoration: none;
        font-size: 1.2rem;
    }
    
    .page-title {
        font-weight: 800;
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
    }

    .dots-btn {
        color: var(--text-dark);
        font-size: 1.2rem;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 15px;
    }

    .avatar-circle {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #a7f3d0;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .verified-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 28px;
        height: 28px;
        background: var(--mandal-green);
        color: white;
        border-radius: 50%;
        border: 3px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .profile-name {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    
    .profile-role {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    
    .profile-id {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .menu-list {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    .menu-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none;
        color: var(--text-dark);
        transition: background 0.2s;
    }
    .menu-item:last-child {
        border-bottom: none;
    }
    .menu-item:hover {
        background: #f8fafc;
        color: var(--text-dark);
    }
    
    .menu-icon {
        width: 24px;
        color: var(--text-muted);
        font-size: 1.1rem;
        text-align: center;
        margin-right: 15px;
    }
    
    .menu-text {
        flex: 1;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .menu-arrow {
        color: #cbd5e1;
        font-size: 0.9rem;
    }

    .btn-logout {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        text-decoration: none;
        color: #ef4444;
        font-weight: 700;
        font-size: 0.95rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
    }
    .btn-logout i {
        margin-right: 15px;
        font-size: 1.1rem;
        width: 24px;
        text-align: center;
    }
    .btn-logout:hover {
        color: #dc2626;
        background: #fef2f2;
    }
</style>

<div class="profile-container">
    <div class="page-header">
        <div class="header-left">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title">My Profile</h4>
        </div>
        <i class="fa-solid fa-ellipsis-vertical dots-btn"></i>
    </div>

    <div class="profile-header">
        <div class="avatar-wrapper">
            <div class="avatar-circle">
                <!-- SVG Avatar mimic -->
                <svg width="60" height="60" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="40" r="25" fill="#059669"/>
                    <path d="M 20 100 Q 50 60 80 100 Z" fill="#059669"/>
                    <path d="M 30 25 Q 50 5 70 25 Z" fill="#064e3b"/> <!-- Hat -->
                </svg>
            </div>
            <div class="verified-badge"><i class="fa-solid fa-check"></i></div>
        </div>
        
        <h1 class="profile-name"><?= e($name) ?></h1>
        <div class="profile-role">Delivery Partner</div>
        <div class="profile-id">ID: DB<?= str_pad($delivery_id, 4, '0', STR_PAD_LEFT) ?></div>
    </div>

    <div class="menu-list">
        <a href="#" class="menu-item">
            <div class="menu-icon"><i class="fa-regular fa-user"></i></div>
            <div class="menu-text">Personal Information</div>
            <i class="fa-solid fa-chevron-right menu-arrow"></i>
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon"><i class="fa-regular fa-file-lines"></i></div>
            <div class="menu-text">Documents</div>
            <i class="fa-solid fa-chevron-right menu-arrow"></i>
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon"><i class="fa-solid fa-building-columns"></i></div>
            <div class="menu-text">Bank Details</div>
            <i class="fa-solid fa-chevron-right menu-arrow"></i>
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon"><i class="fa-solid fa-gear"></i></div>
            <div class="menu-text">App Settings</div>
            <i class="fa-solid fa-chevron-right menu-arrow"></i>
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon"><i class="fa-regular fa-circle-question"></i></div>
            <div class="menu-text">Help & Support</div>
            <i class="fa-solid fa-chevron-right menu-arrow"></i>
        </a>
    </div>

    <a href="logout.php" class="btn-logout">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        Logout
    </a>

</div>

<?php include 'includes/footer.php'; ?>
