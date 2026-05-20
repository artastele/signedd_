<!-- Step 1: Learner Information (Document Style) -->
<div class="form-step" id="step-1">
    <div class="beef-document">
        <!-- Document Header -->
        <div class="document-header">
            <p>Republic of the Philippines</p>
            <p>Department of Education</p>
            <h1>Basic Education Enrollment Form (BEEF)</h1>
            <h2>Special Education (SPED) Program</h2>
            <p>School Year <?php echo date('Y') . '-' . (date('Y') + 1); ?></p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            PART I: LEARNER INFORMATION (Page 1 of 7)
        </div>

        <!-- Learner Reference Number -->
        <div class="form-row">
            <div class="form-field half-width">
                <label class="form-label">Learner Reference Number (LRN)</label>
                <input type="text" class="form-control" id="lrn" name="lrn" 
                       value="<?php echo htmlspecialchars(getFormValue('lrn')); ?>"
                       placeholder="12-digit LRN" maxlength="12">
                <div class="form-text">Leave blank if not yet assigned</div>
            </div>
        </div>

        <!-- Name Section -->
        <div class="section-header">A. LEARNER'S NAME</div>
        
        <div class="form-row">
            <div class="form-field quarter-width">
                <label class="form-label">LAST NAME <span class="required">*</span></label>
                <input type="text" class="form-control" id="last_name" name="last_name" 
                       value="<?php echo htmlspecialchars(getFormValue('last_name')); ?>"
                       required style="text-transform: uppercase;">
            </div>

            <div class="form-field quarter-width">
                <label class="form-label">FIRST NAME <span class="required">*</span></label>
                <input type="text" class="form-control" id="first_name" name="first_name" 
                       value="<?php echo htmlspecialchars(getFormValue('first_name')); ?>"
                       required style="text-transform: uppercase;">
            </div>

            <div class="form-field quarter-width">
                <label class="form-label">MIDDLE NAME</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" 
                       value="<?php echo htmlspecialchars(getFormValue('middle_name')); ?>"
                       style="text-transform: uppercase;">
            </div>

            <div class="form-field quarter-width">
                <label class="form-label">EXTENSION NAME</label>
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
        <div class="section-header">B. BIRTH INFORMATION</div>
        
        <div class="form-row">
            <div class="form-field third-width">
                <label class="form-label">DATE OF BIRTH <span class="required">*</span></label>
                <input type="date" class="form-control" id="birth_date" name="birth_date" 
                       value="<?php echo htmlspecialchars(getFormValue('birth_date')); ?>"
                       onchange="calculateAge()"
                       required>
            </div>

            <div class="form-field third-width">
                <label class="form-label">SEX <span class="required">*</span></label>
                <select class="form-select" id="sex" name="sex" required>
                    <option value="">-- Select --</option>
                    <option value="Male" <?php echo getFormValue('sex') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo getFormValue('sex') === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>

            <div class="form-field third-width">
                <label class="form-label">AGE</label>
                <input type="number" class="form-control" id="age" name="age" 
                       value="<?php echo htmlspecialchars(getFormValue('age')); ?>"
                       min="0" max="100" readonly style="background: #f5f5f5;">
                <div class="form-text">Auto-calculated</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field full-width">
                <label class="form-label">PLACE OF BIRTH (City/Municipality, Province) <span class="required">*</span></label>
                <input type="text" class="form-control" id="birth_place" name="birth_place" 
                       value="<?php echo htmlspecialchars(getFormValue('birth_place')); ?>"
                       placeholder="e.g., Cebu City, Cebu"
                       required>
            </div>
        </div>

        <!-- Language -->
        <div class="section-header">C. LANGUAGE</div>
        
        <div class="form-row">
            <div class="form-field half-width">
                <label class="form-label">MOTHER TONGUE</label>
                <input type="text" class="form-control" id="mother_tongue" name="mother_tongue" 
                       value="<?php echo htmlspecialchars(getFormValue('mother_tongue')); ?>"
                       placeholder="e.g., Cebuano, Tagalog, Ilocano">
            </div>
        </div>

        <!-- Indigenous Peoples -->
        <div class="section-header">D. INDIGENOUS PEOPLES (IP)</div>
        
        <div class="checkbox-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_indigenous_people" 
                       name="is_indigenous_people" value="1"
                       <?php echo isChecked('is_indigenous_people') ? 'checked' : ''; ?>
                       onchange="document.getElementById('indigenous_group_field').style.display = this.checked ? 'block' : 'none'">
                <label class="form-check-label" for="is_indigenous_people">
                    Learner belongs to Indigenous Peoples (IP) Community
                </label>
            </div>
            <div id="indigenous_group_field" style="display: <?php echo isChecked('is_indigenous_people') ? 'block' : 'none'; ?>; margin-top: 10px;">
                <label class="form-label">Specify Ethnic Group:</label>
                <input type="text" class="form-control" id="indigenous_group" name="indigenous_group" 
                       value="<?php echo htmlspecialchars(getFormValue('indigenous_group')); ?>"
                       placeholder="e.g., Lumad, Aeta, Igorot">
            </div>
        </div>

        <!-- 4Ps Beneficiary -->
        <div class="section-header">E. PANTAWID PAMILYANG PILIPINO PROGRAM (4Ps)</div>
        
        <div class="checkbox-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_4ps_beneficiary" 
                       name="is_4ps_beneficiary" value="1"
                       <?php echo isChecked('is_4ps_beneficiary') ? 'checked' : ''; ?>
                       onchange="document.getElementById('fourps_field').style.display = this.checked ? 'block' : 'none'">
                <label class="form-check-label" for="is_4ps_beneficiary">
                    Learner is a 4Ps Beneficiary
                </label>
            </div>
            <div id="fourps_field" style="display: <?php echo isChecked('is_4ps_beneficiary') ? 'block' : 'none'; ?>; margin-top: 10px;">
                <label class="form-label">4Ps Household ID Number:</label>
                <input type="text" class="form-control" id="fourps_household_id" name="fourps_household_id" 
                       value="<?php echo htmlspecialchars(getFormValue('fourps_household_id')); ?>">
            </div>
        </div>

        <!-- Disability Information -->
        <div class="section-header">F. DISABILITY/SPECIAL NEEDS</div>
        
        <div class="checkbox-group">
            <div class="checkbox-group-title">Check all that apply:</div>
            <div class="checkbox-row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_visual" 
                           name="disability_visual" value="1"
                           <?php echo isChecked('disability_visual') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_visual">
                        Visual Impairment
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_hearing" 
                           name="disability_hearing" value="1"
                           <?php echo isChecked('disability_hearing') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_hearing">
                        Hearing Impairment
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_learning" 
                           name="disability_learning" value="1"
                           <?php echo isChecked('disability_learning') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_learning">
                        Learning Disability
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_speech" 
                           name="disability_speech" value="1"
                           <?php echo isChecked('disability_speech') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_speech">
                        Speech/Language Impairment
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_intellectual" 
                           name="disability_intellectual" value="1"
                           <?php echo isChecked('disability_intellectual') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_intellectual">
                        Intellectual Disability
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_physical" 
                           name="disability_physical" value="1"
                           <?php echo isChecked('disability_physical') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_physical">
                        Physical Disability
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_emotional" 
                           name="disability_emotional" value="1"
                           <?php echo isChecked('disability_emotional') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_emotional">
                        Emotional-Behavioral Disorder
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_chronic_illness" 
                           name="disability_chronic_illness" value="1"
                           <?php echo isChecked('disability_chronic_illness') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disability_chronic_illness">
                        Chronic Illness
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="disability_others" 
                           name="disability_others" value="1"
                           <?php echo isChecked('disability_others') ? 'checked' : ''; ?>
                           onchange="document.getElementById('disability_others_field').style.display = this.checked ? 'block' : 'none'">
                    <label class="form-check-label" for="disability_others">
                        Others (Specify)
                    </label>
                </div>
            </div>
            <div id="disability_others_field" style="display: <?php echo isChecked('disability_others') ? 'block' : 'none'; ?>; margin-top: 10px;">
                <label class="form-label">Specify Other Disability:</label>
                <input type="text" class="form-control" id="disability_others_specify" 
                       name="disability_others_specify" 
                       value="<?php echo htmlspecialchars(getFormValue('disability_others_specify')); ?>">
            </div>
        </div>
    </div>
</div>
