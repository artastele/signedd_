<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P3 Form (Final IEP Document)

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
                    <i class="fas fa-file-alt"></i> IEP P3 - Final Individualized Education Plan
                </h1>
                <p class="text-muted mt-2">Create or upload final IEP document</p>
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
            <form id="p3Form" method="POST" action="<?php echo $basePath; ?>/iep/p3/submit">
                <input type="hidden" name="meeting_id" value="<?php echo htmlspecialchars($meetingId); ?>">
                <input type="hidden" name="form_type" value="online">

                <!-- Section 1: Student Information -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 1: Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student Name</label>
                                <input type="text" class="form-control" name="student_name" placeholder="Student name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LRN</label>
                                <input type="text" class="form-control" name="lrn" placeholder="Learner Reference Number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Grade Level</label>
                                <input type="text" class="form-control" name="grade_level" placeholder="Grade level" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">School Year</label>
                                <input type="text" class="form-control" name="school_year" placeholder="School year" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Present Level of Performance -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 2: Present Level of Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Academic Performance</label>
                            <textarea class="form-control" name="academic_performance" rows="3" placeholder="Describe academic performance..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Behavioral Performance</label>
                            <textarea class="form-control" name="behavioral_performance" rows="3" placeholder="Describe behavioral performance..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Social Performance</label>
                            <textarea class="form-control" name="social_performance" rows="3" placeholder="Describe social performance..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Annual Goals & Objectives -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 3: Annual Goals & Objectives</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Goal 1</label>
                            <textarea class="form-control" name="goal_1" rows="2" placeholder="Enter annual goal 1..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goal 2</label>
                            <textarea class="form-control" name="goal_2" rows="2" placeholder="Enter annual goal 2..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goal 3</label>
                            <textarea class="form-control" name="goal_3" rows="2" placeholder="Enter annual goal 3..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Special Education Services -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 4: Special Education Services</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Services to be Provided</label>
                            <textarea class="form-control" name="services_provided" rows="3" placeholder="List services to be provided..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Frequency & Duration</label>
                            <textarea class="form-control" name="frequency_duration" rows="2" placeholder="Specify frequency and duration..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Accommodations & Modifications -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Section 5: Accommodations & Modifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Accommodations</label>
                            <textarea class="form-control" name="accommodations" rows="3" placeholder="List accommodations..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modifications</label>
                            <textarea class="form-control" name="modifications" rows="3" placeholder="List modifications..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn w-100" style="background-color: #a01422; color: white;">
                                    <i class="fas fa-save"></i> Save P3 Document
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
            <form id="uploadP3Form" method="POST" action="<?php echo $basePath; ?>/iep/p3/upload" enctype="multipart/form-data">
                <input type="hidden" name="meeting_id" value="<?php echo htmlspecialchars($meetingId); ?>">

                <div class="card">
                    <div class="card-header" style="background-color: #1e4072; color: white;">
                        <h5 class="mb-0">Upload Pre-filled P3 Document</h5>
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
                            <strong>Note:</strong> Upload a pre-filled P3 document in PDF format. The document will be stored and sent for signatures.
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
document.getElementById('p3Form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo $basePath; ?>/iep/p3/submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'P3 Document saved successfully!');
            setTimeout(() => {
                window.location.href = '<?php echo $basePath; ?>/iep/p3/sign/' + data.p3_id;
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error saving P3 document');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error saving P3 document');
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
