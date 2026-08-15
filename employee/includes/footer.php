        </div> <!-- end container-fluid -->
    </main> <!-- end main-content -->
    
    <?php
    if (is_logged_in()) {
        require_once __DIR__ . '/bottom-nav.php';
    }
    ?>
</div> <!-- end main-wrapper -->

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<?php if (isset($extra_js)): echo $extra_js; endif; ?>
</body>
</html>
