<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P2 Review List (Guidance/Principal)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-clipboard-check"></i> Review IEP P2 Assessments
                </h1>
                <p class="text-muted mt-2">Pending P2 assessments requiring your review and signature</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #a01422;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending Review</h6>
                        <h3 style="color: #a01422;"><?php echo count(array_filter($p2Documents ?? [], fn($d) => $d['status'] === 'pending_review')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Reviewed</h6>
                        <h3 style="color: #3b6d11;"><?php echo count(array_filter($p2Documents ?? [], fn($d) => $d['status'] === 'reviewed_signed')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total</h6>
                        <h3 style="color: #1e4072;"><?php echo count($p2Documents ?? []); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- P2 Documents Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>Meeting Date</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($p2Documents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No pending assessments
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($p2Documents as $doc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['lrn'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($doc['meeting_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?php echo ucwords(str_replace('_', ' ', $doc['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/iep/p2/<?php echo $doc['id']; ?>/review" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
