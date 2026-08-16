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
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 10px 15px 25px 15px;
        z-index: 1000;
        border-top-left-radius: 24px;
        border-top-right-radius: 24px;
    }
    
    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        gap: 4px;
    }
    
    .nav-item i {
        font-size: 1.25rem;
        margin-bottom: 2px;
    }
    
    .nav-item.active {
        color: var(--mandal-green);
    }
    
    .nav-item-center {
        position: relative;
        top: -15px;
    }
    
    .nav-item-center .power-btn {
        width: 56px;
        height: 56px;
        background: var(--mandal-green);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 4px 15px rgba(7, 161, 88, 0.3);
        margin-bottom: 4px;
    }

    .nav-item.disabled {
        opacity: 0.5;
        pointer-events: none;
    }
</style>

<div class="bottom-nav">
    <a href="index.php" class="nav-item <?= $current_page == 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a href="index.php#tasks" class="nav-item">
        <i class="fa-solid fa-clipboard-list"></i>
        <span>Orders</span>
    </a>
    
    <a href="javascript:void(0)" onclick="document.getElementById('availabilityForm').submit();" class="nav-item nav-item-center">
        <div class="power-btn">
            <i class="fa-solid fa-power-off"></i>
        </div>
        <span style="color: var(--text-dark);">Go Online</span>
    </a>
    
    <a href="earnings.php" class="nav-item <?= $current_page == 'earnings.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-wallet"></i>
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
