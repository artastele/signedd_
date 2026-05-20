<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Status (Parent View)

$pageTitle = 'Enrollment Status - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Status colors
$statusColors = [
    'draft' => 'secondary',
    'pending' => 'warning',
    'verified' => 'success',
    'rejected' => 'danger'
];

// Status icons
$statusIcons = [
    'draft' => 'bi-pencil-square',
    'pending' => 'bi-clock-history',
    'verified' => 'bi-check-circle',
    'rejected' => 'bi-x-circle'
];
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-list-check text-primary"></i> My Enrollment Applications
        </h1>
        <a href="<?php echo $basePath; ?>/enrollment" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Enrollment
        </a>
    </div>

    <?php if (empty($enrollments)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">No Enrollments Yet</h4>
                <p class="text-muted">You haven't submitted any enrollment applications.</p>
                <a href="<?php echo $basePath; ?>/enrollment" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Start New Enrollment
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($enrollments as $enrollment): ?>
            <div class="col-md-6 mb-4">
                <div class="card border-<?php echo $statusColors[$enrollment['status']]; ?>">
                    <div class="card-header bg-<?php echo $statusColors[$enrollment['status']]; ?> text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi <?php echo $statusIcons[$enrollment['status']]; ?>"></i>
                                <?php echo ucfirst($enrollment['status']); ?>
                            </h5>
                            <span class="badge bg-light text-dark">
                                #<?php echo $enrollment['id']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Student Info -->
                        <h5 class="card-title text-primary">
                            <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                        </h5>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Enrollment Type</small><br>
                                <strong><?php echo ucfirst($enrollment['enrollment_type']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Grade Level</small><br>
                                <strong><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></strong>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">School Year</small><br>
                                <strong><?php echo htmlspecialchars($enrollment['school_year']); ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Submitted</small><br>
                                <strong><?php echo date('M j, Y', strtotime($enrollment['submitted_at'])); ?></strong>
                            </div>
                        </div>

                        <!-- Status Message -->
                        <?php if ($enrollment['status'] === 'pending'): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-hourglass-split"></i> 
                                Your enrollment is being reviewed by a SPED teacher. You will be notified once the review is complete.
                            </div>
                        <?php elseif ($enrollment['status'] === 'verified'): ?>
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-check-circle"></i> 
                                Your enrollment has been approved! All documents have been verified.
                            </div>
                        <?php elseif ($enrollment['status'] === 'rejected'): ?>
                            <div class="alert alert-danger mb-3">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    Enrollment Rejected
                                </h6>
                                <?php if (!empty($enrollment['review_note'])): ?>
                                    <hr>
                                    <p class="mb-0">
                                        <strong>Reason:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($enrollment['review_note'])); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="mb-0">
                                        Your enrollment was rejected. Please contact the school for more information.
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <a href="<?php echo $basePath; ?>/enrollment/view/<?php echo $enrollment['id']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                            
                            <?php if ($enrollment['status'] === 'rejected'): ?>
                                <a href="<?php echo $basePath; ?>/enrollment/create?type=<?php echo $enrollment['enrollment_type']; ?>&resubmit=<?php echo $enrollment['id']; ?>" 
                                   class="btn btn-warning">
                                    <i class="bi bi-arrow-repeat"></i> Resubmit
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>
                            <i class="bi bi-calendar"></i> 
                            Last updated: <?php echo date('M j, Y g:i A', strtotime($enrollment['updated_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
