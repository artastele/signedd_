<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Type Selection

$pageTitle = 'Enroll Your Child - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-clipboard-heart text-primary"></i> Enroll Your Child
    </h1>

    <?php if (isset($draft) && $draft): ?>
        <!-- Resume Draft Card -->
        <div class="card border-warning mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text"></i> Unfinished Enrollment Found
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-2">
                            <strong>Type:</strong> <?php echo ucfirst($draft['enrollment_type']); ?> Student<br>
                            <strong>Last saved:</strong> <?php echo date('F j, Y g:i A', strtotime($draft['last_activity'] ?? $draft['draft_saved_at'])); ?><br>
                            <?php if (!empty($draft['first_name']) || !empty($draft['last_name'])): ?>
                                <strong>Student:</strong> <?php echo htmlspecialchars(($draft['first_name'] ?? '') . ' ' . ($draft['last_name'] ?? '')); ?>
                            <?php endif; ?>
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle"></i> Your progress has been saved. You can continue where you left off or start fresh.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?php echo $basePath; ?>/enrollment/create?type=<?php echo $draft['enrollment_type']; ?>&resume=1" 
                           class="btn btn-warning mb-2 w-100">
                            <i class="bi bi-play-circle"></i> Resume Draft
                        </a>
                        <form method="POST" action="<?php echo $basePath; ?>/enrollment/discard-draft" 
                              onsubmit="return confirm('Are you sure you want to discard this draft? This cannot be undone.');" 
                              class="d-inline w-100">
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-trash"></i> Discard Draft
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- New Student -->
        <div class="col-md-4">
            <div class="card h-100 border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-plus-fill text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="card-title text-primary">New Student</h4>
                    <p class="card-text text-muted">
                        First time enrolling in this school. Complete all information from scratch.
                    </p>
                    <ul class="list-unstyled text-start mb-4">
                        <li><i class="bi bi-check-circle text-success"></i> Fill complete BEEF form</li>
                        <li><i class="bi bi-check-circle text-success"></i> Upload required documents</li>
                        <li><i class="bi bi-check-circle text-success"></i> No previous school info needed</li>
                    </ul>
                    <a href="<?php echo $basePath; ?>/enrollment/create?type=new" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right-circle"></i> Start New Enrollment
                    </a>
                </div>
            </div>
        </div>

        <!-- Transfer Student -->
        <div class="col-md-4">
            <div class="card h-100 border-secondary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-arrow-left-right text-secondary" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="card-title text-secondary">Transfer Student</h4>
                    <p class="card-text text-muted">
                        Transferring from another school. Provide previous school information.
                    </p>
                    <ul class="list-unstyled text-start mb-4">
                        <li><i class="bi bi-check-circle text-success"></i> Fill complete BEEF form</li>
                        <li><i class="bi bi-check-circle text-success"></i> Upload required documents</li>
                        <li><i class="bi bi-exclamation-circle text-warning"></i> Previous school info required</li>
                    </ul>
                    <a href="<?php echo $basePath; ?>/enrollment/create?type=transfer" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-right-circle"></i> Start Transfer Enrollment
                    </a>
                </div>
            </div>
        </div>

        <!-- Returning Student -->
        <div class="col-md-4">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-arrow-clockwise text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="card-title text-success">Returning Student</h4>
                    <p class="card-text text-muted">
                        Previously enrolled in this school. Most information will be pre-filled.
                    </p>
                    <ul class="list-unstyled text-start mb-4">
                        <li><i class="bi bi-check-circle text-success"></i> Auto-fill from previous enrollment</li>
                        <li><i class="bi bi-check-circle text-success"></i> Update changed information only</li>
                        <li><i class="bi bi-check-circle text-success"></i> Faster enrollment process</li>
                    </ul>
                    <?php if (isset($previousEnrollment) && $previousEnrollment): ?>
                        <a href="<?php echo $basePath; ?>/enrollment/returning-lookup" class="btn btn-success w-100">
                            <i class="bi bi-search"></i> Find Returning Student
                        </a>
                        <small class="text-muted d-block mt-2">
                            Search by LRN or name to auto-fill data
                        </small>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>/enrollment/returning-lookup" class="btn btn-success w-100">
                            <i class="bi bi-search"></i> Find Returning Student
                        </a>
                        <small class="text-muted d-block mt-2">
                            Search by LRN or name to auto-fill data
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-info-circle text-primary"></i> Before You Start
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-secondary">Required Documents:</h6>
                    <ul>
                        <li>PSA Birth Certificate</li>
                        <li>PWD ID or Medical Record (showing disability)</li>
                        <li>BEEF Form (can be filled online or uploaded)</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-secondary">What to Expect:</h6>
                    <ul>
                        <li>8-step enrollment form (auto-saves every 30 seconds)</li>
                        <li>Digital signature required</li>
                        <li>SPED teacher will review your application</li>
                        <li>You'll be notified via email and in-app notification</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
