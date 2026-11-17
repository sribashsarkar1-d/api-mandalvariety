</div> <!-- End .app-wrapper -->

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php if (isset($_SESSION['delivery_id'])): ?>
<style>
    .mv-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 480px;
        background: #FFFFFF;
        border-top: 1px solid var(--border-color, #E5E7EB);
        display: flex;
        justify-content: space-around;
        align-items: center;
        height: 68px;
        z-index: 1000;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.03);
    }

    .mv-nav-link {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #64748B;
        height: 100%;
        position: relative;
        transition: color 0.15s ease;
        padding-top: 4px;
    }

    .mv-nav-icon {
        font-size: 20px;
        line-height: 1;
        margin-bottom: 4px;
        transition: transform 0.15s ease;
    }

    .mv-nav-label {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: -0.1px;
    }

    .mv-nav-indicator {
        position: absolute;
        bottom: 5px;
        width: 24px;
        height: 3px;
        background: transparent;
        border-radius: 3px;
        transition: background-color 0.2s ease;
    }

    /* Active State */
    .mv-nav-link.active {
        color: #009B55;
    }

    .mv-nav-link.active .mv-nav-label {
        font-weight: 700;
    }

    .mv-nav-link.active .mv-nav-indicator {
        background-color: #009B55;
    }
</style>

<nav class="mv-bottom-nav" aria-label="Bottom Navigation">
    <a href="index.php" class="mv-nav-link <?= in_array($current_page, ['index.php', '']) ? 'active' : '' ?>">
        <i class="fa-solid fa-house mv-nav-icon"></i>
        <span class="mv-nav-label">Home</span>
        <span class="mv-nav-indicator"></span>
    </a>
    <a href="orders.php" class="mv-nav-link <?= in_array($current_page, ['orders.php', 'view_order.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-box mv-nav-icon"></i>
        <span class="mv-nav-label">Orders</span>
        <span class="mv-nav-indicator"></span>
    </a>
    <a href="earnings.php" class="mv-nav-link <?= $current_page === 'earnings.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-sack-dollar mv-nav-icon"></i>
        <span class="mv-nav-label">Earnings</span>
        <span class="mv-nav-indicator"></span>
    </a>
    <a href="profile.php" class="mv-nav-link <?= in_array($current_page, ['profile.php', 'profile_personal.php', 'profile_documents.php', 'profile_bank.php', 'profile_settings.php', 'profile_help.php']) ? 'active' : '' ?>">
        <i class="fa-regular fa-user mv-nav-icon"></i>
        <span class="mv-nav-label">Profile</span>
        <span class="mv-nav-indicator"></span>
    </a>
</nav>
<?php endif; ?>

<!-- Shared Toast Alert Element -->
<div class="mv-toast" id="mvGlobalToast">
    <i class="fa-solid fa-circle-check" id="mvToastIcon"></i>
    <span id="mvToastMsg">Notification</span>
</div>

<script>
// Global toast helper function
function showMvToast(message, isSuccess = true) {
    const toast = document.getElementById('mvGlobalToast');
    const msg = document.getElementById('mvToastMsg');
    const icon = document.getElementById('mvToastIcon');
    if (!toast || !msg || !icon) return;

    msg.textContent = message;
    toast.className = 'mv-toast ' + (isSuccess ? 'toast-success' : 'toast-error');
    icon.className = isSuccess ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';

    toast.classList.add('show');
    clearTimeout(window._mvToastTimer);
    window._mvToastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}
</script>

</body>
</html>
