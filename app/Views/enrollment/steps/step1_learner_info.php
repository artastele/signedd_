<!-- Step 1: Learner Information (Simple Card Style) -->
<div class="form-step" id="step-1">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-person-fill"></i> Step 1: Learner Information
            </h4>
            
            <!-- Learner Reference Number -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="lrn" class="form-label">Learner Reference Number (LRN)</label>
                    <input type="text" class="form-control" id="lrn" name="lrn" 
                           value="<?php echo htmlspecialchars(getFormValue('lrn')); ?>"
                           placeholder="12-digit LRN" maxlength="12">
                    <div class="form-text">Leave blank if not yet assigned</div>
                </div>
            </div>

            <!-- Name Section -->
            <h5 class="text-secondary mb-3">Learner's Name</h5>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars(getFormValue('last_name')); ?>"
                           required style="text-transform: uppercase;">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars(getFormValue('first_name')); ?>"
                           required style="text-transform: uppercase;">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" 
                           value="<?php echo htmlspecialchars(getFormValue('middle_name')); ?>"
                           style="text-transform: uppercase;">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="extension_name" class="form-label">Extension Name</label>
                    <select class="form-select" id="extension_name" name="extension_name">
                        <option value="">None</option>
                        <option value="Jr." <?php echo getFormValue('extension_name') === 'Jr.' ? 'selected' : ''; ?>>Jr.</option>
                        <option value="Sr." <?php echo getFormValue('extension_name') === 'Sr.' ? 'selected' : ''; ?>>Sr.</option>
                        <option value="II" <?php echo getFormValue('extension_name') === 'II' ? 'selected' : ''; ?>>II</option>
                        <option value="III" <?php echo getFormValue('extension_name') === 'III' ? 'selected' : ''; ?>>III</option>
                        <option value="IV" <?php echo getFormValue('extension_name') === 'IV' ? 'selected' : ''; ?>>IV</option>
                    </select>
                </div>
            </div>

            <!-- Birth Information -->
            <h5 class="text-secondary mb-3 mt-4">Birth Information</h5>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="birth_date" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" 
                           value="<?php echo htmlspecialchars(getFormValue('birth_date')); ?>"
                           onchange="calculateAge()"
                           required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="">-- Select --</option>
                        <option value="Male" <?php echo getFormValue('sex') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo getFormValue('sex') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age" 
                           value="<?php echo htmlspecialchars(getFormValue('age')); ?>"
                           min="0" max="100" readonly style="background: #f5f5f5;">
                    <div class="form-text">Auto-calculated</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="birth_place" class="form-label">Place of Birth (City/Municipality, Province) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="birth_place" name="birth_place" 
                           value="<?php echo htmlspecialchars(getFormValue('birth_place')); ?>"
                           placeholder="e.g., Cebu City, Cebu" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="mother_tongue" class="form-label">Mother Tongue</label>
                    <input type="text" class="form-control" id="mother_tongue" name="mother_tongue" 
                           value="<?php echo htmlspecialchars(getFormValue('mother_tongue')); ?>"
                           placeholder="e.g., Cebuano, Tagalog, English">
                </div>
            </div>

            <!-- Indigenous People -->
            <h5 class="text-secondary mb-3 mt-4">Indigenous People</h5>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_indigenous_people" 
                       name="is_indigenous_people" value="1"
                       <?php echo isChecked('is_indigenous_people') ? 'checked' : ''; ?>
                       onchange="toggleIndigenousGroup()">
                <label class="form-check-label" for="is_indigenous_people">
                    Learner belongs to Indigenous People (IP) group
                </label>
            </div>

            <div id="indigenous_group_field" style="display: <?php echo isChecked('is_indigenous_people') ? 'block' : 'none'; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="indigenous_group" class="form-label">Indigenous Group Name</label>
                        <input type="text" class="form-control" id="indigenous_group" name="indigenous_group" 
                               value="<?php echo htmlspecialchars(getFormValue('indigenous_group')); ?>"
                               placeholder="e.g., Lumad, Manobo, Badjao">
                    </div>
                </div>
            </div>

            <!-- 4Ps Beneficiary -->
            <h5 class="text-secondary mb-3 mt-4">4Ps Beneficiary</h5>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_4ps_beneficiary" 
                       name="is_4ps_beneficiary" value="1"
                       <?php echo isChecked('is_4ps_beneficiary') ? 'checked' : ''; ?>
                       onchange="toggle4PsHousehold()">
                <label class="form-check-label" for="is_4ps_beneficiary">
                    Learner is a 4Ps (Pantawid Pamilyang Pilipino Program) beneficiary
                </label>
            </div>

            <div id="fourps_household_field" style="display: <?php echo isChecked('is_4ps_beneficiary') ? 'block' : 'none'; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fourps_household_id" class="form-label">4Ps Household ID Number</label>
                        <input type="text" class="form-control" id="fourps_household_id" name="fourps_household_id" 
                               value="<?php echo htmlspecialchars(getFormValue('fourps_household_id')); ?>"
                               placeholder="Enter 4Ps Household ID">
                    </div>
                </div>
            </div>

            <!-- Disabilities -->
            <h5 class="text-secondary mb-3 mt-4">Disabilities / Special Needs</h5>
            
            <div class="alert alert-info">
                <small><i class="bi bi-info-circle"></i> Check all that apply. This information helps us provide appropriate support.</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_visual" 
                               name="disability_visual" value="1"
                               <?php echo isChecked('disability_visual') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_visual">
                            Visual Impairment (Blind/Low Vision)
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_hearing" 
                               name="disability_hearing" value="1"
                               <?php echo isChecked('disability_hearing') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_hearing">
                            Hearing Impairment (Deaf/Hard of Hearing)
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_learning" 
                               name="disability_learning" value="1"
                               <?php echo isChecked('disability_learning') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_learning">
                            Learning Disability
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_speech" 
                               name="disability_speech" value="1"
                               <?php echo isChecked('disability_speech') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_speech">
                            Speech/Language Impairment
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_intellectual" 
                               name="disability_intellectual" value="1"
                               <?php echo isChecked('disability_intellectual') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_intellectual">
                            Intellectual Disability
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_physical" 
                               name="disability_physical" value="1"
                               <?php echo isChecked('disability_physical') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_physical">
                            Physical Disability (Orthopedic)
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_emotional" 
                               name="disability_emotional" value="1"
                               <?php echo isChecked('disability_emotional') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_emotional">
                            Emotional/Behavioral Disorder
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_chronic_illness" 
                               name="disability_chronic_illness" value="1"
                               <?php echo isChecked('disability_chronic_illness') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disability_chronic_illness">
                            Chronic Illness
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disability_others" 
                               name="disability_others" value="1"
                               <?php echo isChecked('disability_others') ? 'checked' : ''; ?>
                               onchange="toggleOthersSpecify()">
                        <label class="form-check-label" for="disability_others">
                            Others (Please specify)
                        </label>
                    </div>
                </div>
            </div>

            <div id="disability_others_field" style="display: <?php echo isChecked('disability_others') ? 'block' : 'none'; ?>" class="mt-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="disability_others_specify" class="form-label">Please specify other disability</label>
                        <input type="text" class="form-control" id="disability_others_specify" name="disability_others_specify" 
                               value="<?php echo htmlspecialchars(getFormValue('disability_others_specify')); ?>"
                               placeholder="Describe the disability">
                    </div>
                </div>
            </div>

            <script>
            // Calculate age from birth date
            function calculateAge() {
                const birthDate = document.getElementById('birth_date').value;
                if (birthDate) {
                    const today = new Date();
                    const birth = new Date(birthDate);
                    let age = today.getFullYear() - birth.getFullYear();
                    const monthDiff = today.getMonth() - birth.getMonth();
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                        age--;
                    }
                    document.getElementById('age').value = age;
                }
            }

            // Toggle indigenous group field
            function toggleIndigenousGroup() {
                const checkbox = document.getElementById('is_indigenous_people');
                const field = document.getElementById('indigenous_group_field');
                field.style.display = checkbox.checked ? 'block' : 'none';
            }

            // Toggle 4Ps household field
            function toggle4PsHousehold() {
                const checkbox = document.getElementById('is_4ps_beneficiary');
                const field = document.getElementById('fourps_household_field');
                field.style.display = checkbox.checked ? 'block' : 'none';
            }

            // Toggle others specify field
            function toggleOthersSpecify() {
                const checkbox = document.getElementById('disability_others');
                const field = document.getElementById('disability_others_field');
                field.style.display = checkbox.checked ? 'block' : 'none';
            }
            </script>
        </div>
    </div>
</div>
