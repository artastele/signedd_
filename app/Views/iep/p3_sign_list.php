<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P3 Sign List (All Signers)

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
                    <i class="fas fa-pen"></i> Sign IEP Documents
                </h1>
                <p class="text-muted mt-2">IEP P3 documents pending your signature</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending Signature</h6>
                        <h3 style="color: #ffc107;"><?php echo count($p3Documents ?? []); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Signed</h6>
                        <h3 style="color: #3b6d11;">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total</h6>
                        <h3 style="color: #1e4072;"><?php echo count($p3Documents ?? []); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- P3 Documents Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>Meeting Date</th>
                            <th>Status</th>
                            <th>Signatures</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($p3Documents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No pending documents
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($p3Documents as $doc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['lrn'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($doc['meeting_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?php echo ucwords(str_replace('_', ' ', $doc['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Pending</span>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/iep/p3/<?php echo $doc['id']; ?>/sign" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pen"></i> Sign
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
