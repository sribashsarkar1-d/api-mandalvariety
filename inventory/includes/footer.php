<?php if(isset($_SESSION['inventory_user_id'])): ?>
        </main> <!-- End content-area -->
    </div> <!-- End main-wrapper -->
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
