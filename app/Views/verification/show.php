<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-05-04
// Part of: SPED LMS — Enrollment Detail View

$basePath = isset($basePath) ? $basePath : '/';
$pageTitle = 'Enrollment Detail - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    @media print {
        .sidebar, .topbar, .btn, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 20px !important; }
    }
    
    .section-header {
        background: linear-gradient(135deg, #1e4072 0%, #2a5a9e 100%);
        color: white;
        padding: 12px 20px;
        margin: 20px 0 15px 0;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 15px;
    }
    
    .field-row.full {
        grid-template-columns: 1fr;
    }
    
    .field-label {
        font-weight: 600;
        color: #1e4072;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .field-value {
        padding: 10px;
        background: #f8f9fa;
        border-left: 3px solid #a01422;
        border-radius: 4px;
        min-height: 40px;
    }
    
    .doc-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        background: white;
        transition: all 0.3s ease;
    }
    
    .doc-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .doc-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .doc-status.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .doc-status.approved {
        background: #d4edda;
        color: #155724;
    }
    
    .doc-status.rejected {
        background: #f8d7da;
        color: #721c24;
    }
    
    .doc-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .btn-approve {
        background: #3b6d11;
        color: white;
        border: none;
        flex: 1;
    }
    
    .btn-approve:hover {
        background: #2d5409;
        color: white;
    }
    
    .btn-reject {
        background: #dc3545;
        color: white;
        border: none;
        flex: 1;
    }
    
    .btn-reject:hover {
        background: #c82333;
        color: white;
    }
    
    .btn-verify {
        width: 100%;
        padding: 15px;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .signature-section {
        background: white;
        border: 2px solid #1e4072;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .signature-box {
        border: 2px solid #1e4072;
        background: white;
        padding: 15px;
        border-radius: 6px;
        text-align: center;
        max-width: 400px;
        margin: 0 auto;
    }
    
    .signature-box img {
        max-width: 100%;
        height: auto;
        max-height: 150px;
    }
    
    .document-preview {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        background: #f8f9fa;
        text-align: center;
        margin-bottom: 15px;
    }
    
    .document-preview img {
        max-width: 100%;
        max-height: 250px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .document-preview img:hover {
        opacity: 0.9;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
    
    .pdf-icon {
        font-size: 4rem;
        color: #dc3545;
    }
</style>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Print Button -->
    <div class="mb-3 no-print">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3" style="color: #1e4072;">Enrollment Detail</h1>
            <p class="text-muted">
                Student: <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
            </p>
        </div>
        <div>
            <span class="badge bg-info">Enrollment ID: <?php echo $enrollment['id']; ?></span>
        </div>
    </div>
    
    <!-- Verification Alert -->
    <?php if ($allApproved): ?>
        <div class="alert alert-success" style="background-color: #d4edda; border-color: #3b6d11; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <strong>✓ All documents approved!</strong> Click "Verify Enrollment" below to create learner account and generate LRN.
        </div>
    <?php endif; ?>
                
                <!-- SECTION 1: LEARNER INFORMATION -->
                <div class="section-header">Section 1: Learner Information</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['last_name']); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['first_name']); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Extension Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['extension_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Date of Birth</div>
                        <div class="field-value"><?php echo date('M d, Y', strtotime($enrollment['birth_date'])); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Age</div>
                        <div class="field-value"><?php echo $enrollment['age'] ?? 'N/A'; ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Sex</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['sex']); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Place of Birth</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['birth_place'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Mother Tongue</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_tongue'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Indigenous People</div>
                        <div class="field-value"><?php echo $enrollment['is_indigenous_people'] ? 'Yes - ' . htmlspecialchars($enrollment['indigenous_group']) : 'No'; ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">4Ps Beneficiary</div>
                        <div class="field-value"><?php echo $enrollment['is_4ps_beneficiary'] ? 'Yes - ' . htmlspecialchars($enrollment['fourps_household_id']) : 'No'; ?></div>
                    </div>
                </div>
                
                <!-- Disabilities -->
                <div class="field-row full">
                    <div>
                        <div class="field-label">Disabilities</div>
                        <div class="field-value">
                            <?php
                            $disabilities = [];
                            if ($enrollment['disability_visual']) $disabilities[] = 'Visual';
                            if ($enrollment['disability_hearing']) $disabilities[] = 'Hearing';
                            if ($enrollment['disability_learning']) $disabilities[] = 'Learning';
                            if ($enrollment['disability_speech']) $disabilities[] = 'Speech';
                            if ($enrollment['disability_intellectual']) $disabilities[] = 'Intellectual';
                            if ($enrollment['disability_physical']) $disabilities[] = 'Physical';
                            if ($enrollment['disability_emotional']) $disabilities[] = 'Emotional';
                            if ($enrollment['disability_chronic_illness']) $disabilities[] = 'Chronic Illness';
                            if ($enrollment['disability_others']) $disabilities[] = 'Others: ' . htmlspecialchars($enrollment['disability_others_specify']);
                            echo !empty($disabilities) ? implode(', ', $disabilities) : 'None';
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 2: CURRENT ADDRESS -->
                <div class="section-header">Section 2: Current Address</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">House No./Street</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_house_no'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Barangay</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_barangay'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">City/Municipality</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_city'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Province</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_province'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Zip Code</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_zip_code'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 3: PERMANENT ADDRESS -->
                <div class="section-header">Section 3: Permanent Address</div>
                <?php if ($enrollment['same_as_current_address']): ?>
                    <div class="alert alert-info">Same as Current Address</div>
                <?php else: ?>
                    <div class="field-row">
                        <div>
                            <div class="field-label">House No./Street</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_house_no'] ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <div class="field-label">Barangay</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_barangay'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="field-row">
                        <div>
                            <div class="field-label">City/Municipality</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_city'] ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <div class="field-label">Province</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_province'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="field-row">
                        <div>
                            <div class="field-label">Zip Code</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_zip_code'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- SECTION 4: PARENT/GUARDIAN INFORMATION -->
                <div class="section-header">Section 4: Parent/Guardian Information</div>
                <div class="field-row full">
                    <div>
                        <div class="field-label"><strong>Father's Information</strong></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_last_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_first_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_contact_number'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <div class="field-row full">
                    <div>
                        <div class="field-label"><strong>Mother's Information</strong></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Maiden Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_maiden_last_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_first_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_contact_number'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <div class="field-row full">
                    <div>
                        <div class="field-label"><strong>Guardian's Information (if applicable)</strong></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_last_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_first_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_contact_number'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 5: PREVIOUS SCHOOL INFORMATION -->
                <div class="section-header">Section 5: Previous School Information</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">School Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">School Type</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_type'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Grade Level</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_grade_level'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">School Year</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_year'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row full">
                    <div>
                        <div class="field-label">School Address</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_address'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 6: ENROLLMENT DETAILS -->
                <div class="section-header">Section 6: Enrollment Details</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Grade Level to Enroll</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Balik Aral</div>
                        <div class="field-value"><?php echo $enrollment['is_balik_aral'] ? 'Yes' : 'No'; ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">PEPT Passer</div>
                        <div class="field-value"><?php echo $enrollment['is_pept_passer'] ? 'Yes - Rating: ' . htmlspecialchars($enrollment['pept_rating']) : 'No'; ?></div>
                    </div>
                    <div>
                        <div class="field-label">ALS Passer</div>
                        <div class="field-value"><?php echo $enrollment['is_als_passer'] ? 'Yes - Rating: ' . htmlspecialchars($enrollment['als_rating']) : 'No'; ?></div>
                    </div>
                </div>
                
                <!-- SECTION 7: LEARNING MODALITY -->
                <div class="section-header">Section 7: Learning Modality</div>
                <div class="field-row full">
                    <div>
                        <div class="field-label">Selected Modalities</div>
                        <div class="field-value">
                            <?php
                            $modalities = [];
                            if ($enrollment['modality_modular_print']) $modalities[] = 'Modular (Print)';
                            if ($enrollment['modality_modular_digital']) $modalities[] = 'Modular (Digital)';
                            if ($enrollment['modality_online']) $modalities[] = 'Online';
                            if ($enrollment['modality_educational_tv']) $modalities[] = 'Educational TV';
                            if ($enrollment['modality_radio']) $modalities[] = 'Radio';
                            if ($enrollment['modality_blended']) $modalities[] = 'Blended';
                            if ($enrollment['modality_face_to_face']) $modalities[] = 'Face-to-Face';
                            echo !empty($modalities) ? implode(', ', $modalities) : 'None';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Preferred Distance Learning Modality</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['preferred_distance_modality'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 8: PARENT/GUARDIAN SIGNATURE -->
                <div class="section-header">Section 8: Parent/Guardian Digital Signature</div>
                <div class="signature-section">
                    <?php if ($enrollment['signature_data']): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="signature-box">
                                    <img src="<?php echo htmlspecialchars($enrollment['signature_data']); ?>" 
                                         alt="Parent Signature">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><i class="bi bi-check-circle"></i> Digitally Signed</h6>
                                    <p class="mb-1"><strong>Signed by:</strong> <?php echo htmlspecialchars($enrollment['parent_name'] ?? 'Parent/Guardian'); ?></p>
                                    <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($enrollment['date_signed'])); ?></p>
                                    <p class="mb-0"><small><i class="bi bi-shield-check"></i> This digital signature is legally binding.</small></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> No signature provided.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- DOCUMENTS SECTION -->
                <div class="section-header">Uploaded Documents for Review</div>
                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                        <div class="col-md-6">
                            <div class="doc-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <?php
                                        $docLabels = [
                                            'psa_birth_cert' => 'PSA Birth Certificate',
                                            'pwd_id' => 'PWD ID',
                                            'medical_record' => 'Medical Record',
                                            'beef_form' => 'BEEF Form'
                                        ];
                                        echo $docLabels[$doc['document_type']] ?? $doc['document_type'];
                                        ?>
                                    </h6>
                                    <span class="doc-status <?php echo $doc['status']; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </div>
                                
                                <!-- Document Preview -->
                                <div class="document-preview">
                                    <?php 
                                    $fileExt = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                    // Use direct file path (no encryption)
                                    $fileUrl = $basePath . '/' . $doc['file_path'];
                                    ?>
                                    
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo $fileUrl; ?>" 
                                             alt="<?php echo $docLabels[$doc['document_type']]; ?>"
                                             onclick="window.open('<?php echo $fileUrl; ?>', '_blank')">
                                        <small class="text-muted d-block mt-2">Click to view full size</small>
                                    <?php else: ?>
                                        <i class="bi bi-file-earmark-pdf pdf-icon"></i>
                                        <p class="mb-0 mt-2"><strong>PDF Document</strong></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- View/Download Button -->
                                <div class="mb-2">
                                    <a href="<?php echo $fileUrl; ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-eye"></i> View Full Document
                                    </a>
                                </div>
                                
                                <!-- Review Notes -->
                                <?php if ($doc['review_note']): ?>
                                    <div class="alert alert-warning small mb-2">
                                        <strong><i class="bi bi-chat-left-text"></i> Review Note:</strong><br>
                                        <?php echo htmlspecialchars($doc['review_note']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Reviewer Info -->
                                <?php if ($doc['status'] !== 'pending' && $doc['reviewed_at']): ?>
                                    <div class="alert alert-info small mb-2">
                                        <strong><i class="bi bi-person-check"></i> Reviewed by:</strong> 
                                        <?php echo htmlspecialchars($doc['reviewer_name'] ?? 'Unknown'); ?><br>
                                        <strong><i class="bi bi-calendar-check"></i> Date:</strong> 
                                        <?php echo date('M j, Y g:i A', strtotime($doc['reviewed_at'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Document status only - no per-document buttons -->
                                <?php if ($doc['status'] === 'pending'): ?>
                                    <div class="alert alert-info small mb-0">
                                        <i class="bi bi-clock"></i> Pending review - use buttons at bottom to approve/reject entire enrollment
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Simplified Approval Buttons (Only if enrollment is pending) -->
                <?php if ($enrollment['status'] === 'pending'): ?>
                    <div class="card mt-4 border-success">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-clipboard-check"></i> Review Decision</h5>
                            <p class="text-muted">After reviewing all documents and information above, make your decision:</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <form method="POST" action="<?php echo $basePath; ?>enrollment/approve/<?php echo $enrollment['id']; ?>" 
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
                <?php endif; ?>
                
                <!-- Verify Enrollment Button (Only if all approved) -->
                <?php if ($allApproved): ?>
                    <div class="mt-4">
                        <button class="btn btn-lg btn-success btn-verify" onclick="verifyEnrollment(<?php echo $enrollment['id']; ?>)" style="background-color: #3b6d11; border-color: #3b6d11;">
                            ✓ Verify Enrollment & Create Learner Account
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- Rejection Modal for Per-Document (Legacy - Not Used) -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reason for Rejection</label>
                <textarea class="form-control" id="rejectionReason" rows="4" placeholder="Explain why this document is being rejected..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitRejection()">Reject Document</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Enrollment Modal (NEW - Simplified) -->
<div class="modal fade" id="rejectEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Reject Enrollment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo $basePath; ?>enrollment/reject/<?php echo $enrollment['id']; ?>">
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

<script>
    let currentDocumentId = null;
    const basePath = '<?php echo $basePath; ?>';
    
    function approveDocument(docId) {
        if (confirm('Approve this document?')) {
            fetch(basePath + 'enrollment/document/approve/' + docId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Document approved!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e.message));
        }
    }
    
    function rejectDocument(docId) {
        currentDocumentId = docId;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
    
    function submitRejection() {
        const reason = document.getElementById('rejectionReason').value;
        if (!reason.trim()) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        fetch(basePath + 'enrollment/document/reject/' + currentDocumentId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Document rejected!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(e => alert('Error: ' + e.message));
    }
    
    function verifyEnrollment(enrollmentId) {
        if (confirm('Verify this enrollment? This will create a learner account and generate an LRN.')) {
            fetch(basePath + 'verification/' + enrollmentId + '/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Enrollment verified!\nLRN: ' + data.lrn + '\nLearner account created successfully.');
                    location.href = basePath + 'verification';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e.message));
        }
    }
</script>
