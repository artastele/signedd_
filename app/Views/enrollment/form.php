<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-02
// Part of: SPED LMS — Enrollment Form (7 Steps)

$pageTitle = 'Enrollment Form - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Prepare data for form (draft, previous enrollment, or empty)
$formData = [];
if (isset($draft) && $draft) {
    $formData = $draft;
} elseif (isset($previousEnrollment) && $previousEnrollment && $enrollmentType === 'returning') {
    $formData = $previousEnrollment;
}

// Helper function to get form value
function getFormValue($field, $default = '') {
    global $formData;
    return $formData[$field] ?? $_SESSION['old_data'][$field] ?? $default;
}

// Helper function for checkboxes
function isChecked($field) {
    global $formData;
    return !empty($formData[$field]) || !empty($_SESSION['old_data'][$field]);
}

// Debug: Check if we have previous enrollment data
$hasAutoFill = isset($previousEnrollment) && $previousEnrollment && $enrollmentType === 'returning';
if ($hasAutoFill) {
    error_log("Form view: Auto-fill active for " . $previousEnrollment['first_name'] . ' ' . $previousEnrollment['last_name']);
    error_log("Form view: enrollmentType = " . $enrollmentType);
    error_log("Form view: formData count = " . count($formData));
    error_log("Form view: Sample fields - last_name: " . ($formData['last_name'] ?? 'MISSING') . ", first_name: " . ($formData['first_name'] ?? 'MISSING'));
}
?>

<!-- Auto-fill Indicator -->
<?php if ($hasAutoFill): ?>
<style>
.auto-filled {
    background-color: #e8f5e9 !important;
    border-left: 3px solid #4caf50 !important;
    transition: background-color 0.3s ease;
}
.auto-filled:focus {
    background-color: #c8e6c9 !important;
}
</style>
<?php endif; ?>

<!-- Include BEEF Document Style -->
<link rel="stylesheet" href="<?php echo $basePath; ?>/css/beef-document.css">

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-clipboard-heart text-primary"></i> 
        <?php 
        $typeLabels = ['new' => 'New Student', 'transfer' => 'Transfer Student', 'returning' => 'Returning Student'];
        echo $typeLabels[$enrollmentType] ?? 'Enrollment';
        ?> Enrollment Form
    </h1>

    <!-- Progress Bar -->
    <div class="progress mb-4" style="height: 30px;">
        <div class="progress-bar bg-primary" role="progressbar" style="width: 14.3%">
            Step 1 of 7
        </div>
    </div>

    <!-- Auto-fill Indicator -->
    <?php if ($hasAutoFill): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <h5 class="alert-heading">
            <i class="bi bi-check-circle-fill"></i> Auto-Fill Active
        </h5>
        <p class="mb-0">
            Previous enrollment data for <strong><?php echo htmlspecialchars($previousEnrollment['first_name'] . ' ' . $previousEnrollment['last_name']); ?></strong> 
            has been loaded. Review and update any changed information.
            <?php if (!empty($previousEnrollment['lrn'])): ?>
                <br><strong>LRN:</strong> <?php echo htmlspecialchars($previousEnrollment['lrn']); ?>
            <?php endif; ?>
        </p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        
        <!-- DEBUG INFO -->
        <hr class="my-2">
        <small class="text-muted">
            <strong>Debug Info:</strong><br>
            Enrollment Type: <?php echo htmlspecialchars($enrollmentType); ?><br>
            Form Data Count: <?php echo count($formData); ?> fields loaded<br>
            Sample: Last Name = "<?php echo htmlspecialchars($formData['last_name'] ?? 'NOT SET'); ?>", 
            First Name = "<?php echo htmlspecialchars($formData['first_name'] ?? 'NOT SET'); ?>", 
            Birth Date = "<?php echo htmlspecialchars($formData['birth_date'] ?? 'NOT SET'); ?>"
        </small>
    </div>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h5>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form id="enrollmentForm" method="POST" action="<?php echo $basePath; ?>/enrollment/submit" enctype="multipart/form-data">
        <input type="hidden" name="enrollment_type" value="<?php echo htmlspecialchars($enrollmentType); ?>">
        <!-- School year is now selected in Step 5, not auto-set -->
        <?php if (isset($previousEnrollment) && $enrollmentType === 'returning'): ?>
            <input type="hidden" name="previous_enrollment_id" value="<?php echo $previousEnrollment['id']; ?>">
        <?php endif; ?>

        <?php 
        // Include all 7 form steps (simple card style)
        require_once __DIR__ . '/steps/step1_learner_info.php';
        require_once __DIR__ . '/steps/step2_current_address.php';
        require_once __DIR__ . '/steps/step3_parent_guardian.php';
        require_once __DIR__ . '/steps/step4_previous_school.php';
        require_once __DIR__ . '/steps/step5_enrollment_details.php';
        require_once __DIR__ . '/steps/step6_learning_modality.php';
        require_once __DIR__ . '/steps/step7_documents_signature.php';
        ?>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" id="prevBtn" class="btn btn-secondary" onclick="prevStep()">
                <i class="bi bi-arrow-left"></i> Previous
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="manualSave()">
                <i class="bi bi-save"></i> Save Draft
            </button>
            <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextStep()">
                Next <i class="bi bi-arrow-right"></i>
            </button>
            <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                <i class="bi bi-check-circle"></i> Submit Enrollment
            </button>
        </div>
    </form>
