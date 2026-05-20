<?php
$pageTitle = 'Guidance Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Guidance Counselor Dashboard</h1>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">IEP Meetings</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/meetings" class="btn btn-primary">Manage Meetings</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-text text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">IEP Documents</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/documents" class="btn btn-primary">View & Sign</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
