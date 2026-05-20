<!-- Step 4: Previous School Information -->
<div class="form-step" id="step-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-building"></i> Step 4: Previous School Information
            </h4>
            
            <?php if ($enrollmentType === 'transfer'): ?>
                <!-- Transfer Student - Show form -->
                <div class="alert alert-warning">
                    <strong><i class="bi bi-exclamation-triangle"></i> Required for Transfer Students</strong>
                    <p class="mb-0">Please provide information about your previous school.</p>
                </div>

                <div class="row">
                    <!-- School ID -->
                    <div class="col-md-6 mb-3">
                        <label for="previous_school_id" class="form-label">School ID</label>
                        <input type="text" class="form-control" id="previous_school_id" name="previous_school_id" 
                               value="<?php echo htmlspecialchars(getFormValue('previous_school_id')); ?>"
                               placeholder="e.g., 123456">
                        <div class="form-text">Optional - If known</div>
                    </div>

                    <!-- School Type -->
                    <div class="col-md-6 mb-3">
                        <label for="previous_school_type" class="form-label">School Type</label>
                        <select class="form-select" id="previous_school_type" name="previous_school_type">
                            <option value="">-- Select --</option>
                            <option value="Public" <?php echo getFormValue('previous_school_type') === 'Public' ? 'selected' : ''; ?>>Public</option>
                            <option value="Private" <?php echo getFormValue('previous_school_type') === 'Private' ? 'selected' : ''; ?>>Private</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- School Name -->
                    <div class="col-md-12 mb-3">
                        <label for="previous_school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="previous_school_name" name="previous_school_name" 
                               value="<?php echo htmlspecialchars(getFormValue('previous_school_name')); ?>"
                               placeholder="e.g., ABC Elementary School"
                               required>
                    </div>
                </div>

                <div class="row">
                    <!-- School Address -->
                    <div class="col-md-12 mb-3">
                        <label for="previous_school_address" class="form-label">School Address</label>
                        <textarea class="form-control" id="previous_school_address" name="previous_school_address" 
                                  rows="2" placeholder="Complete address of previous school"><?php echo htmlspecialchars(getFormValue('previous_school_address')); ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <!-- Grade Level Completed -->
                    <div class="col-md-6 mb-3">
                        <label for="previous_grade_level" class="form-label">Grade Level Completed</label>
                        <select class="form-select" id="previous_grade_level" name="previous_grade_level">
                            <option value="">-- Select --</option>
                            <option value="Kinder" <?php echo getFormValue('previous_grade_level') === 'Kinder' ? 'selected' : ''; ?>>Kinder</option>
                            <option value="Grade 1" <?php echo getFormValue('previous_grade_level') === 'Grade 1' ? 'selected' : ''; ?>>Grade 1</option>
                            <option value="Grade 2" <?php echo getFormValue('previous_grade_level') === 'Grade 2' ? 'selected' : ''; ?>>Grade 2</option>
                            <option value="Grade 3" <?php echo getFormValue('previous_grade_level') === 'Grade 3' ? 'selected' : ''; ?>>Grade 3</option>
                            <option value="Grade 4" <?php echo getFormValue('previous_grade_level') === 'Grade 4' ? 'selected' : ''; ?>>Grade 4</option>
                            <option value="Grade 5" <?php echo getFormValue('previous_grade_level') === 'Grade 5' ? 'selected' : ''; ?>>Grade 5</option>
                            <option value="Grade 6" <?php echo getFormValue('previous_grade_level') === 'Grade 6' ? 'selected' : ''; ?>>Grade 6</option>
                            <option value="Grade 7" <?php echo getFormValue('previous_grade_level') === 'Grade 7' ? 'selected' : ''; ?>>Grade 7</option>
                            <option value="Grade 8" <?php echo getFormValue('previous_grade_level') === 'Grade 8' ? 'selected' : ''; ?>>Grade 8</option>
                            <option value="Grade 9" <?php echo getFormValue('previous_grade_level') === 'Grade 9' ? 'selected' : ''; ?>>Grade 9</option>
                            <option value="Grade 10" <?php echo getFormValue('previous_grade_level') === 'Grade 10' ? 'selected' : ''; ?>>Grade 10</option>
                            <option value="Grade 11" <?php echo getFormValue('previous_grade_level') === 'Grade 11' ? 'selected' : ''; ?>>Grade 11</option>
                            <option value="Grade 12" <?php echo getFormValue('previous_grade_level') === 'Grade 12' ? 'selected' : ''; ?>>Grade 12</option>
                        </select>
                    </div>

                    <!-- School Year Completed -->
                    <div class="col-md-6 mb-3">
                        <label for="previous_school_year" class="form-label">School Year Completed</label>
                        <input type="text" class="form-control" id="previous_school_year" name="previous_school_year" 
                               value="<?php echo htmlspecialchars(getFormValue('previous_school_year')); ?>"
                               placeholder="e.g., 2024-2025">
                    </div>
                </div>
            <?php else: ?>
                <!-- New/Returning Student - Skip this step -->
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> Not Applicable</h5>
                    <p class="mb-0">
                        This section is only required for <strong>transfer students</strong>. 
                        <?php if ($enrollmentType === 'returning'): ?>
                            Your previous school information is already on file.
                        <?php endif; ?>
                        Click <strong>Next</strong> to continue.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Make previous school fields optional for non-transfer students
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentType = '<?php echo $enrollmentType; ?>';
    const previousSchoolName = document.getElementById('previous_school_name');
    
    if (enrollmentType !== 'transfer' && previousSchoolName) {
        previousSchoolName.required = false;
    }
});
</script>
