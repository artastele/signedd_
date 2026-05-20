<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Review List (SPED Teacher)

$pageTitle = 'Review Enrollments - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-clipboard-check text-primary"></i> Review Enrollments
    </h1>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $pending = array_filter($enrollments, fn($e) => $e['status'] === 'pending');
        $verified = array_filter($enrollments, fn($e) => $e['status'] === 'verified');
        $rejected = array_filter($enrollments, fn($e) => $e['status'] === 'rejected');
        ?>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <i class="bi bi-clock-history"></i> Pending Review
                    </h5>
                    <h2 class="mb-0"><?php echo count($pending); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">
                        <i class="bi bi-check-circle"></i> Verified
                    </h5>
                    <h2 class="mb-0"><?php echo count($verified); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">
                        <i class="bi bi-x-circle"></i> Rejected
                    </h5>
                    <h2 class="mb-0"><?php echo count($rejected); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Enrollments</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enrollments)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No enrollments found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Student Name</th>
                                <th>Parent</th>
                                <th>Type</th>
                                <th>Grade Level</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><?php echo $enrollment['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['parent_name']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($enrollment['enrollment_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></td>
                                    <td><?php echo $enrollment['submitted_at'] ? date('M j, Y', strtotime($enrollment['submitted_at'])) : 'Draft'; ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'verified' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$enrollment['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/enrollment/review/<?php echo $enrollment['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Review
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
