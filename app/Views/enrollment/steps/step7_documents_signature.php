<!-- Step 7: Documents & Signature -->
<div class="form-step" id="step-7">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-file-earmark-arrow-up"></i> Step 7: Documents & Signature
            </h4>
            
            <?php if ($enrollmentType === 'returning'): ?>
            <!-- Returning Student - Documents Auto-Copied -->
            <div class="alert alert-success">
                <h5 class="mb-2"><strong><i class="bi bi-check-circle-fill"></i> Returning Student - Documents Auto-Copied</strong></h5>
                <p class="mb-0">
                    Your documents from last year's enrollment have been automatically copied to this enrollment. 
                    <strong>No need to upload documents again!</strong> Only your signature is required to confirm this enrollment.
                </p>
            </div>
            <?php else: ?>
            <!-- New/Transfer Student - Documents Required -->
            <div class="alert alert-info">
                <strong><i class="bi bi-info-circle"></i> Required Documents</strong>
                <p class="mb-0">Only PSA Birth Certificate is required. Other documents are optional but recommended.</p>
            </div>

            <!-- Document Uploads -->
            <div class="row">
                <!-- PSA Birth Certificate -->
                <div class="col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <label class="form-label">
                                <i class="bi bi-file-earmark-text text-danger"></i> 
                                <strong>PSA Birth Certificate *</strong>
                            </label>
                            <?php 
                            $fieldName = 'psa_birth_cert';
                            $acceptedTypes = '.pdf,.jpg,.jpeg,.png';
                            $maxSize = 10;
                            $showCamera = true;
                            include __DIR__ . '/../../components/upload-zone.php';
                            ?>
                            <div class="form-text">Philippine Statistics Authority certified birth certificate (Required)</div>
                        </div>
                    </div>
                </div>

                <!-- PWD ID -->
                <div class="col-md-6 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <label class="form-label">
                                <i class="bi bi-card-heading text-secondary"></i> 
                                <strong>PWD ID</strong> <small class="text-muted">(Optional)</small>
                            </label>
                            <?php 
                            $fieldName = 'pwd_id';
                            $acceptedTypes = '.pdf,.jpg,.jpeg,.png';
                            $maxSize = 10;
                            $showCamera = true;
                            include __DIR__ . '/../../components/upload-zone.php';
                            ?>
                            <div class="form-text">Person with Disability Identification Card (if available)</div>
                        </div>
                    </div>
                </div>

                <!-- Medical Record -->
                <div class="col-md-6 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <label class="form-label">
                                <i class="bi bi-hospital text-secondary"></i> 
                                <strong>Medical Record</strong> <small class="text-muted">(Optional)</small>
                            </label>
                            <?php 
                            $fieldName = 'medical_record';
                            $acceptedTypes = '.pdf,.jpg,.jpeg,.png';
                            $maxSize = 10;
                            $showCamera = true;
                            include __DIR__ . '/../../components/upload-zone.php';
                            ?>
                            <div class="form-text">Medical certificate or assessment showing disability (if available)</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Signature Section -->
            <div class="card border-danger mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-pen"></i> Parent/Guardian Signature *</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        By signing below, I certify that all information provided in this enrollment form is true and correct to the best of my knowledge.
                    </p>
                    
                    <div class="signature-container mb-3">
                        <canvas id="signaturePad" width="600" height="200" style="border: 2px solid #dee2e6; border-radius: 8px; background: white; width: 100%; max-width: 600px; cursor: crosshair;"></canvas>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearSignature()">
                            <i class="bi bi-eraser"></i> Clear Signature
                        </button>
                        <small class="text-muted align-self-center">Draw your signature using mouse or touch</small>
                    </div>
                    
                    <!-- Hidden field for signature data -->
                    <input type="hidden" id="signature_data" name="signature_data">
                </div>
            </div>

            <!-- Confirmation -->
            <div class="alert alert-success mt-4">
                <h5><i class="bi bi-check-circle"></i> Ready to Submit?</h5>
                <p class="mb-0">
                    Please review all information before submitting. Once submitted, a SPED teacher will review your application. 
                    You will be notified via email and in-app notification about the status of your enrollment.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Make PSA Birth Certificate optional for returning students
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentType = '<?php echo $enrollmentType; ?>';
    
    if (enrollmentType === 'returning') {
        // For returning students, no file uploads are required
        console.log('Returning student - file uploads not required');
    }
});
</script>
