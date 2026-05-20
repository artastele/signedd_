<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-06
// Part of: SPED LMS — Assessment Form (Part I - Section A)

$pageTitle = 'Conduct Assessment - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-clipboard-check text-primary"></i> 
        Part I: Learner's Educational Assessment
    </h1>

    <!-- Student Selector -->
    <div class="card border-primary mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-circle"></i> Select Student
            </h5>
        </div>
        <div class="card-body">
            <label for="student_selector" class="form-label">
                <strong>Student to Assess</strong> <span class="text-danger">*</span>
            </label>
            <div class="row g-2">
                <div class="col-md-10">
                    <select class="form-select form-select-lg" id="student_selector" required>
                        <option value="">-- Select a verified student --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>" 
                                    <?php echo ($studentId == $student['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['student_name']); ?> 
                                (LRN: <?php echo htmlspecialchars($student['lrn']); ?>) - 
                                <?php echo htmlspecialchars($student['grade_level_to_enroll']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Select a student with verified enrollment status</div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="loadStudentData()" style="height: 48px;">
                        <i class="bi bi-arrow-clockwise"></i> Load Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Form -->
    <form id="assessmentForm" method="POST" action="<?php echo $basePath; ?>/assessment/submit" enctype="multipart/form-data">
        <input type="hidden" name="student_id" id="student_id" value="<?php echo $studentId ?? ''; ?>">
        <input type="hidden" name="assessment_id" id="assessment_id" value="">

        <!-- Section A: Learner Information (Auto-fill) -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge"></i> Section A: Learner Information
                </h5>
            </div>
            <div class="card-body">
                <!-- Auto-fill Indicator -->
                <div id="autofill-indicator" class="alert alert-success d-none" role="alert">
                    <i class="bi bi-check-circle-fill"></i> 
                    <strong>Auto-filled from student records.</strong> All fields are editable - please review and update if needed.
                </div>

                <!-- Personal Information -->
                <h6 class="text-secondary mb-3">Personal Information</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control auto-fill-field" name="last_name" id="last_name" 
                               value="<?php echo htmlspecialchars($studentData['last_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control auto-fill-field" name="first_name" id="first_name" 
                               value="<?php echo htmlspecialchars($studentData['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control auto-fill-field" name="middle_name" id="middle_name" 
                               value="<?php echo htmlspecialchars($studentData['middle_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Name Extension</label>
                        <input type="text" class="form-control auto-fill-field" name="extension_name" id="extension_name" 
                               value="<?php echo htmlspecialchars($studentData['extension_name'] ?? ''); ?>" 
                               placeholder="Jr., Sr., III">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control auto-fill-field" name="birth_date" id="birth_date" 
                               value="<?php echo $studentData['birth_date'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Age</label>
                        <input type="number" class="form-control auto-fill-field" name="age" id="age" 
                               value="<?php echo $studentData['age'] ?? ''; ?>" readonly>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Sex <span class="text-danger">*</span></label>
                        <select class="form-select auto-fill-field" name="sex" id="sex" required>
                            <option value="">--</option>
                            <option value="Male" <?php echo ($studentData['sex'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($studentData['sex'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Religion</label>
                        <input type="text" class="form-control" name="religion" id="religion" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Home Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control auto-fill-field" name="home_address" id="home_address" 
                               value="<?php echo htmlspecialchars($studentData['full_address'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- School Information -->
                <h6 class="text-secondary mb-3 mt-4">School Information</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">LRN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control auto-fill-field" name="lrn" id="lrn" 
                               value="<?php echo htmlspecialchars($studentData['lrn'] ?? ''); ?>" 
                               pattern="\d{12}" maxlength="12" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">School</label>
                        <input type="text" class="form-control" name="school" id="school" value="">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">School Year <span class="text-danger">*</span></label>
                        <?php
                        $currentYear = (int)date('Y');
                        $syOptions = [];
                        for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
                            $syOptions[] = $y . '-' . ($y + 1);
                        }
                        $selectedSY = $studentData['school_year'] ?? ($currentYear . '-' . ($currentYear + 1));
                        ?>
                        <select class="form-select auto-fill-field" name="school_year" id="school_year" required>
                            <?php foreach ($syOptions as $sy): ?>
                                <option value="<?php echo $sy; ?>" <?php echo $selectedSY === $sy ? 'selected' : ''; ?>>
                                    <?php echo $sy; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Name of Adviser</label>
                        <input type="text" class="form-control" name="adviser_name" id="adviser_name" value="">
                    </div>
                </div>

                <!-- Parent/Guardian Information -->
                <h6 class="text-secondary mb-3 mt-4">Parent/Guardian Information</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Father's Name</label>
                        <input type="text" class="form-control auto-fill-field" name="father_name" id="father_name" 
                               value="<?php echo htmlspecialchars($studentData['father_full_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Father's Contact</label>
                        <input type="text" class="form-control auto-fill-field" name="father_contact" id="father_contact" 
                               value="<?php echo htmlspecialchars($studentData['father_contact_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Father's Occupation</label>
                        <input type="text" class="form-control" name="father_occupation" id="father_occupation" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mother's Name</label>
                        <input type="text" class="form-control auto-fill-field" name="mother_name" id="mother_name" 
                               value="<?php echo htmlspecialchars($studentData['mother_full_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mother's Contact</label>
                        <input type="text" class="form-control auto-fill-field" name="mother_contact" id="mother_contact" 
                               value="<?php echo htmlspecialchars($studentData['mother_contact_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mother's Occupation</label>
                        <input type="text" class="form-control" name="mother_occupation" id="mother_occupation" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Guardian/Caregiver Name</label>
                        <input type="text" class="form-control auto-fill-field" name="guardian_name" id="guardian_name" 
                               value="<?php echo htmlspecialchars($studentData['guardian_full_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Guardian Contact</label>
                        <input type="text" class="form-control auto-fill-field" name="guardian_contact" id="guardian_contact" 
                               value="<?php echo htmlspecialchars($studentData['guardian_contact_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Guardian Occupation</label>
                        <input type="text" class="form-control" name="guardian_occupation" id="guardian_occupation" value="">
                    </div>
                </div>

                <!-- Education History -->
                <h6 class="text-secondary mb-3 mt-4">Education History</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Previous School Attended</label>
                        <input type="text" class="form-control auto-fill-field" name="previous_school" id="previous_school" 
                               value="<?php echo htmlspecialchars($studentData['previous_school_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Grade Level</label>
                        <?php
                        $gradeLevels = ['Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
                                        'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12',
                                        'SPED Program'];
                        $selectedGL = $studentData['previous_grade_level'] ?? '';
                        ?>
                        <select class="form-select auto-fill-field" name="previous_grade_level" id="previous_grade_level">
                            <option value="">-- Select --</option>
                            <?php foreach ($gradeLevels as $gl): ?>
                                <option value="<?php echo $gl; ?>" <?php echo $selectedGL === $gl ? 'selected' : ''; ?>>
                                    <?php echo $gl; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">School Year</label>
                        <input type="text" class="form-control auto-fill-field" name="previous_school_year" id="previous_school_year" 
                               value="<?php echo htmlspecialchars($studentData['previous_school_year'] ?? ''); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">With IEP?</label>
                        <select class="form-select" name="with_iep" id="with_iep">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">With Support Services?</label>
                        <select class="form-select" name="with_support_services" id="with_support_services" onchange="toggleServiceCheckboxes()">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                        <div class="form-text">If "No", service checkboxes below will be disabled</div>
                    </div>
                </div>

                <!-- Services/Screening Unified Checklist -->
                <h6 class="text-secondary mb-3 mt-4">Services / Screening Checklist</h6>
                <p class="text-muted small">Check all services and screening types that apply to this assessment</p>
                
                <div id="services-checklist-container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Occupational Therapy" id="service_ot">
                                <label class="form-check-label" for="service_ot">
                                    Occupational Therapy
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Physical Therapy" id="service_pt">
                                <label class="form-check-label" for="service_pt">
                                    Physical Therapy
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Behavioral Therapy" id="service_bt">
                                <label class="form-check-label" for="service_bt">
                                    Behavioral Therapy
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Psychosocial Intervention" id="service_psi">
                                <label class="form-check-label" for="service_psi">
                                    Psychosocial Intervention
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Speech and Language Therapy" id="service_slt">
                                <label class="form-check-label" for="service_slt">
                                    Speech and Language Therapy
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Daily Living Skills" id="service_dls">
                                <label class="form-check-label" for="service_dls">
                                    Daily Living Skills
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Skills Development" id="service_sd">
                                <label class="form-check-label" for="service_sd">
                                    Skills Development
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="MFAT" id="service_mfat">
                                <label class="form-check-label" for="service_mfat">
                                    MFAT
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="ECCD Checklist" id="service_eccd">
                                <label class="form-check-label" for="service_eccd">
                                    ECCD Checklist
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Psycho-Educational" id="service_psycho">
                                <label class="form-check-label" for="service_psycho">
                                    Psycho-Educational
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                       value="Others" id="service_others" onchange="toggleOthersInput()">
                                <label class="form-check-label" for="service_others">
                                    Others (specify)
                                </label>
                            </div>
                            <input type="text" class="form-control mt-2 d-none" name="services_others_specify" 
                                   id="services_others_specify" placeholder="Specify other services">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B: MDT Assessment Table (Dynamic, Service-Driven) -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i> Section B: Multi-Disciplinary Team (MDT) Assessment
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle"></i> 
                    This table is driven by the services you checked in Section A. 
                    Only checked services will appear as rows below.
                </p>

                <div id="mdt-table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="mdt-table">
                            <thead style="background-color: #1e4072; color: white;">
                                <tr>
                                    <th style="width: 20%;">Service</th>
                                    <th style="width: 35%;">MDT Members</th>
                                    <th style="width: 20%;">Date/s of Assessment</th>
                                    <th style="width: 25%;">Supporting Documents</th>
                                </tr>
                            </thead>
                            <tbody id="mdt-table-body">
                                <!-- Rows will be dynamically added here based on checked services -->
                            </tbody>
                        </table>
                    </div>

                    <div id="no-services-message" class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>No services selected.</strong> Please check at least one service in Section A above.
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-between mb-4">
            <a href="<?php echo $basePath; ?>/assessment" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <div>
                <button type="button" class="btn btn-outline-primary me-2" onclick="saveDraft()">
                    <i class="bi bi-save"></i> Save Draft
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Save & Continue to Section B
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.auto-fill-field {
    background-color: #e8f5e9 !important;
    border-left: 3px solid #4caf50 !important;
}
.auto-fill-field:focus {
    background-color: #c8e6c9 !important;
}
.form-check-input:checked {
    background-color: #a01422;
    border-color: #a01422;
}
#mdt-table thead th {
    background-color: #1e4072;
    color: white;
    font-weight: 600;
}
#mdt-table tbody td {
    vertical-align: middle;
}
.mdt-member-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
.mdt-member-item input {
    flex: 1;
}
.btn-remove-member {
    padding: 2px 8px;
    font-size: 12px;
}
.file-upload-slot {
    border: 2px dashed #dee2e6;
    border-radius: 4px;
    padding: 12px;
    text-align: center;
    transition: all 0.3s;
}
.file-upload-slot:hover {
    border-color: #1e4072;
    background-color: #f8f9fa;
}
.file-upload-slot.has-file {
    border-color: #4caf50;
    background-color: #e8f5e9;
}
.file-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background-color: #e8f5e9;
    border-radius: 4px;
    margin-top: 8px;
}
.file-preview i {
    font-size: 24px;
    color: #4caf50;
}
.file-item {
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
.file-upload-container {
    min-height: 80px;
}
</style>

<script>
// MDT Table Management
const mdtTableBody = document.getElementById('mdt-table-body');
const noServicesMessage = document.getElementById('no-services-message');
const serviceCheckboxes = document.querySelectorAll('.service-checkbox');

// Track MDT data per service
const mdtData = {};

// Initialize MDT table on page load
document.addEventListener('DOMContentLoaded', function() {
    updateMDTTable();
    
    // Add event listeners to service checkboxes
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateMDTTable);
    });
});

// Update MDT table based on checked services
function updateMDTTable() {
    const checkedServices = Array.from(serviceCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
    
    // Clear table
    mdtTableBody.innerHTML = '';
    
    if (checkedServices.length === 0) {
        noServicesMessage.style.display = 'block';
        document.getElementById('mdt-table').style.display = 'none';
        return;
    }
    
    noServicesMessage.style.display = 'none';
    document.getElementById('mdt-table').style.display = 'table';
    
    // Add row for each checked service
    checkedServices.forEach(service => {
        const row = createMDTRow(service);
        mdtTableBody.appendChild(row);
    });
}

// Create MDT row for a service
function createMDTRow(serviceName) {
    const row = document.createElement('tr');
    row.dataset.service = serviceName;
    
    // Initialize data structure if not exists
    if (!mdtData[serviceName]) {
        mdtData[serviceName] = {
            members: [],
            date: '',
            file: null
        };
    }
    
    row.innerHTML = `
        <td>
            <strong>${serviceName}</strong>
            <input type="hidden" name="mdt_services[]" value="${serviceName}">
        </td>
        <td>
            <div class="mdt-members-container" id="members-${sanitizeId(serviceName)}">
                <!-- Members will be added here -->
            </div>
            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addMDTMember('${serviceName}')">
                <i class="bi bi-plus-circle"></i> Add Member
            </button>
        </td>
        <td>
            <input type="date" class="form-control" name="mdt_date_${sanitizeId(serviceName)}" 
                   id="date-${sanitizeId(serviceName)}" 
                   onchange="saveMDTDate('${serviceName}', this.value)">
        </td>
        <td>
            <div class="file-upload-container" id="upload-container-${sanitizeId(serviceName)}">
                <input type="file" class="d-none" id="file-${sanitizeId(serviceName)}" 
                       name="mdt_file_${sanitizeId(serviceName)}[]" 
                       accept=".jpg,.jpeg,.png,.pdf"
                       multiple
                       onchange="handleMultipleFileUpload('${serviceName}', this)">
                <button type="button" class="btn btn-sm" style="background-color: #1e4072; color: white;" 
                        onclick="document.getElementById('file-${sanitizeId(serviceName)}').click()">
                    <i class="bi bi-plus-circle"></i> Add Document
                </button>
                <div id="file-list-${sanitizeId(serviceName)}" class="mt-2">
                    <!-- Files will be listed here -->
                </div>
                <small class="text-muted d-block mt-1">JPG, PNG, or PDF (max 10MB each)</small>
            </div>
        </td>
    `;
    
    return row;
}

// Sanitize service name for use as ID
function sanitizeId(str) {
    return str.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
}

// Add MDT member to a service
function addMDTMember(serviceName) {
    const container = document.getElementById(`members-${sanitizeId(serviceName)}`);
    const memberIndex = mdtData[serviceName].members.length;
    
    const memberDiv = document.createElement('div');
    memberDiv.className = 'mdt-member-item';
    memberDiv.innerHTML = `
        <input type="text" class="form-control form-control-sm" 
               name="mdt_member_name_${sanitizeId(serviceName)}[]" 
               placeholder="Member name" required>
        <input type="text" class="form-control form-control-sm" 
               name="mdt_member_designation_${sanitizeId(serviceName)}[]" 
               placeholder="Designation" required>
        <button type="button" class="btn btn-sm btn-danger btn-remove-member" 
                onclick="removeMDTMember(this, '${serviceName}', ${memberIndex})">
            <i class="bi bi-trash"></i>
        </button>
    `;
    
    container.appendChild(memberDiv);
    mdtData[serviceName].members.push({ name: '', designation: '' });
}

// Remove MDT member
function removeMDTMember(button, serviceName, index) {
    button.closest('.mdt-member-item').remove();
    mdtData[serviceName].members.splice(index, 1);
}

// Save MDT date
function saveMDTDate(serviceName, date) {
    mdtData[serviceName].date = date;
}

// Track uploaded files per service
const uploadedFiles = {};

// Handle multiple file upload
function handleMultipleFileUpload(serviceName, input) {
    const files = Array.from(input.files);
    const serviceId = sanitizeId(serviceName);
    
    if (!uploadedFiles[serviceName]) {
        uploadedFiles[serviceName] = [];
    }
    
    files.forEach(file => {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert(`Invalid file type: ${file.name}. Only JPG, PNG, PDF allowed.`);
            return;
        }
        
        // Validate file size (10MB)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`File too large: ${file.name}. Maximum size is 10MB.`);
            return;
        }
        
        // Add to uploaded files
        uploadedFiles[serviceName].push(file);
    });
    
    // Update file list display
    updateFileList(serviceName);
    
    // Reset input to allow re-selecting same files
    input.value = '';
}

