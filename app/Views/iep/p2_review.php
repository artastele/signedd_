<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P2 Review & Signature

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$p2Id = $_GET['id'] ?? null;
$p2Data = $p2Data ?? [];
$iepData = $p2Data['iep_data'] ?? [];
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-clipboard-check"></i> Review IEP P2 Document
                </h1>
                <p class="text-muted mt-2">Review and sign the P2 assessment document</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Document Status -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Document Status:</strong> 
                            <span class="badge" style="background-color: <?php echo $p2Data['status'] === 'reviewed_signed' ? '#3b6d11' : '#ffc107'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $p2Data['status'] ?? 'draft')); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($p2Data['created_at'] ?? 'now')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- P2 Document Content -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">P2 Document Content</h5>
            </div>
            <div class="card-body">
                <!-- Section 1: Developmental Domains -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 1: Developmental Domains Assessment</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Physical Development</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['physical_development'] ?? 'N/A')); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cognitive Development</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['cognitive_development'] ?? 'N/A')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Social-Emotional Development</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['social_emotional_development'] ?? 'N/A')); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Language Development</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['language_development'] ?? 'N/A')); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Strengths & Challenges -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 2: Strengths & Challenges</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Key Strengths</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['key_strengths'] ?? 'N/A')); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Areas for Development</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['areas_for_development'] ?? 'N/A')); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Recommendations -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 3: Recommendations</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recommended Interventions</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['recommended_interventions'] ?? 'N/A')); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Support Services Needed</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo nl2br(htmlspecialchars($iepData['support_services'] ?? 'N/A')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review & Signature Section -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">Your Review & Signature</h5>
            </div>
            <div class="card-body">
                <form id="reviewForm" method="POST" action="<?php echo $basePath; ?>/iep/p2/review-submit">
                    <input type="hidden" name="p2_id" value="<?php echo htmlspecialchars($p2Id); ?>">

                    <div class="mb-3">
                        <label class="form-label">Feedback & Comments</label>
                        <textarea class="form-control" name="feedback" rows="4" placeholder="Provide your feedback and comments..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Signature</label>
                        <div id="signaturePad" style="border: 2px solid #1e4072; border-radius: 6px; background-color: #f9f9f9; height: 200px; cursor: crosshair;"></div>
                        <small class="text-muted">Draw your signature above</small>
                        <input type="hidden" name="signature_data" id="signatureData">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary w-100" id="clearSignature">
                                <i class="fas fa-redo"></i> Clear Signature
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn w-100" style="background-color: #a01422; color: white;">
                                <i class="fas fa-check"></i> Submit Review & Sign
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Document
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
// Initialize Signature Pad
const canvas = document.getElementById('signaturePad');
const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(249, 249, 249)',
    penColor: '#1e4072'
});

// Resize canvas to fit container
function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
}

resizeCanvas();
window.addEventListener('resize', resizeCanvas);

// Clear signature
document.getElementById('clearSignature').addEventListener('click', function() {
    signaturePad.clear();
});

// Form submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (signaturePad.isEmpty()) {
        showAlert('error', 'Please provide your signature');
        return;
    }
    
    // Get signature data
    document.getElementById('signatureData').value = signaturePad.toDataURL();
    
    const formData = new FormData(this);
    
    fetch('<?php echo $basePath; ?>/iep/p2/review-submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Review and signature submitted successfully!');
            setTimeout(() => {
                window.location.href = '<?php echo $basePath; ?>/iep/p2/review';
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error submitting review');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error submitting review');
    });
});

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
