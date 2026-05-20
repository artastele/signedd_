<?php
$pageTitle = 'Services - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Coming Soon Banner -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); color: white; border: none;">
        <div class="card-body p-5 text-center">
            <i class="bi bi-tools" style="font-size: 5rem; opacity: 0.9;"></i>
            <h1 class="mt-3 mb-2">Services & Information</h1>
            <p class="lead mb-0">Coming Soon</p>
        </div>
    </div>

    <!-- Future Features Preview -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title text-secondary">
                        <i class="bi bi-lightbulb"></i> What's Coming
                    </h5>
                    <p class="text-muted mb-4">
                        This page will provide comprehensive information about our school services and programs.
                    </p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">School Information</h6>
                                    <p class="text-muted mb-0 small">Learn about our mission, vision, and history</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">SPED Programs</h6>
                                    <p class="text-muted mb-0 small">Overview of special education programs offered</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">Enrollment Guide</h6>
                                    <p class="text-muted mb-0 small">Step-by-step guide for parent enrollment</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">Staff Resources</h6>
                                    <p class="text-muted mb-0 small">Information for teachers and staff members</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">FAQs</h6>
                                    <p class="text-muted mb-0 small">Frequently asked questions and answers</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">Contact Directory</h6>
                                    <p class="text-muted mb-0 small">Contact information for different departments</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Temporary Navigation -->
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="background-color: #f9f9f9; border: none;">
                <div class="card-body p-4 text-center">
                    <p class="mb-3 text-muted">
                        <i class="bi bi-arrow-left-circle"></i> 
                        In the meantime, you can return to the dashboard to get started.
                    </p>
                    <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
