    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 for Beautiful Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Flash Messages as Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo addslashes($_SESSION['success']); ?>',
            confirmButtonColor: '#3b6d11',
            timer: 3000,
            timerProgressBar: true
        });
    </script>
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo addslashes($_SESSION['error']); ?>',
            confirmButtonColor: '#a01422'
        });
    </script>
    <?php unset($_SESSION['error']); endif; ?>
    
    <?php if (isset($_SESSION['warning'])): ?>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: '<?php echo addslashes($_SESSION['warning']); ?>',
            confirmButtonColor: '#ffc107'
        });
    </script>
    <?php unset($_SESSION['warning']); endif; ?>
    
    <?php if (isset($_SESSION['info'])): ?>
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Information',
            text: '<?php echo addslashes($_SESSION['info']); ?>',
            confirmButtonColor: '#1e4072'
        });
    </script>
    <?php unset($_SESSION['info']); endif; ?>
    
    <!-- Custom JS -->
    <script src="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/js/app.js"></script>
    
    <!-- Sidebar Mobile Navigation -->
    <script src="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/js/sidebar.js"></script>
</body>
</html>
