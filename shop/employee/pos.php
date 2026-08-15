<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_login(); // Both admin and employee can access POS

$page_title = 'POS';
$show_back_btn = true; // For mobile back button

// Fetch categories for pills
$stmt = $pdo->query("SELECT id, name FROM employee_categories WHERE status = 'active'");
$categories = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<style>
    /* Specific POS overrides */
    body { background-color: var(--bg-color); }
    /* .bottom-nav { display: none !important; } Removed to show bottom buttons */
    .main-content { padding-bottom: 20px; }
    
    /* Search Box */
    .search-box {
        position: relative;
        margin-bottom: 1rem;
    }
    .search-box .form-control {
        padding-left: 2.5rem;
        padding-right: 2.5rem;
        border-radius: 20px;
    }
    .search-box .bi-search {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .search-box .bi-upc-scan {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        cursor: pointer;
    }
    
    /* Floating Cart Button */
    .floating-cart {
        position: fixed;
        bottom: 90px;
        right: 20px;
        background: var(--primary-blue);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-md);
        font-size: 1.5rem;
        z-index: 1050;
        cursor: pointer;
        text-decoration: none;
    }
    .floating-cart .badge {
        position: absolute;
        top: 5px;
        right: 5px;
        background: var(--danger);
        font-size: 0.7rem;
        padding: 0.3em 0.5em;
        border-radius: 50%;
    }
</style>

<div class="search-box">
    <i class="bi bi-search"></i>
    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, barcode or SKU">
    <i class="bi bi-upc-scan"></i>
</div>

<!-- Category Pills -->
<div class="category-pills mb-3" id="categoryPills">
    <div class="category-pill active" data-id="all">All</div>
    <?php foreach ($categories as $cat): ?>
        <div class="category-pill" data-id="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></div>
    <?php endforeach; ?>
</div>

<!-- Product List Container -->
<div id="productList" class="row g-2">
    <!-- Products will be loaded here via JS -->
</div>

<!-- Cart Button -->
<a href="cart.php" class="floating-cart no-print">
    <i class="bi bi-cart3"></i>
    <span class="badge" id="cartCount">0</span>
</a>

<?php 
$extra_js = '<script src="' . BASE_URL . '/assets/js/pos.js"></script>';
require_once '../includes/footer.php'; 
?>
