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
    
    /* Search Box Fixes for POS */
    .search-box .bi-upc-scan {
        left: auto !important;
        right: 1rem !important;
        pointer-events: auto !important;
        cursor: pointer;
    }
    .search-box input {
        padding-right: 2.5rem !important;
    }
</style>

<form onsubmit="return false;" class="mb-3">
<div class="row g-2">
    <div class="col-8 col-md-9">
        <div class="search-box mb-0">
            <i class="bi bi-search"></i>
            <input type="search" name="search" class="form-control" id="searchInput" placeholder="Search by name, barcode or SKU">
            <i class="bi bi-upc-scan"></i>
        </div>
    </div>
    <div class="col-4 col-md-3">
        <div class="dropdown">
            <button class="form-select text-start text-truncate" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="categoryDropdownBtn">
                All Cats
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="max-height: 300px; overflow-y: auto;" id="categoryDropdownList">
                <li><a class="dropdown-item active category-dropdown-item" href="javascript:void(0)" data-id="all" data-name="All Cats">All Cats</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li><a class="dropdown-item category-dropdown-item" href="javascript:void(0)" data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
</form>

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
