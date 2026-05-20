<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Review Detail (SPED Teacher)

$pageTitle = 'Review Enrollment - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Document type labels
$docLabels = [
    'psa_birth_cert' => 'PSA Birth Certificate',
    'pwd_id' => 'PWD ID',
    'medical_record' => 'Medical Record',
    'beef_form' => 'BEEF Form'
];

// Status colors
$statusColors = [
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger'
];
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- VERSION MARKER - v1.8 Simplified Approval -->
    <div class="alert alert-info mb-3" style="background: #e3f2fd; border-left: 4px solid #2196F3;">
        <strong>✨ NEW:</strong> Simplified approval system - Review all documents, then use single approve/reject buttons at bottom
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-clipboard-check text-primary"></i> Review Enrollment
        </h1>
        <a href="<?php echo $basePath; ?>/enrollment/review" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Enrollment Status Card -->
    <div class="card mb-4 border-<?php echo $statusColors[$enrollment['status']]; ?>">
        <div class="card-header bg-<?php echo $statusColors[$enrollment['status']]; ?> text-white">
            <h5 class="mb-0">
                <i class="bi bi-info-circle"></i> Enrollment Status: 
                <strong><?php echo strtoupper($enrollment['status']); ?></strong>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Enrollment ID:</strong><br>
                    #<?php echo $enrollment['id']; ?>
                </div>
                <div class="col-md-3">
                    <strong>Type:</strong><br>
                    <?php echo ucfirst($enrollment['enrollment_type']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Submitted:</strong><br>
                    <?php echo date('M j, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
                </div>
                <div class="col-md-3">
                    <strong>Parent:</strong><br>
                    <?php echo htmlspecialchars($enrollment['parent_name']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Learner Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-fill"></i> Section 1: Learner Information</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>LRN:</strong> <?php echo htmlspecialchars($enrollment['lrn'] ?? 'Not provided'); ?>
                </div>
                <div class="col-md-6">
                    <strong>Full Name:</strong> 
                    <?php 
                    echo htmlspecialchars($enrollment['first_name'] . ' ' . 
                         ($enrollment['middle_name'] ? $enrollment['middle_name'] . ' ' : '') . 
                         $enrollment['last_name'] . 
                         ($enrollment['extension_name'] ? ' ' . $enrollment['extension_name'] : ''));
                    ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Birth Date:</strong> <?php echo date('F j, Y', strtotime($enrollment['birth_date'])); ?>
                </div>
                <div class="col-md-4">
                    <strong>Sex:</strong> <?php echo htmlspecialchars($enrollment['sex']); ?>
                </div>
                <div class="col-md-4">
                    <strong>Age:</strong> <?php echo htmlspecialchars($enrollment['age'] ?? 'N/A'); ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Place of Birth:</strong> 
                    <?php echo htmlspecialchars(($enrollment['place_of_birth_city'] ?? '') . ', ' . ($enrollment['place_of_birth_province'] ?? '')); ?>
                </div>
                <div class="col-md-6">
                    <strong>Mother Tongue:</strong> <?php echo htmlspecialchars($enrollment['mother_tongue'] ?? 'N/A'); ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Indigenous People:</strong> 
                    <?php if ($enrollment['is_indigenous_people']): ?>
                        <span class="badge bg-info">Yes - <?php echo htmlspecialchars($enrollment['indigenous_group']); ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary">No</span>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <strong>4Ps Beneficiary:</strong> 
                    <?php if ($enrollment['is_4ps_beneficiary']): ?>
                        <span class="badge bg-info">Yes - <?php echo htmlspecialchars($enrollment['fourps_household_id']); ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary">No</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Disabilities -->
            <div class="alert alert-info">
                <strong><i class="bi bi-heart-pulse"></i> Disabilities:</strong><br>
                <?php
                $disabilities = [];
                if ($enrollment['disability_visual']) $disabilities[] = 'Visual Impairment';
                if ($enrollment['disability_hearing']) $disabilities[] = 'Hearing Impairment';
                if ($enrollment['disability_learning']) $disabilities[] = 'Learning Disability';
                if ($enrollment['disability_speech']) $disabilities[] = 'Speech/Language Impairment';
                if ($enrollment['disability_intellectual']) $disabilities[] = 'Intellectual Disability';
                if ($enrollment['disability_physical']) $disabilities[] = 'Physical Disability';
                if ($enrollment['disability_emotional']) $disabilities[] = 'Emotional-Behavioral Disorder';
                if ($enrollment['disability_chronic_illness']) $disabilities[] = 'Chronic Illness';
                if ($enrollment['disability_others']) $disabilities[] = 'Others: ' . htmlspecialchars($enrollment['disability_others_specify']);
                
                echo !empty($disabilities) ? implode(', ', $disabilities) : 'None specified';
                ?>
            </div>
        </div>
    </div>

    <!-- Section 2 & 3: Address Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Section 2 & 3: Address Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Current Address</h6>
                    <p>
                        <?php echo htmlspecialchars($enrollment['current_house_no'] ?? ''); ?><br>
                        Barangay <?php echo htmlspecialchars($enrollment['current_barangay'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_city'] ?? ''); ?>, 
                        <?php echo htmlspecialchars($enrollment['current_province'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_region'] ?? ''); ?> 
                        <?php echo htmlspecialchars($enrollment['current_zip_code'] ?? ''); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Permanent Address</h6>
                    <?php if ($enrollment['same_as_current_address']): ?>
                        <p class="text-muted"><em>Same as current address</em></p>
                    <?php else: ?>
                        <p>
                            <?php echo htmlspecialchars($enrollment['permanent_house_no'] ?? ''); ?><br>
                            Barangay <?php echo htmlspecialchars($enrollment['permanent_barangay'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($enrollment['permanent_province'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_region'] ?? ''); ?> 
                            <?php echo htmlspecialchars($enrollment['permanent_zip_code'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Parent/Guardian Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-people-fill"></i> Section 4: Parent/Guardian Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary">Father</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['father_first_name'] ?? '') . ' ' . 
                             ($enrollment['father_middle_name'] ?? '') . ' ' . 
                             ($enrollment['father_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['father_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Mother</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['mother_first_name'] ?? '') . ' ' . 
                             ($enrollment['mother_middle_name'] ?? '') . ' ' . 
                             ($enrollment['mother_maiden_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['mother_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Guardian</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['guardian_first_name'] ?? '') . ' ' . 
                             ($enrollment['guardian_middle_name'] ?? '') . ' ' . 
                             ($enrollment['guardian_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['guardian_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Previous School (if applicable) -->
    <?php if ($enrollment['enrollment_type'] !== 'new'): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-building"></i> Section 5: Previous School Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>School ID:</strong> <?php echo htmlspecialchars($enrollment['previous_school_id'] ?? 'N/A'); ?><br>
                    <strong>School Name:</strong> <?php echo htmlspecialchars($enrollment['previous_school_name'] ?? 'N/A'); ?><br>
                    <strong>Address:</strong> <?php echo htmlspecialchars($enrollment['previous_school_address'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-6">
                    <strong>Grade Level:</strong> <?php echo htmlspecialchars($enrollment['previous_grade_level'] ?? 'N/A'); ?><br>
                    <strong>School Year:</strong> <?php echo htmlspecialchars($enrollment['previous_school_year'] ?? 'N/A'); ?><br>
                    <strong>School Type:</strong> <?php echo htmlspecialchars($enrollment['previous_school_type'] ?? 'N/A'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 6: Enrollment Details -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-journal-text"></i> Section 6: Enrollment Details</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Grade Level to Enroll:</strong> 
                    <span class="badge bg-primary"><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></span>
                </div>
                <div class="col-md-6">
                    <strong>School Year:</strong> <?php echo htmlspecialchars($enrollment['school_year']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <strong>Balik-Aral:</strong> 
                    <span class="badge bg-<?php echo $enrollment['is_balik_aral'] ? 'success' : 'secondary'; ?>">
                        <?php echo $enrollment['is_balik_aral'] ? 'Yes' : 'No'; ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>PEPT Passer:</strong> 
                    <span class="badge bg-<?php echo $enrollment['is_pept_passer'] ? 'success' : 'secondary'; ?>">
                        <?php echo $enrollment['is_pept_passer'] ? 'Yes - ' . htmlspecialchars($enrollment['pept_rating']) : 'No'; ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>ALS Passer:</strong> 
                    <span class="badge bg-<?php echo $enrollment['is_als_passer'] ? 'success' : 'secondary'; ?>">
                        <?php echo $enrollment['is_als_passer'] ? 'Yes - ' . htmlspecialchars($enrollment['als_rating']) : 'No'; ?>
                    </span>
                </div>
            </div>
            <?php if ($enrollment['shs_track']): ?>
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>SHS Track:</strong> <?php echo htmlspecialchars($enrollment['shs_track']); ?> | 
                    <strong>Strand:</strong> <?php echo htmlspecialchars($enrollment['shs_strand']); ?> | 
                    <strong>Semester:</strong> <?php echo htmlspecialchars($enrollment['shs_semester']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 7: Learning Modality -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-laptop"></i> Section 7: Learning Modality</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                $modalities = [
                    'modality_modular_print' => 'Modular (Print)',
                    'modality_modular_digital' => 'Modular (Digital)',
                    'modality_online' => 'Online',
                    'modality_educational_tv' => 'Educational TV',
                    'modality_radio' => 'Radio',
                    'modality_blended' => 'Blended',
                    'modality_face_to_face' => 'Face-to-Face'
                ];
                
                foreach ($modalities as $key => $label):
                    if ($enrollment[$key]):
                ?>
                <div class="col-md-4 mb-2">
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?php echo $label; ?></span>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
            <?php if ($enrollment['preferred_distance_modality']): ?>
            <div class="mt-3">
                <strong>Preferred Distance Modality:</strong> 
                <span class="badge bg-primary"><?php echo htmlspecialchars($enrollment['preferred_distance_modality']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 8: Documents & Signature -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-file-earmark-check"></i> Section 8: Documents & Signature</h5>
        </div>
        <div class="card-body">
            <!-- Signature -->
            <div class="mb-4">
                <h6 class="text-primary">Parent/Guardian Signature</h6>
                <?php if ($enrollment['signature_data']): ?>
                    <div class="border p-2" style="max-width: 400px; background: #f9f9f9;">
                        <img src="<?php echo htmlspecialchars($enrollment['signature_data']); ?>" 
                             alt="Signature" class="img-fluid">
                    </div>
                    <small class="text-muted">Signed on: <?php echo date('F j, Y', strtotime($enrollment['date_signed'])); ?></small>
                <?php else: ?>
                    <p class="text-danger">No signature provided</p>
                <?php endif; ?>
            </div>

            <!-- Uploaded Documents -->
            <h6 class="text-primary mb-3">Uploaded Documents for Review</h6>
            <?php if (empty($documents)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No documents uploaded yet.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong><?php echo $docLabels[$doc['document_type']]; ?></strong>
                                <span class="badge bg-<?php echo $statusColors[$doc['status']]; ?>">
                                    <?php echo ucfirst($doc['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <!-- File Preview/Download -->
                                <div class="mb-3">
                                    <?php 
                                    $fileExt = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                    $fileUrl = $basePath . '/' . $doc['file_path'];
                                    ?>
                                    
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo $fileUrl; ?>" 
                                             alt="Document" class="img-fluid mb-2" style="max-height: 200px;">
                                    <?php else: ?>
                                        <div class="text-center p-3 bg-light">
                                            <i class="bi bi-file-earmark-pdf" style="font-size: 3rem;"></i>
                                            <p class="mb-0">PDF Document</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo $fileUrl; ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-eye"></i> View Document
                                    </a>
                                </div>
                                
                                <!-- Show review note if rejected -->
                                <?php if ($doc['status'] === 'rejected' && !empty($doc['review_note'])): ?>
                                    <div class="alert alert-danger mb-0">
                                        <small><strong>Rejection Reason:</strong><br><?php echo htmlspecialchars($doc['review_note']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action Buttons (Only if pending) -->
    <?php if ($enrollment['status'] === 'pending'): ?>
    <div class="card mb-4 border-success">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-clipboard-check"></i> Review Decision</h5>
            <p class="text-muted">After reviewing all documents and information above, make your decision:</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <form method="POST" action="<?php echo $basePath; ?>/enrollment/approve/<?php echo $enrollment['id']; ?>" 
                          onsubmit="return confirm('Are you sure you want to APPROVE this enrollment? All documents will be marked as verified.');">
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-check-circle-fill"></i> Approve Enrollment
                        </button>
                        <small class="text-muted d-block mt-2">This will verify all documents and approve the enrollment</small>
                    </form>
                </div>
                
                <div class="col-md-6 mb-3">
                    <button type="button" class="btn btn-danger btn-lg w-100" 
                            data-bs-toggle="modal" data-bs-target="#rejectEnrollmentModal">
                        <i class="bi bi-x-circle-fill"></i> Reject Enrollment
                    </button>
                    <small class="text-muted d-block mt-2">Provide feedback on what needs to be corrected</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Enrollment Modal -->
    <div class="modal fade" id="rejectEnrollmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Enrollment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo $basePath; ?>/enrollment/reject/<?php echo $enrollment['id']; ?>">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Note:</strong> The parent will be notified and can resubmit the enrollment with corrections.
                        </div>
                        
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">
                                <strong>Reason for Rejection <span class="text-danger">*</span></strong>
                            </label>
                            <textarea class="form-control" id="rejection_reason" 
                                      name="rejection_reason" rows="5" required
                                      placeholder="Please explain what needs to be corrected or why this enrollment is being rejected..."></textarea>
                            <div class="form-text">Be specific so the parent knows what to fix</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle-fill"></i> Reject Enrollment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Create Learner Account Section (Only if verified and account not yet created) -->
    <?php if ($enrollment['status'] === 'verified' && !$enrollment['learner_account_created']): ?>
    <div class="card mb-4 border-success" style="border-width: 3px;">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-person-plus-fill"></i> Process 2 Part 2: Create Learner Account</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle-fill"></i> Enrollment Verified!</h5>
                <p class="mb-0">All documents have been approved. You can now create the learner account and generate the LRN.</p>
            </div>
            
            <h6 class="mb-3">What will happen when you click the button below:</h6>
            <ul class="mb-4">
                <li><strong>Generate LRN</strong> (Learner Reference Number)</li>
                <li><strong>Create Student Record</strong> in the system</li>
                <li><strong>Create Learner Account</strong> with login credentials</li>
                <li><strong>Send Email & Notification</strong> to parent with LRN and login details</li>
            </ul>
            
            <button type="button" class="btn btn-success btn-lg w-100" 
                    onclick="createLearnerAccount(<?php echo $enrollment['id']; ?>)"
                    id="createLearnerBtn">
                <i class="bi bi-person-plus-fill"></i> Create Learner Account & Generate LRN
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Learner Account Already Created -->
    <?php if ($enrollment['status'] === 'verified' && $enrollment['learner_account_created']): ?>
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Learner Account Created</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Account Information</h6>
                <p><strong>LRN:</strong> <?php echo htmlspecialchars($enrollment['lrn'] ?? 'N/A'); ?></p>
                <p class="mb-0"><strong>Status:</strong> Learner account has been created and parent has been notified.</p>
            </div>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar-check"></i> Created on: 
                <?php echo $enrollment['verified_at'] ? date('F j, Y g:i A', strtotime($enrollment['verified_at'])) : 'N/A'; ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="text-center mb-4">
        <a href="<?php echo $basePath; ?>/enrollment/review" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Back to Enrollment List
        </a>
    </div>
</div>

<script>
function createLearnerAccount(enrollmentId) {
    const btn = document.getElementById('createLearnerBtn');
    
    if (!confirm('Create learner account and generate LRN?\n\nThis will:\n- Generate a unique LRN\n- Create student record\n- Create learner login account\n- Send credentials to parent\n\nProceed?')) {
        return;
    }
    
    // Disable button
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating account...';
    
    // Make AJAX request
    fetch('<?php echo $basePath; ?>/verification/' + enrollmentId + '/verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Learner Account Created Successfully!\n\n' +
                  'LRN: ' + data.lrn + '\n' +
                  'Learner ID: ' + data.learner_id + '\n\n' +
                  'Parent has been notified via email and in-app notification.');
            
            // Reload page to show updated status
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-plus-fill"></i> Create Learner Account & Generate LRN';
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-person-plus-fill"></i> Create Learner Account & Generate LRN';
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
