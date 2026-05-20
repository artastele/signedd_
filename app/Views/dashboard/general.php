<?php
$pageTitle = 'Dashboard - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-permanent" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Pending Application Alert -->
    <?php if (isset($pendingRequest) && $pendingRequest): ?>
        <div class="alert alert-warning alert-permanent" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-clock-history"></i> Application Pending Review
            </h5>
            <p class="mb-2">
                Your application is currently being reviewed.
            </p>
            <p class="mb-2">
                <strong>Applied Role:</strong> 
                <span class="badge bg-secondary"><?php echo ucwords(str_replace('_', ' ', $pendingRequest['requested_role'])); ?></span>
            </p>
            <p class="mb-0">
                <small class="text-muted">
                    <i class="bi bi-calendar"></i> Submitted: <?php echo date('F j, Y g:i A', strtotime($pendingRequest['created_at'])); ?>
                </small>
            </p>
            <hr>
            <p class="mb-0">
                <i class="bi bi-info-circle"></i> You will receive an email notification once your application is reviewed. 
                In the meantime, you can explore the system information below.
            </p>
        </div>
    <?php endif; ?>

    <!-- Rejected Application Alert -->
    <?php if (!isset($pendingRequest) && isset($rejectedRequest) && $rejectedRequest): ?>
        <div class="alert alert-danger alert-permanent" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-x-circle"></i> Application Rejected
            </h5>
            <p class="mb-2">
                Your application for <strong><?php echo ucwords(str_replace('_', ' ', $rejectedRequest['requested_role'])); ?></strong> 
                was not approved.
            </p>
            <?php if (!empty($rejectedRequest['review_note'])): ?>
                <div class="alert alert-light mb-3" style="border-left: 3px solid #a01422;">
                    <strong>Reason:</strong><br>
                    <?php echo nl2br(htmlspecialchars($rejectedRequest['review_note'])); ?>
                </div>
            <?php endif; ?>
            <p class="mb-2">
                <small class="text-muted">
                    <i class="bi bi-calendar"></i> Reviewed: <?php echo date('F j, Y g:i A', strtotime($rejectedRequest['updated_at'])); ?>
                    <?php if (!empty($rejectedRequest['reviewer_name'])): ?>
                        by <?php echo htmlspecialchars($rejectedRequest['reviewer_name']); ?>
                    <?php endif; ?>
                </small>
            </p>
            <hr>
            <div class="d-flex gap-2">
                <a href="<?php echo $basePath; ?>/role/select" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Reapply for Role
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#applicationHistoryModal">
                    <i class="bi bi-clock-history"></i> View Application History
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Welcome Banner -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); color: white; border: none;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Welcome to SPED LMS, <?php echo htmlspecialchars($userName); ?>!</h1>
                    <p class="mb-0 lead">Special Education Learning Management System</p>
                    <p class="mb-0">Empowering educators, supporting learners, building futures.</p>
                </div>
                <div class="col-md-4 text-center">
                    <?php if (file_exists(__DIR__ . '/../../../public/images/logo-large.png')): ?>
                        <img src="<?php echo $basePath; ?>/images/logo-large.png" alt="SPED LMS Logo" style="max-width: 120px; filter: brightness(0) invert(1);">
                    <?php else: ?>
                        <i class="bi bi-mortarboard-fill" style="font-size: 5rem;"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary">
                        <i class="bi bi-info-circle-fill"></i> About Our System
                    </h5>
                    <p class="card-text">
                        The SPED Learning Management System is designed to streamline the management of special education programs. 
                        Our platform supports the entire IEP (Individualized Education Plan) lifecycle, from enrollment and assessment 
                        to implementation and progress tracking.
                    </p>
                    <p class="card-text mb-0">
                        <strong>Our Mission:</strong> To provide inclusive, quality education for learners with special needs through 
                        efficient collaboration between parents, teachers, and administrators.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center" style="border-left: 4px solid #a01422;">
                <div class="card-body">
                    <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">---</h3>
                    <p class="text-muted mb-0">Active Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center" style="border-left: 4px solid #1e4072;">
                <div class="card-body">
                    <i class="bi bi-person-badge-fill text-secondary" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">---</h3>
                    <p class="text-muted mb-0">SPED Teachers</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center" style="border-left: 4px solid #3b6d11;">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text-fill text-success" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">---</h3>
                    <p class="text-muted mb-0">Active IEPs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Get Started Section -->
    <?php if (!isset($pendingRequest) || !$pendingRequest): ?>
        <div class="row">
            <div class="col-12 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-secondary mb-1">
                            <i class="bi bi-arrow-right-circle"></i> Get Started
                        </h4>
                        <p class="text-muted mb-0">Choose how you'd like to use the system:</p>
                    </div>
                    <?php if (isset($applicationHistory) && count($applicationHistory) > 0): ?>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#applicationHistoryModal">
                            <i class="bi bi-clock-history"></i> View Application History
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Role Selection Cards -->
        <div class="row">
        <!-- Apply as Staff -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 role-selection-card" style="border: 2px solid #1e4072; transition: all 0.3s ease;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-badge" style="font-size: 4rem; color: #1e4072;"></i>
                    </div>
                    <h4 class="card-title text-secondary mb-3">Apply as Staff</h4>
                    <p class="card-text mb-4">
                        Are you a teacher, guidance counselor, principal, or master teacher? 
                        Apply for a staff role to access professional features.
                    </p>
                    
                    <div class="mb-4">
                        <p class="mb-2"><strong>Available Roles:</strong></p>
                        <span class="badge bg-secondary me-1 mb-1">SPED Teacher</span>
                        <span class="badge bg-secondary me-1 mb-1">Guidance Counselor</span>
                        <span class="badge bg-secondary me-1 mb-1">Principal</span>
                        <span class="badge bg-secondary me-1 mb-1">Master Teacher</span>
                    </div>

                    <div class="alert alert-info" style="background-color: #e3f2fd; border: none;">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            <strong>Verification Required:</strong> You'll need to submit documents for admin approval.
                        </small>
                    </div>

                    <a href="<?php echo $basePath; ?>/role/select?type=staff" class="btn btn-secondary btn-lg w-100">
                        <i class="bi bi-briefcase"></i> Apply as Staff
                    </a>
                </div>
            </div>
        </div>

        <!-- Enroll Your Child (Parent) -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 role-selection-card" style="border: 2px solid #a01422; transition: all 0.3s ease;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-heart-fill" style="font-size: 4rem; color: #a01422;"></i>
                    </div>
                    <h4 class="card-title text-primary mb-3">Enroll Your Child</h4>
                    <p class="card-text mb-4">
                        Are you a parent or guardian? Start the enrollment process for your child 
                        and track their learning progress.
                    </p>
                    
                    <div class="mb-4">
                        <p class="mb-2"><strong>Parent Features:</strong></p>
                        <ul class="list-unstyled text-start" style="max-width: 300px; margin: 0 auto;">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Submit enrollment documents</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Track application status</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> View child's progress</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Receive IEP notifications</li>
                        </ul>
                    </div>

                    <div class="alert alert-success" style="background-color: #e8f5e9; border: none;">
                        <small>
                            <i class="bi bi-lightning-fill"></i> 
                            <strong>Instant Access:</strong> No verification needed. Start immediately!
                        </small>
                    </div>

                    <a href="<?php echo $basePath; ?>/role/select?type=parent" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-person-plus"></i> Enroll Your Child
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card" style="background-color: #f9f9f9; border: none;">
                <div class="card-body p-3 text-center">
                    <p class="mb-0 text-muted">
                        <i class="bi bi-question-circle"></i> 
                        Need help? Contact the administrator at 
                        <a href="mailto:admin@spedlms.local" class="text-decoration-none">admin@spedlms.local</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application History Modal -->
