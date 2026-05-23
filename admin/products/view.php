<?php
require_once '../../config/config.php';
require_once '../../config/db.php';

$page_title = 'Product Details'; // For topbar

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

// ... your existing product fetch code ...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- REUSABLE SIDEBAR -->
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <!-- REUSABLE TOPBAR -->
            <?php include '../includes/topbar.php'; ?>
            
            <!-- YOUR CONTENT HERE (same compact design as before) -->
            <div class="content-section">
                <!-- Product header, price section, etc. (same as previous compact version) -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
