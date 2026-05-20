<?php
$pageTitle = 'Principal Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Principal Dashboard</h1>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-pen text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">IEP Approval</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/approval" class="btn btn-primary">Review & Sign</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-person-check text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Staff Requests</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/principal/staff-requests" class="btn btn-primary">Approve Staff</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-bar-chart text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Reports</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/reports" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
