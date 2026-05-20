<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — PDSP Form

$pageTitle = 'PDSP Form (Part II) - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Include print stylesheet
echo '<link rel="stylesheet" href="' . $basePath . '/css/print.css" media="print">';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4 no-print">
            <div class="col-md-8">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="bi bi-file-earmark-medical"></i> PDSP Form (Part II)
                </h1>
                <p class="text-muted mt-2">
                    Present Levels of Development and Performance for <strong><?php echo htmlspecialchars($meeting['student_name']); ?></strong>
                </p>
                <div class="mt-2">
                    <span class="badge" style="background-color: #1e4072; font-size: 0.9rem;">
                        <i class="bi bi-calendar"></i> 
                        Meeting: <?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?>
                    </span>
                    <?php
                    $statusBadge = match($pdsp['status']) {
                        'signed' => '<span class="badge" style="background-color: #3b6d11; font-size: 0.9rem;"><i class="bi bi-check-circle"></i> Signed</span>',
                        'draft' => '<span class="badge" style="background-color: #ffc107; color: #000; font-size: 0.9rem;"><i class="bi bi-pencil"></i> Draft</span>',
                        default => '<span class="badge" style="background-color: #6c757d; font-size: 0.9rem;">Not Started</span>'
                    };
                    echo ' ' . $statusBadge;
                    ?>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>

        <!-- Validation Summary (hidden by default) -->
        <div id="validationSummary" class="alert alert-danger no-print" style="display: none; border-left: 4px solid #a01422;">
            <h6><i class="bi bi-exclamation-triangle"></i> Please complete the following before marking as signed:</h6>
            <ul id="validationErrors" class="mb-0"></ul>
        </div>

        <!-- Upload Signed Document Section -->
        <?php if ($pdsp['status'] === 'draft'): ?>
        <div class="card mb-4 no-print" style="border-left: 4px solid #a01422;">
            <div class="card-header" style="background-color: #a01422; color: white;">
                <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up"></i> Upload Signed Handwritten Document</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> After the physical IEP meeting, upload the signed handwritten PDSP document here. This serves as proof of signing.
                </div>
                
                <?php if (!empty($pdsp['signed_document_path'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Signed document uploaded successfully!
                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2">
                        <i class="bi bi-eye"></i> View Document
                    </a>
                </div>
                <?php else: ?>
                <div id="uploadZone" class="text-center p-5" style="border: 3px dashed #a01422; border-radius: 10px; background-color: #f9f9f9; cursor: pointer;">
                    <i class="bi bi-cloud-upload" style="font-size: 4rem; color: #a01422;"></i>
                    <h5 class="mt-3">Drag and drop your signed document here</h5>
                    <p class="text-muted">or click to browse</p>
                    <p class="small text-muted">Accepts: JPG, PNG, PDF (Max 10MB)</p>
                    <input type="file" id="signedDocInput" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                </div>
                
                <div id="uploadProgress" class="mt-3" style="display: none;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status" style="color: #a01422 !important;">
                            <span class="visually-hidden">Uploading...</span>
                        </div>
                        <p class="mt-2">Uploading document...</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Who Signed Section -->
        <?php if ($pdsp['status'] === 'draft'): ?>
        <div class="card mb-4 no-print" style="border-left: 4px solid #1e4072;">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0"><i class="bi bi-people"></i> Who Signed This Document?</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Check all signatories who signed the handwritten document and enter their names.</p>
                
                <div class="row g-3">
                    <?php
                    $signatoryRoles = [
                        'sped_teacher' => 'SPED Teacher',
                        'gen_ed_teacher' => 'General Ed Teacher',
                        'school_head' => 'School Head',
                        'ilrc_supervisor' => 'ILRC Supervisor',
                        'parent_guardian' => 'Parents/Guardian',
                        'medical_allied_1' => 'Medical/Allied Health Professional 1',
                        'medical_allied_2' => 'Medical/Allied Health Professional 2',
                        'medical_allied_3' => 'Medical/Allied Health Professional 3'
                    ];
                    
                    $existingSignatories = !empty($pdsp['signatories']) ? json_decode($pdsp['signatories'], true) : [];
                    $signatoryMap = [];
                    foreach ($existingSignatories as $sig) {
                        $signatoryMap[$sig['role']] = $sig['name'];
                    }
                    
                    foreach ($signatoryRoles as $role => $label):
                        $isChecked = isset($signatoryMap[$role]);
                        $name = $signatoryMap[$role] ?? '';
                    ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input signatory-checkbox" type="checkbox" value="<?php echo $role; ?>" id="sig_<?php echo $role; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="sig_<?php echo $role; ?>">
                                        <?php echo $label; ?>
                                    </label>
                                </div>
                                <input type="text" class="form-control signatory-name" data-role="<?php echo $role; ?>" placeholder="Enter full name" value="<?php echo htmlspecialchars($name); ?>" <?php echo !$isChecked ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- PDSP Form -->
        <form id="pdspForm" method="POST" action="<?php echo $basePath; ?>/iep/meetings/pdsp/save">
            <input type="hidden" name="pdsp_id" value="<?php echo $pdsp['id']; ?>">
            <input type="hidden" name="meeting_id" value="<?php echo $meeting['id']; ?>">

            <div class="card mb-4 no-print" style="border-left: 4px solid #3b6d11;">
                <div class="card-header" style="background-color: #3b6d11; color: white;">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Fill Domain Data</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Fill in all required fields for each domain. All fields are required.</p>
                </div>
            </div>

            <!-- Domain Cards -->
            <?php foreach ($domainNames as $domainName): ?>
            <div class="card mb-4 domain-card page-break-avoid" style="border-left: 4px solid #1e4072;">
                <div class="card-header" style="background-color: #a01422; color: white;">
                    <h5 class="mb-0"><?php echo htmlspecialchars($domainName); ?></h5>
                </div>
                <div class="card-body">
                    <div class="domain-rows" data-domain="<?php echo htmlspecialchars($domainName); ?>">
                        <?php
                        $domainRows = array_filter($domains, fn($d) => $d['domain_name'] === $domainName);
                        if (empty($domainRows)):
                        ?>
                        <!-- Default empty row -->
                        <div class="domain-row mb-3 p-3 page-break-avoid" style="background-color: #f9f9f9; border-radius: 6px;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Sub-Domain <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="domains[<?php echo htmlspecialchars($domainName); ?>][0][sub_domain]" placeholder="Enter sub-domain" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Skills Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="domains[<?php echo htmlspecialchars($domainName); ?>][0][skills_description]" rows="2" placeholder="Describe skills" required></textarea>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Mastered? <span class="text-danger">*</span></label>
                                    <select class="form-select mastered-select" name="domains[<?php echo htmlspecialchars($domainName); ?>][0][mastered]" required>
                                        <option value="">Choose...</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="col-md-10">
                                    <label class="form-label">Educational Recommendation <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="domains[<?php echo htmlspecialchars($domainName); ?>][0][educational_recommendation]" rows="2" placeholder="Enter recommendation" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Q1 Level of Performance <span class="text-danger">*</span></label>
                                    <select class="form-select" name="domains[<?php echo htmlspecialchars($domainName); ?>][0][q1_level]" style="border-color: #1e4072;" required>
                                        <option value="">Select level</option>
                                        <?php foreach ($performanceLevels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Q2 Level of Performance <span class="text-danger">*</span></label>
                                    <select class="form-select" name="domains[<?php echo htmlspecialchars($domainName); ?>][0][q2_level]" style="border-color: #1e4072;" required>
                                        <option value="">Select level</option>
                                        <?php foreach ($performanceLevels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Load existing rows -->
                        <?php foreach ($domainRows as $index => $row): ?>
                        <div class="domain-row mb-3 p-3 page-break-avoid" style="background-color: #f9f9f9; border-radius: 6px;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Sub-Domain <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $index; ?>][sub_domain]" value="<?php echo htmlspecialchars($row['sub_domain'] ?? ''); ?>" placeholder="Enter sub-domain" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Skills Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $index; ?>][skills_description]" rows="2" placeholder="Describe skills" required><?php echo htmlspecialchars($row['skills_description'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Mastered? <span class="text-danger">*</span></label>
                                    <select class="form-select mastered-select" name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $index; ?>][mastered]" required>
                                        <option value="">Choose...</option>
                                        <option value="1" <?php echo ($row['mastered'] ?? null) === 1 || ($row['mastered'] ?? null) === '1' ? 'selected' : ''; ?>>Yes</option>
                                        <option value="0" <?php echo ($row['mastered'] ?? null) === 0 || ($row['mastered'] ?? null) === '0' ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-10">
                                    <label class="form-label">Educational Recommendation <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $index; ?>][educational_recommendation]" rows="2" placeholder="Enter recommendation" required><?php echo htmlspecialchars($row['educational_recommendation'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Q1 Level of Performance <span class="text-danger">*</span></label>
                                    <select class="form-select" name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $index; ?>][q1_level]" style="border-color: #1e4072;" required>
                                        <option value="">Select level</option>
                                        <?php foreach ($performanceLevels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($row['q1_level'] ?? '') === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Q2 Level of Performance <span class="text-danger">*</span></label>
                                    <select class="form-select" name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $index; ?>][q2_level]" style="border-color: #1e4072;" required>
                                        <option value="">Select level</option>
                                        <?php foreach ($performanceLevels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($row['q2_level'] ?? '') === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add Sub-Domain Button -->
                    <button type="button" class="btn btn-sm btn-outline-secondary add-subdomain-btn no-print" data-domain="<?php echo htmlspecialchars($domainName); ?>">
                        <i class="bi bi-plus-circle"></i> Add Sub-Domain
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Action Buttons -->
            <div class="card no-print">
                <div class="card-body text-center">
                    <?php if ($pdsp['status'] === 'draft'): ?>
                    <button type="submit" class="btn btn-secondary btn-lg me-2" style="background-color: #6c757d; border-color: #6c757d;">
                        <i class="bi bi-save"></i> Save Draft
                    </button>
                    <button type="button" id="markAsSignedBtn" class="btn btn-lg" style="background-color: #a01422; border-color: #a01422; color: white;">
                        <i class="bi bi-check-circle"></i> Mark as Signed
                    </button>
                    <?php else: ?>
                    <div class="alert alert-success mb-0" style="background-color: #3b6d11; color: white; border: none;">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle-fill"></i> This PDSP has been marked as signed
                        </h5>
                        <?php if (!empty($pdsp['completed_at'])): ?>
                        <p class="mb-0 mt-2">
                            <small>Completed on <?php echo date('F d, Y g:i A', strtotime($pdsp['completed_at'])); ?></small>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>" class="btn btn-outline-secondary btn-lg <?php echo $pdsp['status'] === 'draft' ? '' : 'mt-3'; ?>">
                        <i class="bi bi-arrow-left"></i> Back to Meeting
                    </a>
                </div>
            </div>
        </form>

        <!-- Signed Status (if already signed) -->
        <?php if ($pdsp['status'] === 'signed' && !empty($pdsp['signatories'])): ?>
        <div class="card mt-4 signature-section" style="border-left: 4px solid #3b6d11;">
            <div class="card-header" style="background-color: #3b6d11; color: white;">
                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Signatories</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $signatories = json_decode($pdsp['signatories'], true);
                    foreach ($signatories as $sig):
                        $roleLabel = $signatoryRoles[$sig['role']] ?? $sig['role'];
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">
                                <strong><?php echo htmlspecialchars($roleLabel); ?>:</strong><br>
                                <?php echo htmlspecialchars($sig['name']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($pdsp['signed_document_path'])): ?>
                <div class="mt-3 no-print">
                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>" target="_blank" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-pdf"></i> View Signed Document
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- AI Upload Modal -->
<div class="modal fade" id="aiUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title"><i class="bi bi-cloud-upload"></i> Upload Handwritten Form (Optional AI Auto-Fill)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Upload a photo or scanned PDF of your handwritten Part II form. Our AI will attempt to auto-fill the form fields. You can review and correct all fields before saving.
                </div>
                
                <div id="aiUploadZone" class="text-center p-5" style="border: 3px dashed #1e4072; border-radius: 10px; background-color: #f9f9f9; cursor: pointer;">
                    <i class="bi bi-cloud-upload" style="font-size: 4rem; color: #1e4072;"></i>
                    <h5 class="mt-3">Drag and drop your file here</h5>
                    <p class="text-muted">or click to browse</p>
                    <p class="small text-muted">Accepts: JPG, PNG, PDF (Max 10MB)</p>
                    <input type="file" id="aiFileInput" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                </div>
                
                <div id="aiUploadProgress" class="mt-3" style="display: none;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status" style="color: #1e4072 !important;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Reading your document...</p>
                    </div>
                </div>
                
                <div id="aiUploadError" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #a01422; color: white;">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Confirm Signing</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>All fields are complete. Mark Part II as signed and notify Guidance and Principal?</p>
                <div id="signatoryList" class="mt-3">
                    <h6>Signatories:</h6>
                    <ul id="signatoryListItems"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSignBtn" class="btn btn-primary" style="background-color: #a01422; border-color: #a01422;">
                    <i class="bi bi-check-circle"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global functions (accessible from onclick handlers)
const basePath = '<?php echo $basePath; ?>';
const pdspId = '<?php echo $pdsp['id']; ?>';

// OCR Auto-Fill functionality (must be global for onclick)
function triggerOCRExtraction() {
    // Show loading
    Swal.fire({
        title: 'OCR Extraction in Progress',
        text: 'Reading your document...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = new FormData();
    formData.append('pdsp_id', pdspId);
    
    fetch(basePath + '/iep/meetings/pdsp/ai-extract', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Pre-fill form with extracted data
            if (data.domains && Array.isArray(data.domains)) {
                fillFormWithOCRData(data.domains);
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Form Auto-Filled!',
                html: data.message + '<br><br><strong>Important:</strong> ' + (data.note || 'Please review all fields carefully.'),
                confirmButtonColor: '#3b6d11'
            });
        } else {
            let errorMessage = data.message || 'OCR extraction failed. Please fill manually.';
            if (data.install_required) {
                errorMessage += '<br><br><small>Tesseract OCR is not installed on this server.</small>';
            }
            
            Swal.fire({
                icon: 'info',
                title: 'OCR Unavailable',
                html: errorMessage,
                confirmButtonColor: '#1e4072'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'OCR extraction unavailable. Please fill the form manually.',
            confirmButtonColor: '#a01422'
        });
        console.error('OCR extraction error:', error);
    });
}

// Fill form with OCR extracted data
function fillFormWithOCRData(domains) {
    domains.forEach(domainData => {
        const domainName = domainData.domain_name;
        const container = document.querySelector(`.domain-rows[data-domain="${domainName}"]`);
        
        if (!container) return;
        
        // Clear existing rows
        container.innerHTML = '';
        
        // Add the extracted row
        const index = 0;
        const newRow = document.createElement('div');
        newRow.className = 'domain-row mb-3 p-3 page-break-avoid';
        newRow.style.cssText = 'background-color: #f9f9f9; border-radius: 6px;';
        newRow.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Sub-Domain <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="domains[${domainName}][${index}][sub_domain]" 
                           value="${escapeHtml(domainData.sub_domain || '')}" placeholder="Enter sub-domain" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Skills Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="domains[${domainName}][${index}][skills_description]" 
                              rows="2" placeholder="Describe skills" required>${escapeHtml(domainData.skills_description || '')}</textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mastered? <span class="text-danger">*</span></label>
                    <select class="form-select mastered-select" name="domains[${domainName}][${index}][mastered]" required>
                        <option value="">Choose...</option>
                        <option value="1" ${domainData.mastered ? 'selected' : ''}>Yes</option>
                        <option value="0" ${!domainData.mastered ? 'selected' : ''}>No</option>
                    </select>
                </div>
                <div class="col-md-10">
                    <label class="form-label">Educational Recommendation <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="domains[${domainName}][${index}][educational_recommendation]" 
                              rows="2" placeholder="Enter recommendation" required>${escapeHtml(domainData.educational_recommendation || '')}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Q1 Level of Performance <span class="text-danger">*</span></label>
                    <select class="form-select" name="domains[${domainName}][${index}][q1_level]" style="border-color: #1e4072;" required>
                        <option value="">Select level</option>
                        <option value="beginning" ${domainData.q1_level === 'beginning' ? 'selected' : ''}>Beginning (74% and below)</option>
                        <option value="developing" ${domainData.q1_level === 'developing' ? 'selected' : ''}>Developing (75-79%)</option>
                        <option value="approaching" ${domainData.q1_level === 'approaching' ? 'selected' : ''}>Approaching Proficiency (80-84%)</option>
                        <option value="proficient" ${domainData.q1_level === 'proficient' ? 'selected' : ''}>Proficient (85-89%)</option>
                        <option value="advanced" ${domainData.q1_level === 'advanced' ? 'selected' : ''}>Advanced (90% and above)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Q2 Level of Performance <span class="text-danger">*</span></label>
                    <select class="form-select" name="domains[${domainName}][${index}][q2_level]" style="border-color: #1e4072;" required>
                        <option value="">Select level</option>
                        <option value="beginning" ${domainData.q2_level === 'beginning' ? 'selected' : ''}>Beginning (74% and below)</option>
                        <option value="developing" ${domainData.q2_level === 'developing' ? 'selected' : ''}>Developing (75-79%)</option>
                        <option value="approaching" ${domainData.q2_level === 'approaching' ? 'selected' : ''}>Approaching Proficiency (80-84%)</option>
                        <option value="proficient" ${domainData.q2_level === 'proficient' ? 'selected' : ''}>Proficient (85-89%)</option>
                        <option value="advanced" ${domainData.q2_level === 'advanced' ? 'selected' : ''}>Advanced (90% and above)</option>
                    </select>
                </div>
            </div>
        `;
        
        container.appendChild(newRow);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// DOM Ready event
document.addEventListener('DOMContentLoaded', function() {
    const pdspStatus = '<?php echo $pdsp['status']; ?>';
    let rowCounters = {};
    
    // Initialize row counters for each domain
    document.querySelectorAll('.domain-rows').forEach(container => {
        const domain = container.dataset.domain;
        rowCounters[domain] = container.querySelectorAll('.domain-row').length;
    });
    
    // Add Sub-Domain functionality
    document.querySelectorAll('.add-subdomain-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const domain = this.dataset.domain;
            const container = document.querySelector(`.domain-rows[data-domain="${domain}"]`);
            const index = rowCounters[domain]++;
            
            const newRow = document.createElement('div');
            newRow.className = 'domain-row mb-3 p-3';
            newRow.style.cssText = 'background-color: #f9f9f9; border-radius: 6px;';
            newRow.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sub-Domain <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="domains[${domain}][${index}][sub_domain]" placeholder="Enter sub-domain" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Skills Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="domains[${domain}][${index}][skills_description]" rows="2" placeholder="Describe skills" required></textarea>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mastered? <span class="text-danger">*</span></label>
                        <select class="form-select mastered-select" name="domains[${domain}][${index}][mastered]" required>
                            <option value="">Choose...</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="col-md-10">
                        <label class="form-label">Educational Recommendation <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="domains[${domain}][${index}][educational_recommendation]" rows="2" placeholder="Enter recommendation" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Q1 Level of Performance <span class="text-danger">*</span></label>
                        <select class="form-select" name="domains[${domain}][${index}][q1_level]" style="border-color: #1e4072;" required>
                            <option value="">Select level</option>
                            <option value="beginning">Beginning (74% and below)</option>
                            <option value="developing">Developing (75-79%)</option>
                            <option value="approaching">Approaching Proficiency (80-84%)</option>
                            <option value="proficient">Proficient (85-89%)</option>
                            <option value="advanced">Advanced (90% and above)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Q2 Level of Performance <span class="text-danger">*</span></label>
                        <select class="form-select" name="domains[${domain}][${index}][q2_level]" style="border-color: #1e4072;" required>
                            <option value="">Select level</option>
                            <option value="beginning">Beginning (74% and below)</option>
                            <option value="developing">Developing (75-79%)</option>
                            <option value="approaching">Approaching Proficiency (80-84%)</option>
                            <option value="proficient">Proficient (85-89%)</option>
                            <option value="advanced">Advanced (90% and above)</option>
                        </select>
                    </div>
                </div>
            `;
            
            container.appendChild(newRow);
        });
    });
    
    // Signatory checkbox toggle
    document.querySelectorAll('.signatory-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const role = this.value;
            const nameInput = document.querySelector(`.signatory-name[data-role="${role}"]`);
            nameInput.disabled = !this.checked;
            if (!this.checked) {
                nameInput.value = '';
            }
        });
    });
    
    // Upload Signed Document
    if (pdspStatus === 'draft') {
        const uploadZone = document.getElementById('uploadZone');
        const signedDocInput = document.getElementById('signedDocInput');
        const uploadProgress = document.getElementById('uploadProgress');
        
        if (uploadZone) {
            uploadZone.addEventListener('click', () => signedDocInput.click());
            
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '#3b6d11';
            });
            
            uploadZone.addEventListener('dragleave', () => {
                uploadZone.style.borderColor = '#a01422';
            });
            
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '#a01422';
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleSignedDocUpload(files[0]);
                }
            });
            
            signedDocInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleSignedDocUpload(e.target.files[0]);
                }
            });
        }
        
        function handleSignedDocUpload(file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Only JPG, PNG, and PDF files are accepted.',
                    confirmButtonColor: '#a01422'
                });
                return;
            }
            
            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'Maximum file size is 10MB.',
                    confirmButtonColor: '#a01422'
                });
                return;
            }
            
            uploadProgress.style.display = 'block';
            uploadZone.style.display = 'none';
            
            // Create FormData
            const formData = new FormData();
            formData.append('signed_document', file);
            formData.append('pdsp_id', pdspId);
            
            // Send to server
            fetch(basePath + '/iep/meetings/pdsp/upload-signed-document', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Document Uploaded!',
                        text: data.message,
                        confirmButtonColor: '#3b6d11'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: error.message,
                    confirmButtonColor: '#a01422'
                });
                uploadProgress.style.display = 'none';
                uploadZone.style.display = 'block';
            });
        }
    }
    
    // Note: triggerOCRExtraction() and fillFormWithOCRData() are defined globally above
    // so they can be accessed from onclick handlers
    
    // AI Upload functionality (legacy - can be removed if not needed)
    const aiUploadZone = document.getElementById('aiUploadZone');
    const aiFileInput = document.getElementById('aiFileInput');
    const aiUploadProgress = document.getElementById('aiUploadProgress');
    const aiUploadError = document.getElementById('aiUploadError');
    
    if (aiUploadZone) {
        aiUploadZone.addEventListener('click', () => aiFileInput.click());
        
        aiUploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            aiUploadZone.style.borderColor = '#3b6d11';
        });
        
        aiUploadZone.addEventListener('dragleave', () => {
            aiUploadZone.style.borderColor = '#1e4072';
        });
        
        aiUploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            aiUploadZone.style.borderColor = '#1e4072';
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleAIUpload(files[0]);
            }
        });
        
        aiFileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleAIUpload(e.target.files[0]);
            }
        });
    }
    
    function handleAIUpload(file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            aiUploadError.textContent = 'Only JPG, PNG, and PDF files are accepted.';
            aiUploadError.style.display = 'block';
            return;
        }
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            aiUploadError.textContent = 'File too large. Maximum size is 10MB.';
            aiUploadError.style.display = 'block';
            return;
        }
        
        aiUploadError.style.display = 'none';
        aiUploadProgress.style.display = 'block';
        aiUploadZone.style.display = 'none';
        
        // Create FormData
        const formData = new FormData();
        formData.append('file', file);
        formData.append('pdsp_id', pdspId);
        
        // Send to server for AI extraction
        fetch(basePath + '/iep/meetings/pdsp/ai-extract', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Pre-fill form with extracted data
                if (data.domains && Array.isArray(data.domains)) {
                    // TODO: Implement pre-fill logic
                    console.log('Extracted data:', data.domains);
                }
                
                // Close modal and show success
                bootstrap.Modal.getInstance(document.getElementById('aiUploadModal')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Form Auto-Filled!',
                    text: 'Please review and correct all fields before saving.',
                    confirmButtonColor: '#3b6d11'
                });
            } else {
                throw new Error(data.message || 'AI extraction failed');
            }
        })
        .catch(error => {
            aiUploadError.textContent = 'Auto-fill unavailable. Please fill the form manually.';
            aiUploadError.style.display = 'block';
            console.error('AI extraction error:', error);
        })
        .finally(() => {
            aiUploadProgress.style.display = 'none';
            aiUploadZone.style.display = 'block';
        });
    }
    
    // Mark as Signed Button
    const markAsSignedBtn = document.getElementById('markAsSignedBtn');
    if (markAsSignedBtn) {
        markAsSignedBtn.addEventListener('click', function() {
            // Validate form
            const validation = validateForm();
            
            if (!validation.valid) {
                // Show validation errors
                showValidationErrors(validation.errors);
                return;
            }
            
            // Show confirmation modal
            showConfirmationModal(validation.signatories);
        });
    }
    
    function validateForm() {
        const errors = [];
        const signatories = [];
        
        // Check if signed document is uploaded
        const hasSignedDoc = <?php echo !empty($pdsp['signed_document_path']) ? 'true' : 'false'; ?>;
        if (!hasSignedDoc) {
            errors.push('Signed handwritten document must be uploaded');
        }
        
        // Check signatories
        document.querySelectorAll('.signatory-checkbox:checked').forEach(checkbox => {
            const role = checkbox.value;
            const nameInput = document.querySelector(`.signatory-name[data-role="${role}"]`);
            const name = nameInput.value.trim();
            
            if (!name) {
                const label = checkbox.nextElementSibling.textContent.trim();
                errors.push(`Signatory name required for: ${label}`);
            } else {
                signatories.push({ role: role, name: name });
            }
        });
        
        if (signatories.length === 0) {
            errors.push('At least one signatory must be selected');
        }
        
        // Check all domains
        const domainNames = [
            'Perceptuo-Cognitive',
            'Psychosocial',
            'Socio-Emotional',
            'Psychomotor',
            'Daily Living Skills',
            'Communication and Language'
        ];
        
        const domains = {};
        
        domainNames.forEach(domainName => {
            const container = document.querySelector(`.domain-rows[data-domain="${domainName}"]`);
            const rows = container.querySelectorAll('.domain-row');
            
            if (rows.length === 0) {
                errors.push(`Domain "${domainName}" has no entries`);
                return;
            }
            
            domains[domainName] = [];
            
            rows.forEach((row, index) => {
                const rowNum = index + 1;
                const rowData = {};
                
                // Sub-Domain
                const subDomain = row.querySelector('[name*="[sub_domain]"]');
                if (!subDomain || !subDomain.value.trim()) {
                    errors.push(`${domainName} - Row ${rowNum}: Sub-Domain is required`);
                    if (subDomain) subDomain.style.borderColor = '#a01422';
                } else {
                    rowData.sub_domain = subDomain.value.trim();
                    if (subDomain) subDomain.style.borderColor = '';
                }
                
                // Skills Description
                const skillsDesc = row.querySelector('[name*="[skills_description]"]');
                if (!skillsDesc || !skillsDesc.value.trim()) {
                    errors.push(`${domainName} - Row ${rowNum}: Skills Description is required`);
                    if (skillsDesc) skillsDesc.style.borderColor = '#a01422';
                } else {
                    rowData.skills_description = skillsDesc.value.trim();
                    if (skillsDesc) skillsDesc.style.borderColor = '';
                }
                
                // Mastered
                const mastered = row.querySelector('[name*="[mastered]"]');
                if (!mastered || mastered.value === '') {
                    errors.push(`${domainName} - Row ${rowNum}: Mastered status must be selected`);
                    if (mastered) mastered.style.borderColor = '#a01422';
                } else {
                    rowData.mastered = mastered.value;
                    if (mastered) mastered.style.borderColor = '';
                }
                
                // Educational Recommendation
                const eduRec = row.querySelector('[name*="[educational_recommendation]"]');
                if (!eduRec || !eduRec.value.trim()) {
                    errors.push(`${domainName} - Row ${rowNum}: Educational Recommendation is required`);
                    if (eduRec) eduRec.style.borderColor = '#a01422';
                } else {
                    rowData.educational_recommendation = eduRec.value.trim();
                    if (eduRec) eduRec.style.borderColor = '';
                }
                
                // Q1 Level
                const q1 = row.querySelector('[name*="[q1_level]"]');
                if (!q1 || !q1.value) {
                    errors.push(`${domainName} - Row ${rowNum}: Q1 Level is required`);
                    if (q1) q1.style.borderColor = '#a01422';
                } else {
                    rowData.q1_level = q1.value;
                    if (q1) q1.style.borderColor = '';
                }
                
                // Q2 Level
                const q2 = row.querySelector('[name*="[q2_level]"]');
                if (!q2 || !q2.value) {
                    errors.push(`${domainName} - Row ${rowNum}: Q2 Level is required`);
                    if (q2) q2.style.borderColor = '#a01422';
                } else {
                    rowData.q2_level = q2.value;
                    if (q2) q2.style.borderColor = '';
                }
                
                domains[domainName].push(rowData);
            });
        });
        
        return {
            valid: errors.length === 0,
            errors: errors,
            signatories: signatories,
            domains: domains
        };
    }
    
    function showValidationErrors(errors) {
        const summary = document.getElementById('validationSummary');
        const errorList = document.getElementById('validationErrors');
        
        errorList.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        
        summary.style.display = 'block';
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Show alert
        Swal.fire({
            icon: 'error',
            title: 'Validation Failed',
            text: 'Please complete all required fields before marking as signed.',
            confirmButtonColor: '#a01422'
        });
    }
    
    function showConfirmationModal(signatories) {
        const listItems = document.getElementById('signatoryListItems');
        listItems.innerHTML = '';
        
        signatories.forEach(sig => {
            const li = document.createElement('li');
            li.textContent = sig.name;
            listItems.appendChild(li);
        });
        
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        modal.show();
    }
    
    // Confirm Sign Button
    const confirmSignBtn = document.getElementById('confirmSignBtn');
    if (confirmSignBtn) {
        confirmSignBtn.addEventListener('click', function() {
            const validation = validateForm();
            
            if (!validation.valid) {
                return;
            }
            
            // Show loading
            confirmSignBtn.disabled = true;
            confirmSignBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            // Send to server
            fetch(basePath + '/iep/meetings/pdsp/mark-as-signed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pdsp_id: pdspId,
                    signatories: validation.signatories,
                    domains: validation.domains
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('confirmationModal')).hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'PDSP Marked as Signed!',
                        text: 'Notifications have been sent to Guidance and Principal. Process 5 is now unlocked.',
                        confirmButtonColor: '#3b6d11'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to mark as signed');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#a01422'
                });
                confirmSignBtn.disabled = false;
                confirmSignBtn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm';
            });
        });
    }
});
</script>

<style>
.form-check-input:checked {
    background-color: #a01422;
    border-color: #a01422;
}

.form-select:focus,
.form-control:focus {
    border-color: #a01422;
    box-shadow: 0 0 0 0.25rem rgba(160, 20, 34, 0.25);
}

.mastered-select {
    font-weight: 600;
}

.mastered-select option[value="1"] {
    color: #3b6d11;
}

.mastered-select option[value="0"] {
    color: #a01422;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