</div>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<!-- Enrollment Utilities -->
<script src="<?php echo $basePath; ?>/js/enrollment.js"></script>

<script>
// Override getBasePath for this form
function getBasePath() {
    return '<?php echo $basePath; ?>';
}

// AUTO-FILL: Explicitly populate fields with previous enrollment data
<?php if ($hasAutoFill && !empty($formData)): ?>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Auto-fill: Starting field population...');
    
    // Form data from PHP
    const formData = <?php echo json_encode($formData); ?>;
    
    let populatedCount = 0;
    let skippedCount = 0;
    
    // Populate each field
    for (const [fieldName, fieldValue] of Object.entries(formData)) {
        if (!fieldValue || fieldValue === '' || fieldValue === null) {
            continue; // Skip empty values
        }
        
        // Try to find field by name or id
        const field = document.querySelector(`[name="${fieldName}"]`) || document.getElementById(fieldName);
        
        if (field) {
            // Handle different input types
            if (field.type === 'checkbox') {
                field.checked = (fieldValue == 1 || fieldValue === true || fieldValue === 'on');
                if (field.checked) {
                    populatedCount++;
                    console.log(`✓ Checkbox: ${fieldName} = checked`);
                }
            } else if (field.type === 'radio') {
                if (field.value === fieldValue) {
                    field.checked = true;
                    populatedCount++;
                    console.log(`✓ Radio: ${fieldName} = ${fieldValue}`);
                }
            } else if (field.tagName === 'SELECT') {
                // For select dropdowns
                field.value = fieldValue;
                populatedCount++;
                console.log(`✓ Select: ${fieldName} = ${fieldValue}`);
            } else {
                // For text, date, number, etc.
                field.value = fieldValue;
                populatedCount++;
                
                // Add visual indicator (green background)
                field.classList.add('auto-filled');
                
                console.log(`✓ Input: ${fieldName} = ${fieldValue}`);
            }
        } else {
            skippedCount++;
        }
    }
    
    console.log(`✅ Auto-fill complete: ${populatedCount} fields populated, ${skippedCount} skipped`);
    
    // Show success message
    if (populatedCount > 0) {
        console.log('🎉 Form auto-filled successfully!');
    }
});
<?php endif; ?>

