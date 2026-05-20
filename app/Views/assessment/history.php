<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-07
// Part of: SPED LMS — Assessment History View

$pageTitle = 'Assessment History - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-2">
                <i class="bi bi-clock-history text-primary"></i> 
                Assessment History
            </h1>
            <p class="text-muted mb-0">
                Complete assessment history for <strong><?php echo htmlspecialchars($student['student_name']); ?></strong> 
                (LRN: <?php echo htmlspecialchars($student['lrn']); ?>)
            </p>
        </div>
        <div>
            <a href="<?php echo $basePath; ?>/students/view/<?php echo $student['id']; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Student
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Student Name:</strong><br>
                    <?php echo htmlspecialchars($student['student_name']); ?>
                </div>
                <div class="col-md-2">
                    <strong>LRN:</strong><br>
                    <?php echo htmlspecialchars($student['lrn']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Date of Birth:</strong><br>
                    <?php echo date('F d, Y', strtotime($student['date_of_birth'])); ?>
                </div>
                <div class="col-md-4">
                    <strong>Disability Type:</strong><br>
                    <?php echo htmlspecialchars($student['disability_type']); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($history)): ?>
        <!-- No History -->
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>No assessment history found.</strong> 
            This student has not been assessed yet.
        </div>
    <?php else: ?>
        <!-- Assessment History Timeline -->
        <div class="card">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> 
                    Assessment Versions (<?php echo count($history); ?> total)
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="assessmentAccordion">
                    <?php foreach ($history as $index => $assessment): ?>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="heading<?php echo $assessment['id']; ?>">
                                <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?php echo $assessment['id']; ?>" 
                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                        aria-controls="collapse<?php echo $assessment['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <span class="badge bg-primary me-2">Version <?php echo $assessment['version']; ?></span>
                                            <?php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'finalized' => 'primary',
                                                'approved' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $statusColor = $statusColors[$assessment['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $statusColor; ?> me-2">
                                                <?php echo ucfirst($assessment['status']); ?>
                                            </span>
                                            <strong>Conducted by:</strong> <?php echo htmlspecialchars($assessment['conducted_by_name'] ?? 'Unknown'); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo date('F d, Y g:i A', strtotime($assessment['created_at'])); ?>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $assessment['id']; ?>" 
                                 class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                 aria-labelledby="heading<?php echo $assessment['id']; ?>" 
                                 data-bs-parent="#assessmentAccordion">
                                <div class="accordion-body">
                                    
                                    <!-- Section A: Learner Information -->
                                    <h6 class="text-secondary mb-3">
                                        <i class="bi bi-person-badge"></i> Section A: Learner Information
                                    </h6>
                                    <?php if (!empty($assessment['section_a_data'])): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <table class="table table-sm table-bordered">
                                                    <tr>
                                                        <th style="width: 40%;">Full Name</th>
                                                        <td>
                                                            <?php 
                                                            echo htmlspecialchars(
                                                                ($assessment['section_a_data']['first_name'] ?? '') . ' ' .
                                                                ($assessment['section_a_data']['middle_name'] ?? '') . ' ' .
                                                                ($assessment['section_a_data']['last_name'] ?? '') . ' ' .
                                                                ($assessment['section_a_data']['extension_name'] ?? '')
                                                            ); 
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Birth Date</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['birth_date'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Age</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['age'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Sex</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['sex'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Religion</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['religion'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Home Address</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['home_address'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm table-bordered">
                                                    <tr>
                                                        <th style="width: 40%;">LRN</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['lrn'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>School</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['school'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>School Year</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['school_year'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Adviser</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['adviser_name'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Previous School</th>
                                                        <td><?php echo htmlspecialchars($assessment['section_a_data']['previous_school'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>With IEP</th>
                                                        <td><?php echo ucfirst($assessment['section_a_data']['with_iep'] ?? 'no'); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No Section A data available</p>
                                    <?php endif; ?>

                                    <!-- Services Checklist -->
                                    <h6 class="text-secondary mb-3 mt-4">
                                        <i class="bi bi-check2-square"></i> Services Checked
                                    </h6>
                                    <?php if (!empty($assessment['services_checked'])): ?>
                                        <div class="mb-3">
                                            <?php foreach ($assessment['services_checked'] as $service): ?>
                                                <span class="badge" style="background-color: #1e4072; margin-right: 8px; margin-bottom: 8px;">
                                                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($service); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No services checked</p>
                                    <?php endif; ?>

                                    <!-- Screening Types -->
                                    <?php if (!empty($assessment['screening_types'])): ?>
                                        <h6 class="text-secondary mb-3 mt-4">
                                            <i class="bi bi-clipboard-check"></i> Screening Types
                                        </h6>
                                        <div class="mb-3">
                                            <?php foreach ($assessment['screening_types'] as $screening): ?>
                                                <span class="badge bg-success me-2 mb-2">
                                                    <?php echo htmlspecialchars($screening); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Section B: MDT Assessment -->
                                    <?php if (!empty($assessment['mdt_services'])): ?>
                                        <h6 class="text-secondary mb-3 mt-4">
                                            <i class="bi bi-table"></i> Section B: Multi-Disciplinary Team Assessment
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead style="background-color: #1e4072; color: white;">
                                                    <tr>
                                                        <th>Service</th>
                                                        <th>MDT Members</th>
                                                        <th>Assessment Date</th>
                                                        <th>Documents</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assessment['mdt_services'] as $service): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></td>
                                                            <td>
                                                                <?php if (!empty($service['mdt_members'])): ?>
                                                                    <ul class="mb-0 ps-3">
                                                                        <?php foreach ($service['mdt_members'] as $member): ?>
                                                                            <li>
                                                                                <strong><?php echo htmlspecialchars($member['name']); ?></strong>
                                                                                <?php if (!empty($member['designation'])): ?>
                                                                                    - <?php echo htmlspecialchars($member['designation']); ?>
                                                                                <?php endif; ?>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No members</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo date('F d, Y', strtotime($service['date_of_assessment'])); ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($service['documents'])): ?>
                                                                    <?php foreach ($service['documents'] as $doc): ?>
                                                                        <div class="mb-1">
                                                                            <a href="<?php echo $basePath; ?>/file/view/assessment/<?php echo $doc['id']; ?>" 
                                                                               target="_blank" class="btn btn-sm btn-outline-primary no-print">
                                                                                <i class="bi bi-file-earmark-text"></i> 
                                                                                <?php echo htmlspecialchars($doc['original_name']); ?>
                                                                            </a>
                                                                            <span class="print-only"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No documents</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Timestamps -->
                                    <div class="mt-4 pt-3 border-top">
                                        <div class="row text-muted small">
                                            <div class="col-md-6">
                                                <strong>Created:</strong> <?php echo date('F d, Y g:i A', strtotime($assessment['created_at'])); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Last Updated:</strong> <?php echo date('F d, Y g:i A', strtotime($assessment['updated_at'])); ?>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .print-only {
        display: inline !important;
    }
    .accordion-button:not(.collapsed) {
        background-color: white !important;
    }
    .accordion-collapse {
        display: block !important;
    }
}
.print-only {
    display: none;
}
.accordion-button {
    background-color: #f8f9fa;
}
.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #1e4072;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
