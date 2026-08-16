<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Inventory Pro</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #5438dc;
            --primary-hover: #432ac7;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-active-bg: #1e293b;
            --sidebar-active-text: #ffffff;
            --bg-color: #fafafa;
            --card-shadow: 0 10px 25px rgba(0,0,0,0.03), 0 4px 10px rgba(0,0,0,0.02);
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: #334155;
            margin: 0;
            overflow-x: hidden;
            /* Extra padding on mobile for the fixed bottom nav */
            padding-bottom: 0;
        }
        @media (max-width: 767.98px) {
            body { padding-bottom: 80px; }
        }

        /* --- Sidebar Styles --- */
        .sidebar {
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: white;
            position: fixed;
            width: 260px;
            z-index: 1040;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            top: 0;
            left: 0;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            margin-bottom: 10px;
        }
        .sidebar-brand:hover { color: white; }
        .sidebar-nav { padding: 10px; }
        .sidebar-nav a {
            color: var(--sidebar-text);
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            margin-bottom: 5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar-nav a i { font-size: 1.1rem; width: 20px; text-align: center; }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            transform: translateX(4px);
        }
        .sidebar-nav a.text-danger:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444 !important;
        }

        /* --- Top Navbar (Desktop Only) --- */
        .top-navbar {
            background: white;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        @media (max-width: 767.98px) {
            .top-navbar { display: none; }
        }
        
        .sidebar-toggler {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            display: none;
        }

        /* --- Main Content Area --- */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }
        .content-area {
            padding: 24px;
            flex-grow: 1;
        }
        @media (max-width: 767.98px) {
            .content-area { padding: 15px; }
        }

        /* --- UI Components --- */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            background: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(84, 56, 220, 0.15);
        }
        .btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary, .btn-success {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover, .btn-success:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        /* Glassmorphism Auth Card */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%);
            padding: 20px;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255,255,255,0.5);
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
        }

        /* --- Responsive Design --- */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .sidebar-toggler {
                display: block;
            }
            /* Backdrop for mobile sidebar */
            .sidebar-backdrop {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(15, 23, 42, 0.5);
                backdrop-filter: blur(2px);
                z-index: 1035;
                display: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .sidebar-backdrop.show {
                display: block;
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<?php if(isset($_SESSION['inventory_user_id'])): ?>
    
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <a href="<?= INVENTORY_BASE_URL ?>index.php" class="sidebar-brand">
            <i class="fas fa-layer-group text-primary"></i> Inventory Pro
        </a>
        <div class="sidebar-nav">
            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
            <a href="<?= INVENTORY_BASE_URL ?>index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i> All Purchases
            </a>
            <a href="<?= INVENTORY_BASE_URL ?>add_purchase.php" class="<?= ($current_page == 'add_purchase.php') ? 'active' : '' ?>">
                <i class="fas fa-cart-plus"></i> Add Purchase
            </a>
            
            <div style="margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                <a href="<?= INVENTORY_BASE_URL ?>logout.php" class="text-danger mt-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <button class="sidebar-toggler" id="sidebarToggler">
                <i class="fas fa-bars"></i>
            </button>
            <div class="ms-auto fw-medium text-secondary">
                <i class="fas fa-user-circle me-1"></i> Hello, <?= e($_SESSION['inventory_username'] ?? 'Admin') ?>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
<?php else: ?>
    <!-- Non-logged in layout handled directly in login/signup -->
<?php endif; ?>