// Add signature data to form before submit
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Always prevent default first
    
    const form = this;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const enrollmentType = '<?php echo $enrollmentType; ?>';
    
    // Validation errors array
    const errors = [];
    
    // Step 1: Learner Information
    if (!data.last_name || data.last_name.trim() === '') {
        errors.push('❌ Step 1: Last Name is required');
    }
    if (!data.first_name || data.first_name.trim() === '') {
        errors.push('❌ Step 1: First Name is required');
    }
    if (!data.birth_date || data.birth_date.trim() === '') {
        errors.push('❌ Step 1: Birth Date is required');
    }
    if (!data.sex || data.sex.trim() === '') {
        errors.push('❌ Step 1: Sex is required');
    }
    if (!data.birth_place || data.birth_place.trim() === '') {
        errors.push('❌ Step 1: Place of Birth is required');
    }
    
    // Step 2: Current Address (basic check)
    if (!data.current_city || data.current_city.trim() === '') {
        errors.push('❌ Step 2: Current City/Municipality is required');
    }
    if (!data.current_province || data.current_province.trim() === '') {
        errors.push('❌ Step 2: Current Province is required');
    }
    if (!data.current_barangay || data.current_barangay.trim() === '') {
        errors.push('❌ Step 2: Current Barangay is required');
    }
    
    // Step 4: Previous School (only for transfer students)
    if (enrollmentType === 'transfer') {
        if (!data.previous_school_name || data.previous_school_name.trim() === '') {
            errors.push('❌ Step 4: Previous School Name is required for transfer students');
        }
    }
    
    // Step 5: Enrollment Details
    if (!data.school_year || data.school_year.trim() === '') {
        errors.push('❌ Step 5: School Year is required');
    }
    if (!data.grade_level_to_enroll || data.grade_level_to_enroll.trim() === '') {
        errors.push('❌ Step 5: Grade Level to Enroll is required');
    }
    
    // Step 6: Learning Modality (at least one must be checked)
    const modalityChecked = data.modality_modular_print || data.modality_modular_digital || 
                           data.modality_online || data.modality_educational_tv || 
                           data.modality_radio || data.modality_blended || 
                           data.modality_face_to_face;
    if (!modalityChecked) {
        errors.push('❌ Step 6: Please select at least one learning modality');
    }
    
    // Step 7: Signature
    const signatureData = getSignatureData();
    if (!signatureData) {
        errors.push('❌ Step 7: Parent/Guardian signature is required');
    }
    
    // Step 7: Documents (only for new/transfer students)
    if (enrollmentType !== 'returning') {
        // Check if PSA birth certificate is uploaded using the new upload component
        const psaPreview = document.querySelector('input[name="psa_birth_cert"]');
        const psaUploaded = psaPreview && psaPreview.files && psaPreview.files.length > 0;
        
        if (!psaUploaded) {
            errors.push('❌ Step 7: PSA Birth Certificate is required');
        }
    }
    
    // If there are errors, show them
    if (errors.length > 0) {
        let errorMsg = '⚠️  PLEASE COMPLETE THE FOLLOWING REQUIRED FIELDS:\n\n';
        errorMsg += errors.join('\n');
        errorMsg += '\n\n📝 Please go back and fill in the missing information.';
        
        alert(errorMsg);
        
        // Try to navigate to first error step
        if (errors[0].includes('Step 1')) showStep(1);
        else if (errors[0].includes('Step 2')) showStep(2);
        else if (errors[0].includes('Step 4')) showStep(4);
        else if (errors[0].includes('Step 5')) showStep(5);
        else if (errors[0].includes('Step 6')) showStep(6);
        else if (errors[0].includes('Step 7')) showStep(7);
        
        return false;
    }
    
    // All validation passed - show confirmation
    let confirmMsg = '📋 PLEASE REVIEW BEFORE SUBMITTING:\n\n';
    confirmMsg += `👤 Name: ${data.first_name || ''} ${data.middle_name || ''} ${data.last_name || ''}\n`;
    confirmMsg += `📅 Birth Date: ${data.birth_date || 'Not set'}\n`;
    confirmMsg += `🏠 Birth Place: ${data.birth_place || 'Not set'}\n`;
    confirmMsg += `👨‍👩‍👧 Grade Level: ${data.grade_level_to_enroll || 'Not set'}\n`;
    confirmMsg += `🌍 City: ${data.current_city || 'Not set'}\n`;
    confirmMsg += `📍 Province: ${data.current_province || 'Not set'}\n\n`;
    confirmMsg += '✅ All required fields are complete!\n\n';
    confirmMsg += '⚠️  Once submitted, your enrollment will be sent for review.\n\n';
    confirmMsg += 'Do you want to continue with the submission?';
    
    if (confirm(confirmMsg)) {
        // User confirmed - add signature and submit
        document.getElementById('signature_data').value = signatureData;
        
        // Show loading indicator
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        }
        
        form.submit();
    }
});
</script>

<?php 
unset($_SESSION['old_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>
