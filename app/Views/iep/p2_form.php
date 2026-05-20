<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P2 Form (Developmental Domains Assessment)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$meetingId = $_GET['id'] ?? null;
$formType = $_GET['type'] ?? 'fill'; // 'fill' or 'upload'
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-file-alt"></i> IEP P2 - Developmental Domains Assessment
                </h1>
                <p class="text-muted mt-2">Create or upload IEP P2 document</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Form Type Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="formType" id="fillForm" value="fill" <?php echo $formType === 'fill' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-secondary" for="fillForm" style="border-color: #1e4072; color: #1e4072;">
                        <i class="fas fa-pen"></i> Fill Online Form
                    </label>

                    <input type="radio" class="btn-check" name="formType" id="uploadForm" value="upload" <?php echo $formType === 'upload' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-secondary" for="uploadForm" style="border-color: #1e4072; color: #1e4072;">
                        <i class="fas fa-cloud-upload-alt"></i> Upload PDF
                    </label>
                </div>
            </div>
        </div>

        <!-- Online Form Section -->
        <div id="fillFormSection" style="display: <?php echo $formType === 'fill' ? 'block' : 'none'; ?>;">
            <form id="p2Form" method="POST" action="<?php echo $basePath; ?>/iep/p2/submit">
                <input type="hidden" name="meeting_id" value="<?php echo htmlspecialchars($meetingId); ?>">
                <input type="hidden" name="form_type" value="online">

                <!-- Section 1: Developmental Domains -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 1: Developmental Domains Assessment</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Physical Development</label>
                                <textarea class="form-control" name="physical_development" rows="3" placeholder="Describe physical development..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cognitive Development</label>
                                <textarea class="form-control" name="cognitive_development" rows="3" placeholder="Describe cognitive development..."></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Social-Emotional Development</label>
                                <textarea class="form-control" name="social_emotional_development" rows="3" placeholder="Describe social-emotional development..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Language Development</label>
                                <textarea class="form-control" name="language_development" rows="3" placeholder="Describe language development..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Strengths & Challenges -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 2: Strengths & Challenges</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Key Strengths</label>
                            <textarea class="form-control" name="key_strengths" rows="3" placeholder="List key strengths..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Areas for Development</label>
                            <textarea class="form-control" name="areas_for_development" rows="3" placeholder="List areas for development..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Recommendations -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 3: Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Recommended Interventions</label>
                            <textarea class="form-control" name="recommended_interventions" rows="3" placeholder="List recommended interventions..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Support Services Needed</label>
                            <textarea class="form-control" name="support_services" rows="3" placeholder="List support services..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn w-100" style="background-color: #a01422; color: white;">
                                    <i class="fas fa-save"></i> Save P2 Document
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print Document
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Upload Form Section -->
        <div id="uploadFormSection" style="display: <?php echo $formType === 'upload' ? 'block' : 'none'; ?>;">
            <form id="uploadP2Form" method="POST" action="<?php echo $basePath; ?>/iep/p2/upload" enctype="multipart/form-data">
                <input type="hidden" name="meeting_id" value="<?php echo htmlspecialchars($meetingId); ?>">

                <div class="card">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Upload Pre-filled P2 Document</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Select PDF File</label>
                            <?php 
                            $fieldName = 'pdf_file';
                            $acceptedTypes = '.pdf';
                            $maxSize = 10;
                            $showCamera = true;
                            include __DIR__ . '/../components/upload-zone.php';
                            ?>
                            <small class="text-muted">Maximum file size: 10MB. PDF format only.</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Upload a pre-filled P2 document in PDF format. The document will be stored and can be reviewed by Guidance and Principal.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn w-100" style="background-color: #a01422; color: white;">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload PDF
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="history.back()">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="formType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('fillFormSection').style.display = this.value === 'fill' ? 'block' : 'none';
        document.getElementById('uploadFormSection').style.display = this.value === 'upload' ? 'block' : 'none';
    });
});

// Form submission
document.getElementById('p2Form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo $basePath; ?>/iep/p2/submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'P2 Document saved successfully!');
            setTimeout(() => {
                window.location.href = '<?php echo $basePath; ?>/iep/p2/review/' + data.p2_id;
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error saving P2 document');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error saving P2 document');
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
