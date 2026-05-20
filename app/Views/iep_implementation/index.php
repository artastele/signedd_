<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-06-01
// Part of: SPED LMS — IEP Implementation Index

$pageTitle = 'IEP Implementation - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h3 class="mb-1 fw-bold" style="color:#1e4072;">
                    <i class="ti ti-clipboard-check me-2"></i>IEP Implementation
                </h3>
                <p class="text-muted mb-0">Manage lesson plans and learning activities for your students</p>
            </div>
        </div>

        <!-- Flash Alerts -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-circle-check me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100" style="border-top:3px solid #1e4072;">
                    <div class="card-body text-center py-3">
                        <div class="mb-1">
                            <i class="ti ti-users" style="font-size:1.8rem;color:#1e4072;"></i>
                        </div>
                        <h4 class="fw-bold mb-0" style="color:#1e4072;"><?php echo (int)$totalStudents; ?></h4>
                        <small class="text-muted">Total Students</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100" style="border-top:3px solid #3b6d11;">
                    <div class="card-body text-center py-3">
                        <div class="mb-1">
                            <i class="ti ti-book-upload" style="font-size:1.8rem;color:#3b6d11;"></i>
                        </div>
                        <h4 class="fw-bold mb-0" style="color:#3b6d11;"><?php echo (int)$published; ?></h4>
                        <small class="text-muted">Published Plans</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100" style="border-top:3px solid #a01422;">
                    <div class="card-body text-center py-3">
                        <div class="mb-1">
                            <i class="ti ti-clock-exclamation" style="font-size:1.8rem;color:#a01422;"></i>
                        </div>
                        <h4 class="fw-bold mb-0" style="color:#a01422;"><?php echo (int)$pending; ?></h4>
                        <small class="text-muted">Pending Submissions</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100" style="border-top:3px solid #e67e22;">
                    <div class="card-body text-center py-3">
                        <div class="mb-1">
                            <i class="ti ti-file-pencil" style="font-size:1.8rem;color:#e67e22;"></i>
                        </div>
                        <h4 class="fw-bold mb-0" style="color:#e67e22;"><?php echo (int)$draftCount; ?></h4>
                        <small class="text-muted">Draft Plans</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Cards -->
        <?php if (empty($students)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-inbox" style="font-size:3.5rem;color:#ccc;"></i>
                    <h5 class="mt-3 text-muted">No students with signed IEPs yet</h5>
                    <p class="text-muted mb-0">Complete Process 5 (IEP Generation) first to see students here.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($students as $s): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100" style="border-left:4px solid #1e4072;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0" style="color:#1e4072;">
                                            <?php echo htmlspecialchars($s['student_name']); ?>
                                        </h6>
                                        <small class="text-muted">LRN: <?php echo htmlspecialchars($s['lrn']); ?></small>
                                    </div>
                                    <span class="badge" style="background:#3b6d11;font-size:0.7rem;">
                                        <i class="ti ti-circle-check me-1"></i>Signed
                                    </span>
                                </div>

                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <span class="badge" style="background:#1e4072;">
                                        <i class="ti ti-calendar me-1"></i><?php echo htmlspecialchars($s['school_year']); ?>
                                    </span>
                                    <?php if ((int)$s['lesson_plan_count'] > 0): ?>
                                        <span class="badge bg-secondary">
                                            <i class="ti ti-book me-1"></i><?php echo (int)$s['lesson_plan_count']; ?> plan<?php echo $s['lesson_plan_count'] != 1 ? 's' : ''; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ((int)$s['published_count'] > 0): ?>
                                        <span class="badge" style="background:#3b6d11;">
                                            <?php echo (int)$s['published_count']; ?> published
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo $basePath; ?>/iep/implementation/workspace/<?php echo (int)$s['iep_id']; ?>"
                                   class="btn btn-sm w-100 fw-semibold"
                                   style="background:#a01422;color:#fff;border:none;">
                                    <i class="ti ti-layout-dashboard me-1"></i>Implement IEP
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div><!-- /container-fluid -->
</div><!-- /main-content -->

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
