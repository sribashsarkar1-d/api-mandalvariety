<?php if(isset($_SESSION['inventory_user_id'])): ?>
        </main> <!-- End content-area -->
    </div> <!-- End main-wrapper -->
    
    <!-- Mobile Bottom Navigation (Hidden on Desktop) -->
    <style>
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            padding-bottom: calc(10px + env(safe-area-inset-bottom));
            z-index: 1030;
            border-top: 1px solid #f1f5f9;
        }
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.2s;
            gap: 4px;
            width: 25%;
        }
        .bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
        .bottom-nav-item.active {
            color: var(--primary);
        }
        .bottom-nav-item.active i {
            background: #eef2ff;
            width: 48px;
            height: 32px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bottom-nav-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    
    <div class="bottom-nav d-md-none">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <a href="<?= INVENTORY_BASE_URL ?>index.php" class="bottom-nav-item <?= ($current_page == 'index.php') ? 'active' : '' ?>">
            <i class="fas fa-boxes"></i>
            <span>Purchases</span>
        </a>
        <a href="<?= INVENTORY_BASE_URL ?>add_purchase.php" class="bottom-nav-item <?= ($current_page == 'add_purchase.php') ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i>
            <span>Add Purchase</span>
        </a>
        <a href="javascript:void(0)" class="bottom-nav-item disabled" title="Coming Soon">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        <a href="javascript:void(0)" class="bottom-nav-item disabled" title="Coming Soon">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>

<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile Sidebar Toggle Logic
    document.addEventListener("DOMContentLoaded", function() {
        const toggler = document.getElementById('sidebarToggler');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if(toggler && sidebar && backdrop) {
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                if (sidebar.classList.contains('show')) {
                    backdrop.classList.add('show');
                } else {
                    backdrop.classList.remove('show');
                }
            }

            toggler.addEventListener('click', toggleSidebar);
            backdrop.addEventListener('click', toggleSidebar);
        }
    });
</script>
</body>
</html>
