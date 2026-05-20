<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Assign IEP Form

$pageTitle = 'Assign IEP - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-plus-circle text-primary"></i> Assign IEP to Student
        </h1>
        <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> IEP Assignment Form</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($students)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-info-circle" style="font-size: 4rem; color: #a01422;"></i>
                            <h5 class="mt-3">No Students Ready for IEP Assignment</h5>
                            <p class="text-muted">
                                Students must have an approved IEP P3 document before they can be assigned an IEP for implementation.
                            </p>
                            <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo $basePath; ?>/iep/implementation/assign">
                            <!-- Student Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-person"></i> Select Student *
                                </label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">-- Choose Student --</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['student_id']; ?>" 
                                                data-iep-id="<?php echo $student['iep_p3_id']; ?>">
                                            <?php echo htmlspecialchars($student['student_name']); ?> 
                                            (LRN: <?php echo htmlspecialchars($student['lrn']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    Only students with approved IEP P3 documents are shown
                                </small>
                            </div>

                            <!-- Hidden IEP ID (auto-filled by JavaScript) -->
                            <input type="hidden" name="iep_id" id="iep_id" required>

                            <!-- Start Date -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar"></i> Implementation Start Date *
                                </label>
                                <input type="date" 
                                       name="start_date" 
                                       class="form-control" 
                                       value="<?php echo date('Y-m-d'); ?>"
                                       min="<?php echo date('Y-m-d'); ?>"
                                       required>
                                <small class="text-muted">
                                    When should the IEP implementation begin?
                                </small>
                            </div>

                            <!-- Initial Notes -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-pencil"></i> Initial Notes (Optional)
                                </label>
                                <textarea name="notes" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Add any initial notes or observations about the IEP implementation..."></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Assign IEP
                                </button>
                                <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="text-primary"><i class="bi bi-info-circle"></i> What happens after assignment?</h6>
                    <ul class="mb-0">
                        <li>You can upload learning materials for the student</li>
                        <li>You can create interactive activities</li>
                        <li>The student will see materials in their learning dashboard</li>
                        <li>You can track the student's progress</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill IEP ID when student is selected
document.querySelector('select[name="student_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const iepId = selectedOption.getAttribute('data-iep-id');
    document.getElementById('iep_id').value = iepId || '';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
