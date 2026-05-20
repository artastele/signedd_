<?php
$pageTitle = 'Master Teacher Dashboard - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Master Teacher Dashboard</h1>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-eye text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Class Observation</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/observation" class="btn btn-primary">Conduct Observation</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">COT Results</h5>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/cot" class="btn btn-primary">Submit Results</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
