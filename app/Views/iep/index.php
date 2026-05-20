<?php
// DO NOT ALTER WITHOUT APPROVAL -- Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS -- IEP Repository (list all IEPs)

$pageTitle = 'IEP Repository - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
$role     = $_SESSION['role'];
$basePath = BASE_PATH;
$statusColors = ['draft'=>'#6c757d','signing'=>'#ffc107','signed'=>'#3b6d11','locked'=>'#a01422'];
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-1" style="color:#1e4072;">
                <i class="bi bi-archive me-2"></i>IEP Repository
            </h1>
            <p class="text-muted mb-0">Individualized Education Plans</p>
        </div>
        <?php if (in_array($role, ['sped_teacher','admin'])): ?>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn" style="background:#a01422;color:white;" data-bs-toggle="modal" data-bs-target="#newIepStudentModal">
                <i class="bi bi-plus-lg me-1"></i>New IEP
            </button>
            <a class="btn btn-outline-secondary" href="<?php echo $basePath; ?>/students"><i class="bi bi-people me-1"></i>Students</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="school_year" class="form-select form-select-sm" style="min-width:140px;">
                <option value="">All School Years</option>
                <?php
                $years = array_unique(array_column($ieps ?? [], 'school_year'));
                foreach ($years as $y): ?>
                <option value="<?php echo htmlspecialchars($y); ?>"
                        <?php echo ($filterYear ?? '') === $y ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($y); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['draft','signing','signed','locked'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo ($filterStatus ?? '') === $st ? 'selected' : ''; ?>>
                    <?php echo ucfirst($st); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm" style="background:#1e4072;color:white;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="<?php echo $basePath; ?>/iep" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
        </div>
    </form>

    <!-- IEP Table -->
    <div class="card">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>
                IEPs (<?php echo count($ieps ?? []); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ieps)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                <p class="mt-2">No IEPs found.</p>
                <?php if (in_array($role, ['sped_teacher','admin'])): ?>
                <p class="text-muted small">Students with a signed PDSP will appear in the "New IEP" dropdown above.</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#1e4072;color:white;">
                        <tr>
                            <th>Student</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Re-evaluation Date</th>
                            <th>Drafted By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ieps as $iep): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($iep['student_name']); ?></strong><br>
                            <small class="text-muted">LRN: <?php echo htmlspecialchars($iep['lrn']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($iep['school_year']); ?></td>
                        <td>
                            <span class="badge" style="background:<?php echo $statusColors[$iep['status']] ?? '#6c757d'; ?>;">
                                <?php echo ucfirst($iep['status']); ?>
                            </span>
                            <?php if (!empty($iep['re_evaluation_date']) && strtotime($iep['re_evaluation_date']) < time() && in_array($iep['status'],['signed','locked'])): ?>
                            <span class="badge ms-1" style="background:#a01422;">Re-eval Passed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($iep['re_evaluation_date'])
                                ? date('M d, Y', strtotime($iep['re_evaluation_date']))
                                : '<span class="text-muted">Not set</span>'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($iep['drafted_by_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($iep['created_at'])); ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="<?php echo $basePath; ?>/iep/form/<?php echo $iep['id']; ?>"
                                   class="btn btn-sm" style="background:#1e4072;color:white;">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="<?php echo $basePath; ?>/iep/print/<?php echo $iep['id']; ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer me-1"></i>Print
                                </a>
                                <?php if ($iep['status'] === 'draft' && in_array($role, ['sped_teacher','admin'])): ?>
                                <form method="POST" action="<?php echo $basePath; ?>/iep/draft/<?php echo (int)$iep['id']; ?>/delete" class="d-inline"
                                      onsubmit="return confirm('Delete this draft permanently? This cannot be undone.');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i>Delete draft
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (in_array($role, ['sped_teacher','admin'])): ?>
<div class="modal fade" id="newIepStudentModal" tabindex="-1" aria-labelledby="newIepStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="newIepStudentModalLabel">Choose a student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Students need a signed PDSP (Process 4). No duplicate draft for the same learner this year.</p>
                <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="newIepEligibleReload">Reload list</button>
                <div id="newIepEligibleList" class="list-group">
                    <?php foreach ($eligibleStudents ?? [] as $s): ?>
                    <a class="list-group-item list-group-item-action" href="<?php echo $basePath; ?>/iep/create?student_id=<?php echo (int)$s['id']; ?>">
                        <strong><?php echo htmlspecialchars($s['student_name']); ?></strong>
                        <span class="text-muted small d-block">LRN <?php echo htmlspecialchars($s['lrn']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <p class="small text-muted mt-3 mb-0" id="newIepEligibleEmpty" style="<?php echo !empty($eligibleStudents) ? 'display:none;' : ''; ?>">No eligible students right now. Complete PDSP signing or open the full <a href="<?php echo $basePath; ?>/students">students list</a>.</p>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var basePath = <?php echo json_encode($basePath); ?>;
    var listEl = document.getElementById('newIepEligibleList');
    var emptyEl = document.getElementById('newIepEligibleEmpty');
    var btn = document.getElementById('newIepEligibleReload');
    if (!listEl || !btn) return;
    function renderList(students) {
        listEl.innerHTML = '';
        (students || []).forEach(function (s) {
            var a = document.createElement('a');
            a.className = 'list-group-item list-group-item-action';
            a.href = basePath + '/iep/create?student_id=' + encodeURIComponent(s.id);
            a.innerHTML = '<strong>' + (s.student_name || '') + '</strong><span class="text-muted small d-block">LRN ' + (s.lrn || '') + '</span>';
            listEl.appendChild(a);
        });
        if (emptyEl) emptyEl.style.display = (students && students.length) ? 'none' : 'block';
    }
    btn.addEventListener('click', function () {
        fetch(basePath + '/iep/ajax/eligible-students', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.success) renderList(j.students || []); })
            .catch(function () {});
    });
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
