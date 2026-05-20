<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Review (SPED Teacher)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-clipboard-check"></i> Assessment Review
                </h1>
                <p class="text-muted mt-2">Review and approve/reject learner assessment</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Assessment Status Card -->
        <div class="card mb-4" style="border-top: 4px solid #1e4072;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Student Information</h6>
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($assessment['student_name']); ?></p>
                        <p class="mb-1"><strong>LRN:</strong> <code><?php echo htmlspecialchars($assessment['lrn']); ?></code></p>
                        <p class="mb-1"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($assessment['date_of_birth']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Assessment Information</h6>
                        <p class="mb-1"><strong>Quarter:</strong> <span class="badge" style="background-color: #1e4072;"><?php echo htmlspecialchars($assessment['quarter']); ?></span></p>
                        <p class="mb-1"><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($assessment['submitted_at'])); ?></p>
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <?php 
                            $statusColor = match($assessment['status']) {
                                'pending' => '#ffc107',
                                'approved' => '#3b6d11',
                                'rejected' => '#dc3545',
                                default => '#6c757d'
                            };
                            ?>
                            <span class="badge" style="background-color: <?php echo $statusColor; ?>;">
                                <?php echo ucfirst($assessment['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section A: Education History -->
        <div class="card mb-4 border-left-navy">
            <div class="card-header bg-light" style="border-left: 4px solid #1e4072;">
                <h5 class="mb-0" style="color: #1e4072;">
                    <i class="fas fa-book"></i> Section A.2: Education History
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Data is stored in section_a_data for SPED teacher assessments
                // education_history is the legacy key for old parent-submitted assessments
                $sectionA = $assessment['section_a_data'] ?? $assessment['education_history'] ?? [];
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Previous School:</strong></p>
                        <p class="text-muted"><?php echo htmlspecialchars($sectionA['previous_school'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Grade Level:</strong></p>
                        <p class="text-muted"><?php echo htmlspecialchars($sectionA['previous_grade_level'] ?? $sectionA['grade_level'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>With IEP:</strong></p>
                        <p class="text-muted"><?php echo ucfirst($sectionA['with_iep'] ?? 'No'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>With Support Services:</strong></p>
                        <p class="text-muted"><?php echo ucfirst($sectionA['with_support_services'] ?? 'No'); ?></p>
                    </div>
                </div>

                <?php if (!empty($sectionA['support_services_detail'])): ?>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Support Services Detail:</strong></p>
                            <p class="text-muted"><?php echo htmlspecialchars($sectionA['support_services_detail']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Show services_checked if available (SPED teacher flow)
                $servicesChecked = $assessment['services_checked'] ?? [];
                if (!empty($servicesChecked)):
                ?>
                    <div class="row mt-2">
                        <div class="col-12">
                            <p><strong>Services Checked:</strong></p>
                            <div>
                                <?php foreach ($servicesChecked as $service): ?>
                                    <span class="badge me-1 mb-1" style="background: #1e4072;">
                                        <?php echo htmlspecialchars($service); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section B: Assessment Information -->
        <div class="card mb-4 border-left-crimson">
            <div class="card-header bg-light" style="border-left: 4px solid #a01422;">
                <h5 class="mb-0" style="color: #1e4072;">
                    <i class="fas fa-table"></i> Section B: MDT Assessment Information
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($assessment['mdt_services'])): ?>
                    <?php foreach ($assessment['mdt_services'] as $service): ?>
                        <div class="card mb-3" style="border-left: 3px solid #1e4072;">
                            <div class="card-body">
                                <h6 class="text-primary mb-3" style="color: #a01422 !important;">
                                    <i class="bi bi-briefcase"></i> <?php echo htmlspecialchars($service['service_name']); ?>
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <p class="mb-1"><strong>MDT Members:</strong></p>
                                        <?php 
                                        $members = is_string($service['mdt_members']) ? json_decode($service['mdt_members'], true) : $service['mdt_members'];
                                        if (!empty($members)): 
                                        ?>
                                            <ul class="list-unstyled ms-3">
                                                <?php foreach ($members as $member): ?>
                                                    <li class="mb-1">
                                                        <i class="bi bi-person-fill text-muted"></i>
                                                        <?php echo htmlspecialchars($member['name']); ?>
                                                        <?php if (!empty($member['designation'])): ?>
                                                            <small class="text-muted">(<?php echo htmlspecialchars($member['designation']); ?>)</small>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-muted ms-3">No members listed</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <p class="mb-1"><strong>Date of Assessment:</strong></p>
                                        <p class="text-muted ms-3">
                                            <?php echo $service['date_of_assessment'] ? date('M d, Y', strtotime($service['date_of_assessment'])) : 'Not specified'; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Supporting Documents -->
                                <?php if (!empty($service['documents'])): ?>
                                    <div class="mt-3">
                                        <p class="mb-2"><strong>Supporting Documents:</strong></p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($service['documents'] as $doc): ?>
                                                <div class="card" style="width: 200px;">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-file-earmark-pdf text-danger me-2" style="font-size: 1.5rem;"></i>
                                                            <div class="flex-grow-1" style="min-width: 0;">
                                                                <p class="mb-0 small text-truncate" title="<?php echo htmlspecialchars($doc['original_name']); ?>">
                                                                    <?php echo htmlspecialchars($doc['original_name']); ?>
                                                                </p>
                                                                <small class="text-muted">
                                                                    <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <a href="<?php echo BASE_PATH; ?>/uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary w-100 mt-2">
                                                            <i class="bi bi-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle"></i> No documents uploaded for this service
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> No MDT assessment information available
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Review Actions -->
        <?php if ($assessment['status'] === 'pending'): ?>
            <div class="card mb-4" style="border-top: 4px solid #a01422;">
                <div class="card-header bg-light">
                    <h5 class="mb-0" style="color: #1e4072;">
                        <i class="fas fa-check-circle"></i> Review Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-lg btn-success w-100" id="approveBtn">
                                <i class="fas fa-check"></i> Approve Assessment
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-lg btn-danger w-100" id="rejectBtn">
                                <i class="fas fa-times"></i> Reject Assessment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> This assessment has already been reviewed.
                        <?php if ($assessment['status'] === 'approved'): ?>
                            <strong>Status: Approved</strong> on <?php echo date('M d, Y', strtotime($assessment['reviewed_at'])); ?>
                        <?php elseif ($assessment['status'] === 'rejected'): ?>
                            <strong>Status: Rejected</strong> on <?php echo date('M d, Y', strtotime($assessment['reviewed_at'])); ?>
                            <br><strong>Reason:</strong> <?php echo htmlspecialchars($assessment['review_note']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?php echo BASE_PATH; ?>/assessment" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Assessments
            </a>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title">Reject Assessment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea id="rejectionReason" class="form-control" rows="4" placeholder="Please provide a detailed reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject Assessment</button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-crimson {
    border-left: 4px solid #a01422 !important;
}

.border-left-navy {
    border-left: 4px solid #1e4072 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const assessmentId = <?php echo $assessment['id']; ?>;
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));

    // Approve button
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to approve this assessment?')) {
                fetch('<?php echo BASE_PATH; ?>/assessment/' + assessmentId + '/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Assessment approved successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = '<?php echo BASE_PATH; ?>/assessment';
                        }, 2000);
                    } else {
                        showAlert(data.message || 'Error approving assessment', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error approving assessment', 'danger');
                });
            }
        });
    }

    // Reject button
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            rejectModal.show();
        });
    }

    // Confirm reject button
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            const reason = document.getElementById('rejectionReason').value.trim();
            
            if (!reason) {
                alert('Please provide a reason for rejection');
                return;
            }

            const formData = new FormData();
            formData.append('reason', reason);

            fetch('<?php echo BASE_PATH; ?>/assessment/' + assessmentId + '/reject', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Assessment rejected successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_PATH; ?>/assessment';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Error rejecting assessment', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error rejecting assessment', 'danger');
            });
        });
    }

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertDiv);
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
