<?php
// DO NOT ALTER WITHOUT APPROVAL — Student Records
// Last modified: 2026-05-06
// Part of: SPED LMS — Student Record Detail

$pageTitle = 'Student Record - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?php echo $basePath; ?>/students" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Student Records
        </a>
        <a href="<?php echo $basePath; ?>/assessment/history/<?php echo $student['id']; ?>" class="btn btn-primary">
            <i class="bi bi-clock-history"></i> View Assessment History
        </a>
    </div>

    <h1 class="mb-4">
        <i class="bi bi-person-badge text-primary"></i> Student Record
    </h1>

    <!-- Student Information Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-fill"></i> Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>LRN:</strong> <?php echo htmlspecialchars($student['lrn']); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student['student_name']); ?></p>
                    <p><strong>Birth Date:</strong> <?php echo date('F d, Y', strtotime($student['date_of_birth'])); ?></p>
                    <p><strong>Disability Type:</strong> <?php echo htmlspecialchars($student['disability_type'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>PSA Number:</strong> <?php echo htmlspecialchars($student['psa_number'] ?? 'N/A'); ?></p>
                    <p><strong>PWD ID Number:</strong> <?php echo htmlspecialchars($student['pwd_id_number'] ?? 'N/A'); ?></p>
                    <p><strong>Parent/Guardian:</strong> <?php echo htmlspecialchars($student['parent_name'] ?? 'N/A'); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($student['contact_number'] ?? 'N/A'); ?></p>
                    <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment History -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Enrollment History</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enrollments)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No enrollment history found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>School Year</th>
                                <th>Type</th>
                                <th>Grade Level</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Verified By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($enrollment['school_year']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($enrollment['enrollment_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'verified' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$enrollment['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['submitted_at'])); ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($enrollment['verifier_name'] ?? 'Pending'); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/enrollment/review/<?php echo $enrollment['id']; ?>" 
                                           class="btn btn-sm btn-info">
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

    <!-- All Documents -->
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> All Documents</h5>
        </div>
        <div class="card-body">
            <?php if (empty($allDocuments)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No documents found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Document Type</th>
                                <th>School Year</th>
                                <th>Enrollment Type</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Reviewed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allDocuments as $doc): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo ucwords(str_replace('_', ' ', $doc['document_type'])); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['enrollment_year']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($doc['enrollment_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$doc['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($doc['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($doc['reviewer_name'] ?? 'Pending'); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/<?php echo $doc['file_path']; ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> View
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
