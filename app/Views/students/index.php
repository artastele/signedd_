<?php
// DO NOT ALTER WITHOUT APPROVAL — Student Records
// Last modified: 2026-05-06
// Part of: SPED LMS — Student Records List

$pageTitle = 'Student Records - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-person-lines-fill text-primary"></i> Student Records
    </h1>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Students</h6>
                    <h3 class="text-primary"><?php echo count($students); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Active Enrollments</h6>
                    <h3 class="text-success">
                        <?php echo count(array_filter($students, fn($s) => $s['enrollment_status'] === 'verified')); ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Pending Verification</h6>
                    <h3 class="text-warning">
                        <?php echo count(array_filter($students, fn($s) => $s['enrollment_status'] === 'pending')); ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted">This School Year</h6>
                    <h3 class="text-info">
                        <?php 
                        $currentSY = date('Y') . '-' . (date('Y') + 1);
                        echo count(array_filter($students, fn($s) => $s['latest_school_year'] === $currentSY)); 
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-table"></i> All Students</h5>
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No student records found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>LRN</th>
                                <th>Student Name</th>
                                <th>Birth Date</th>
                                <th>Disability Type</th>
                                <th>Current Grade</th>
                                <th>Parent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['lrn']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></td>
                                    <td><?php echo htmlspecialchars($student['disability_type'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($student['current_grade_level']): ?>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($student['current_grade_level']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'verified' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$student['enrollment_status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($student['enrollment_status'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/students/view/<?php echo $student['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
