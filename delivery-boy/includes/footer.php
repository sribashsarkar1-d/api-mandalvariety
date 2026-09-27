</div> <!-- end .app-container -->

<?php
// Determine active page for nav
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php if (isset($_SESSION['delivery_id'])): ?>
<style>
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 480px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 12px 10px 20px 10px;
        z-index: 1000;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }
    
    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #64748b;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s;
        gap: 6px;
    }
    
    .nav-item i {
        font-size: 22px;
    }
    
    .nav-item.active {
        color: var(--mandal-green);
        font-weight: 700;
    }
</style>

<div class="bottom-nav">
    <a href="index.php" class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a href="orders.php" class="nav-item <?= $current_page == 'orders.php' ? 'active' : '' ?>">
        <i class="fa-regular fa-clipboard"></i>
        <span>Orders</span>
    </a>
    <a href="#" class="nav-item">
        <i class="fa-regular fa-map"></i>
        <span>Map</span>
    </a>
    <a href="earnings.php" class="nav-item <?= $current_page == 'earnings.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-sack-dollar"></i>
        <span>Earnings</span>
    </a>
    <a href="profile.php" class="nav-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
        <i class="fa-regular fa-user"></i>
        <span>Profile</span>
    </a>
</div>
<?php endif; ?>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
