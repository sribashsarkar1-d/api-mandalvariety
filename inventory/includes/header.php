<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Inventory Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            width: 250px;
        }
        .sidebar a {
            color: #c2c7d0;
            text-decoration: none;
            padding: 15px;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #494e53;
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar-brand {
            font-weight: bold;
            padding: 15px;
            display: block;
            text-align: center;
            border-bottom: 1px solid #4b545c;
            color: white !important;
        }
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
            border: none;
        }
    </style>
</head>
<body>
<?php if(isset($_SESSION['inventory_user_id'])): ?>
    <div class="sidebar">
        <a href="<?= INVENTORY_BASE_URL ?>index.php" class="navbar-brand">
            <i class="fas fa-store"></i> Shop Inventory
        </a>
        <a href="<?= INVENTORY_BASE_URL ?>index.php"><i class="fas fa-list"></i> All Purchases</a>
        <a href="<?= INVENTORY_BASE_URL ?>add_purchase.php"><i class="fas fa-plus-circle"></i> Add Purchase</a>
        <a href="<?= INVENTORY_BASE_URL ?>logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="main-content">
<?php else: ?>
    <div class="container mt-5">
<?php endif; ?>
