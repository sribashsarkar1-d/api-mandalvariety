<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Partner Portal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --secondary-bg: #f8fafc;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--secondary-bg);
            font-family: 'Inter', system-ui, sans-serif;
            color: #1e293b;
        }

        .navbar-premium {
            background: var(--primary-gradient);
            padding: 15px 20px;
            color: white;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-link-icon {
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-link-icon:hover {
            color: #d1fae5;
            transform: scale(1.1);
        }

        .premium-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .btn-premium {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            color: white;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['delivery_id'])): ?>
<div class="navbar-premium">
    <a href="index.php" class="navbar-brand">
        <i class="fa-solid fa-motorcycle"></i> Partner Portal
    </a>
    <div class="nav-actions">
        <a href="index.php" class="nav-link-icon"><i class="fa-solid fa-house"></i></a>
        <a href="logout.php" class="nav-link-icon" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</div>
<?php endif; ?>
