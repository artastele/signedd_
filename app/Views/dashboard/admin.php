<?php
$pageTitle = 'Admin Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Users</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/admin/users" class="btn btn-sm btn-primary">Manage</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-person-check-fill text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Principal Requests</h5>
                    <p class="text-muted small mb-2">Approve Principal roles</p>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/admin/role-requests" class="btn btn-sm btn-primary">Review</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-file-text-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Activity Logs</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/admin/activity-logs" class="btn btn-sm btn-primary">View</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
