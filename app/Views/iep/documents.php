<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS — IEP Documents Unified Dashboard

$pageTitle = 'IEP Documents - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-medical"></i> IEP Documents</h1>
            <p class="text-muted mb-0">
                <?php if ($_SESSION['role'] === 'sped_teacher' || $_SESSION['role'] === 'admin'): ?>
                    Manage P2 assessments and Final IEP documents
                <?php elseif ($_SESSION['role'] === 'principal'): ?>
                    Review, sign, and approve IEP documents
                <?php elseif ($_SESSION['role'] === 'guidance'): ?>
                    Review and sign IEP documents
                <?php else: ?>
                    IEP documents pending your signature
                <?php endif; ?>
            </p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    $role = $_SESSION['role'];
    // Build tabs based on role
    $tabs = [];
    if ($role === 'sped_teacher' || $role === 'admin') {
        $tabs = [
            ['id' => 'p2', 'label' => 'P2 — Developmental Assessment', 'icon' => 'bi-clipboard-data', 'badge' => count($p2Documents)],
            ['id' => 'p3', 'label' => 'P3 — Final IEP',               'icon' => 'bi-file-earmark-text', 'badge' => count($p3Documents)],
        ];
    } elseif ($role === 'principal') {
        $tabs = [
            ['id' => 'p2',       'label' => 'P2 Reviews',      'icon' => 'bi-clipboard-check', 'badge' => count($p2Documents)],
            ['id' => 'p3',       'label' => 'P3 Signatures',   'icon' => 'bi-pen',             'badge' => count($p3Documents)],
            ['id' => 'approval', 'label' => 'Approval Queue',  'icon' => 'bi-check-circle',    'badge' => count($approvalDocuments)],
        ];
    } elseif ($role === 'guidance') {
        $tabs = [
            ['id' => 'p2', 'label' => 'P2 Reviews',    'icon' => 'bi-clipboard-check', 'badge' => count($p2Documents)],
            ['id' => 'p3', 'label' => 'P3 Signatures', 'icon' => 'bi-pen',             'badge' => count($p3Documents)],
        ];
    } else {
        // Parent
        $tabs = [
            ['id' => 'p3', 'label' => 'Final IEP — Pending Signature', 'icon' => 'bi-pen', 'badge' => count($p3Documents)],
        ];
    }
    ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="iepDocTabs">
        <?php foreach ($tabs as $i => $tab): ?>
            <li class="nav-item">
                <button class="nav-link <?php echo $i === 0 ? 'active' : ''; ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-<?php echo $tab['id']; ?>">
                    <i class="<?php echo $tab['icon']; ?> me-1"></i>
                    <?php echo $tab['label']; ?>
                    <?php if ($tab['badge'] > 0): ?>
                        <span class="badge ms-1" style="background: #a01422;"><?php echo $tab['badge']; ?></span>
                    <?php endif; ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">

        <!-- ===== P2 TAB ===== -->
        <?php if (in_array($role, ['sped_teacher', 'guidance', 'principal', 'admin'])): ?>
        <div class="tab-pane fade <?php echo $tabs[0]['id'] === 'p2' ? 'show active' : ''; ?>" id="tab-p2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="background: #1e4072; color: white;">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>
                        <?php echo ($role === 'sped_teacher' || $role === 'admin') ? 'P2 Developmental Assessment Documents' : 'P2 Documents Pending Your Review'; ?>
                    </h5>
                    <?php if ($role === 'sped_teacher' || $role === 'admin'): ?>
                        <small class="opacity-75">Create from a completed meeting</small>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($p2Documents)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                            <p class="mt-2 mb-0">
                                <?php echo ($role === 'sped_teacher' || $role === 'admin')
                                    ? 'No P2 documents yet. Create one from a completed meeting.'
                                    : 'No P2 documents pending your review.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background: #f5f5f5;">
                                    <tr>
                                        <th>Student</th>
                                        <th>Meeting Date</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($p2Documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($doc['student_name']); ?></strong><br>
                                            <small class="text-muted">LRN: <?php echo htmlspecialchars($doc['lrn'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($doc['meeting_date'])); ?></td>
                                        <td>
                                            <?php
                                            $sc = ['draft' => 'secondary', 'pending_review' => 'warning', 'reviewed_signed' => 'success'];
                                            $color = $sc[$doc['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $doc['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo $basePath; ?>/iep/p2/<?php echo $doc['id']; ?>/review"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                                <?php echo ($role === 'sped_teacher' || $role === 'admin') ? 'View' : 'Review & Sign'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($role === 'sped_teacher' || $role === 'admin'): ?>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        To create a P2 document, go to
                        <a href="<?php echo $basePath; ?>/iep/meetings" style="color: #a01422;">IEP Meetings</a>
                        and open a completed meeting.
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ===== P3 TAB ===== -->
        <?php
        $p3TabActive = ($role === 'parent') || ($tabs[0]['id'] === 'p3') ? 'show active' : '';
        $p3TabIndex  = ($role === 'principal' || $role === 'guidance') ? 1 : 0;
        $isFirstTab  = ($role === 'parent');
        ?>
        <div class="tab-pane fade <?php echo $isFirstTab ? 'show active' : ''; ?>" id="tab-p3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="background: #1e4072; color: white;">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>
                        <?php echo ($role === 'sped_teacher' || $role === 'admin')
                            ? 'Final IEP (P3) Documents'
                            : 'Final IEP Documents Pending Your Signature'; ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($p3Documents)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                            <p class="mt-2 mb-0">
                                <?php echo ($role === 'sped_teacher' || $role === 'admin')
                                    ? 'No Final IEP documents yet.'
                                    : 'No Final IEP documents pending your signature.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background: #f5f5f5;">
                                    <tr>
                                        <th>Student</th>
                                        <th>Meeting Date</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($p3Documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($doc['student_name']); ?></strong><br>
                                            <small class="text-muted">LRN: <?php echo htmlspecialchars($doc['lrn'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($doc['meeting_date'])); ?></td>
                                        <td>
                                            <?php
                                            $sc3 = ['draft' => 'secondary', 'pending_signatures' => 'warning', 'signed_approved' => 'success'];
                                            $color3 = $sc3[$doc['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color3; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $doc['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                        <td>
                                            <?php if ($role === 'sped_teacher' || $role === 'admin'): ?>
                                                <a href="<?php echo $basePath; ?>/iep/p3/<?php echo $doc['id']; ?>/sign"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo $basePath; ?>/iep/p3/<?php echo $doc['id']; ?>/sign"
                                                   class="btn btn-sm btn-primary"
                                                   style="background: #a01422; border-color: #a01422;">
                                                    <i class="bi bi-pen"></i> Sign
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($role === 'sped_teacher' || $role === 'admin'): ?>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        To create a Final IEP, go to
                        <a href="<?php echo $basePath; ?>/iep/meetings" style="color: #a01422;">IEP Meetings</a>
                        and open a completed meeting.
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== APPROVAL TAB (Principal only) ===== -->
        <?php if ($role === 'principal' || $role === 'admin'): ?>
        <div class="tab-pane fade" id="tab-approval">
            <div class="card">
                <div class="card-header" style="background: #1e4072; color: white;">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> Final IEP Approval Queue</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($approvalDocuments)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                            <p class="mt-2 mb-0">No documents pending final approval.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background: #f5f5f5;">
                                    <tr>
                                        <th>Student</th>
                                        <th>LRN</th>
                                        <th>Created By</th>
                                        <th>Created</th>
                                        <th>Signatures</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approvalDocuments as $doc): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doc['student_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($doc['lrn']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['created_by_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                                        <td>
                                            <span class="badge" style="background: #1e4072;">
                                                <?php echo count($doc['signatures']); ?> signed
                                            </span>
                                        </td>
                                        <td class="d-flex gap-1">
                                            <a href="<?php echo $basePath; ?>/iep/p3/<?php echo $doc['id']; ?>/sign"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm"
                                                    style="background: #a01422; color: white;"
                                                    onclick="approveDoc(<?php echo $doc['id']; ?>)">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-lock me-1"></i>
                        Approving a Final IEP locks it permanently — no further edits allowed.
                    </small>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /tab-content -->
</div>

<script>
function approveDoc(docId) {
    if (!confirm('Approve this Final IEP? This will lock the document permanently.')) return;
    fetch('<?php echo $basePath; ?>/iep/documents/' + docId + '/approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error approving document');
        }
    })
    .catch(() => alert('Error approving document'));
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