function updateFileList(serviceName) {
    const serviceId = sanitizeId(serviceName);
    const container = document.getElementById(`file-list-${serviceId}`);
    const files = uploadedFiles[serviceName] || [];
    
    if (files.length === 0) {
        container.innerHTML = '<small class="text-muted">No documents uploaded</small>';
        return;
    }
    
    container.innerHTML = files.map((file, index) => `
        <div class="file-item d-flex align-items-center gap-2 p-2 mb-2" 
             style="background-color: #e8f5e9; border-radius: 4px; border-left: 3px solid #3b6d11;">
            <i class="bi bi-file-earmark-check-fill text-success"></i>
            <span class="flex-grow-1 small text-truncate" title="${file.name}">${file.name}</span>
            <span class="badge" style="background-color: #3b6d11;">${formatFileSize(file.size)}</span>
            <button type="button" class="btn btn-sm btn-danger" 
                    onclick="removeUploadedFile('${serviceName}', ${index})">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');
}

function removeUploadedFile(serviceName, index) {
    if (uploadedFiles[serviceName]) {
        uploadedFiles[serviceName].splice(index, 1);
        updateFileList(serviceName);
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Auto-calculate age from birth date
document.getElementById('birth_date').addEventListener('change', function() {
    const birthDate = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    document.getElementById('age').value = age;
});

// Load student data via AJAX
function loadStudentData() {
    const studentId = document.getElementById('student_selector').value;
    
    if (!studentId) {
        alert('Please select a student first');
        return;
    }
    
    // Redirect to conduct page with student ID
    window.location.href = '<?php echo $basePath; ?>/assessment/conduct/' + studentId;
}

// Student selector change
document.getElementById('student_selector').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('student_id').value = this.value;
    }
});

// Toggle "Others" input
function toggleOthersInput() {
    const checkbox = document.getElementById('service_others');
    const input = document.getElementById('services_others_specify');
    
    if (checkbox.checked) {
        input.classList.remove('d-none');
        input.required = true;
    } else {
        input.classList.add('d-none');
        input.required = false;
        input.value = '';
    }
}

// Toggle service checkboxes based on "With Support Services?" selection
function toggleServiceCheckboxes() {
    const withServices = document.getElementById('with_support_services').value;
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const servicesContainer = document.getElementById('services-checklist-container');
    
    if (withServices === 'no') {
        // Disable all service checkboxes and uncheck them
        serviceCheckboxes.forEach(checkbox => {
            checkbox.disabled = true;
            checkbox.checked = false;
        });
        servicesContainer.style.opacity = '0.5';
        servicesContainer.style.pointerEvents = 'none';
        
        // Clear MDT table
        updateMDTTable();
    } else {
        // Enable all service checkboxes
        serviceCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
        servicesContainer.style.opacity = '1';
        servicesContainer.style.pointerEvents = 'auto';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleServiceCheckboxes();
});

// Show auto-fill indicator if student data loaded
<?php if ($studentData): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('autofill-indicator').classList.remove('d-none');
});
<?php endif; ?>

// Save draft
function saveDraft() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);
    formData.append('save_draft', '1');
    
    fetch('<?php echo $basePath; ?>/assessment/save-draft', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Draft saved successfully');
        } else {
            alert('Error saving draft: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving draft');
    });
}

// Form validation before submit
document.getElementById('assessmentForm').addEventListener('submit', function(e) {
    const studentId = document.getElementById('student_id').value;
    
    if (!studentId) {
        e.preventDefault();
        alert('Please select a student first');
        return false;
    }
    
    // Re-attach files from uploadedFiles JS array back into the file inputs
    // (they were cleared from inputs after selection to allow re-selection)
    Object.keys(uploadedFiles).forEach(serviceName => {
        const files = uploadedFiles[serviceName];
        if (!files || files.length === 0) return;
        const serviceId = sanitizeId(serviceName);
        const input = document.getElementById(`file-${serviceId}`);
        if (!input) return;
        try {
            const dt = new DataTransfer();
            files.forEach(file => dt.items.add(file));
            input.files = dt.files;
        } catch (err) {
            console.warn('DataTransfer not supported, files may not upload:', err);
        }
    });
    
    // Check if "With Support Services?" is "Yes"
    const withServices = document.getElementById('with_support_services').value;
    
    if (withServices === 'yes') {
        // Only validate services if "With Support Services?" is "Yes"
        
        // Check if at least one service is checked
        const checkedServices = Array.from(serviceCheckboxes).filter(cb => cb.checked);
        if (checkedServices.length === 0) {
            e.preventDefault();
            alert('Please check at least one service in Section A');
            return false;
        }
        
        // Validate MDT data for each checked service
        let validationErrors = [];
        
        checkedServices.forEach(checkbox => {
            const serviceName = checkbox.value;
            const serviceId = sanitizeId(serviceName);
            
            // Check if at least one member added
            const memberInputs = document.querySelectorAll(`input[name="mdt_member_name_${serviceId}[]"]`);
            if (memberInputs.length === 0) {
                validationErrors.push(`${serviceName}: Please add at least one MDT member`);
            }
            
            // Check if date is filled
            const dateInput = document.getElementById(`date-${serviceId}`);
            if (!dateInput || !dateInput.value) {
                validationErrors.push(`${serviceName}: Please select assessment date`);
            }
        });
        
        if (validationErrors.length > 0) {
            e.preventDefault();
            alert('Please complete the following:\n\n' + validationErrors.join('\n'));
            return false;
        }
    } else {
        // If "With Support Services?" is "No", skip service validation
        // Allow submission without services
        console.log('No support services required - skipping service validation');
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