<div class="modal fade" id="applicationHistoryModal" tabindex="-1" aria-labelledby="applicationHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); color: white;">
                <h5 class="modal-title" id="applicationHistoryModalLabel">
                    <i class="bi bi-clock-history"></i> Application History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($applicationHistory) && count($applicationHistory) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background-color: #1e4072; color: white;">
                                <tr>
                                    <th>Role Applied</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Reviewed</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applicationHistory as $app): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo ucwords(str_replace('_', ' ', $app['requested_role'])); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadge = [
                                                'pending' => '<span class="badge" style="background-color: #ffc107; color: #000;">Pending</span>',
                                                'approved' => '<span class="badge bg-success">Approved</span>',
                                                'rejected' => '<span class="badge bg-danger">Rejected</span>'
                                            ];
                                            echo $statusBadge[$app['status']] ?? '<span class="badge bg-secondary">Unknown</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($app['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($app['status'] !== 'pending'): ?>
                                                <small>
                                                    <?php echo date('M j, Y', strtotime($app['updated_at'])); ?>
                                                    <?php if (!empty($app['reviewer_name'])): ?>
                                                        <br><em class="text-muted">by <?php echo htmlspecialchars($app['reviewer_name']); ?></em>
                                                    <?php endif; ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($app['review_note'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="popover" 
                                                        data-bs-trigger="focus"
                                                        data-bs-placement="left"
                                                        data-bs-content="<?php echo htmlspecialchars($app['review_note']); ?>">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No application history found.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize popovers for review notes
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<style>
.role-selection-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
