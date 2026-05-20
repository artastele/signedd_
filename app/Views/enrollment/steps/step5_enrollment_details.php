<!-- Step 5: Enrollment Details -->
<div class="form-step" id="step-5">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-clipboard-check"></i> Step 5: Enrollment Details
            </h4>
            
            <div class="row">
                <!-- School Year -->
                <div class="col-md-6 mb-3">
                    <label for="school_year" class="form-label">School Year <span class="text-danger">*</span></label>
                    <select class="form-select" id="school_year" name="school_year" required>
                        <?php
                        // Generate school years (current and next 2 years)
                        $currentYear = date('Y');
                        $defaultSY = $currentYear . '-' . ($currentYear + 1);
                        
                        for ($i = 0; $i <= 2; $i++) {
                            $startYear = $currentYear + $i;
                            $endYear = $startYear + 1;
                            $sy = $startYear . '-' . $endYear;
                            $selected = (getFormValue('school_year', $defaultSY) === $sy) ? 'selected' : '';
                            echo "<option value=\"$sy\" $selected>$sy</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">Select the school year for this enrollment</div>
                </div>
                
                <!-- Grade Level to Enroll -->
                <div class="col-md-6 mb-3">
                    <label for="grade_level_to_enroll" class="form-label">Grade Level to Enroll <span class="text-danger">*</span></label>
                    <select class="form-select" id="grade_level_to_enroll" name="grade_level_to_enroll" required>
                        <option value="">-- Select Grade Level --</option>
                        <option value="Kinder" <?php echo getFormValue('grade_level_to_enroll') === 'Kinder' ? 'selected' : ''; ?>>Kinder</option>
                        <option value="Grade 1" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 1' ? 'selected' : ''; ?>>Grade 1</option>
                        <option value="Grade 2" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 2' ? 'selected' : ''; ?>>Grade 2</option>
                        <option value="Grade 3" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 3' ? 'selected' : ''; ?>>Grade 3</option>
                        <option value="Grade 4" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 4' ? 'selected' : ''; ?>>Grade 4</option>
                        <option value="Grade 5" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 5' ? 'selected' : ''; ?>>Grade 5</option>
                        <option value="Grade 6" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 6' ? 'selected' : ''; ?>>Grade 6</option>
                        <option value="Grade 7" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 7' ? 'selected' : ''; ?>>Grade 7</option>
                        <option value="Grade 8" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 8' ? 'selected' : ''; ?>>Grade 8</option>
                        <option value="Grade 9" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 9' ? 'selected' : ''; ?>>Grade 9</option>
                        <option value="Grade 10" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 10' ? 'selected' : ''; ?>>Grade 10</option>
                        <option value="Grade 11" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 11' ? 'selected' : ''; ?>>Grade 11</option>
                        <option value="Grade 12" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 12' ? 'selected' : ''; ?>>Grade 12</option>
                        <option value="SPED Program" <?php echo getFormValue('grade_level_to_enroll') === 'SPED Program' ? 'selected' : ''; ?>>SPED Program</option>
                    </select>
                </div>
            </div>

            <!-- Additional Options -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_balik_aral" 
                                       name="is_balik_aral" value="1"
                                       <?php echo isChecked('is_balik_aral') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_balik_aral">
                                    <strong>Balik-Aral</strong> (Returning to School)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PEPT Passer -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_pept_passer" 
                               name="is_pept_passer" value="1"
                               <?php echo isChecked('is_pept_passer') ? 'checked' : ''; ?>
                               onchange="document.getElementById('pept_rating_field').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label" for="is_pept_passer">
                            <strong>PEPT Passer</strong> (Philippine Educational Placement Test)
                        </label>
                    </div>
                    <div id="pept_rating_field" style="display: <?php echo isChecked('is_pept_passer') ? 'block' : 'none'; ?>">
                        <label for="pept_rating" class="form-label">PEPT Rating</label>
                        <input type="text" class="form-control" id="pept_rating" name="pept_rating" 
                               value="<?php echo htmlspecialchars(getFormValue('pept_rating')); ?>"
                               placeholder="e.g., 85%">
                    </div>
                </div>
            </div>

            <!-- ALS Passer -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_als_passer" 
                               name="is_als_passer" value="1"
                               <?php echo isChecked('is_als_passer') ? 'checked' : ''; ?>
                               onchange="document.getElementById('als_rating_field').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label" for="is_als_passer">
                            <strong>ALS A&E Passer</strong> (Alternative Learning System)
                        </label>
                    </div>
                    <div id="als_rating_field" style="display: <?php echo isChecked('is_als_passer') ? 'block' : 'none'; ?>">
                        <label for="als_rating" class="form-label">ALS Rating</label>
                        <input type="text" class="form-control" id="als_rating" name="als_rating" 
                               value="<?php echo htmlspecialchars(getFormValue('als_rating')); ?>"
                               placeholder="e.g., 85%">
                    </div>
                </div>
            </div>

            <!-- Senior High School Details -->
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Senior High School Details <small>(If applicable)</small></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- SHS Track -->
                        <div class="col-md-4 mb-3">
                            <label for="shs_track" class="form-label">Track</label>
                            <select class="form-select" id="shs_track" name="shs_track">
                                <option value="">-- Select Track --</option>
                                <option value="Academic" <?php echo getFormValue('shs_track') === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                                <option value="TVL" <?php echo getFormValue('shs_track') === 'TVL' ? 'selected' : ''; ?>>Technical-Vocational-Livelihood (TVL)</option>
                                <option value="Sports" <?php echo getFormValue('shs_track') === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                                <option value="Arts & Design" <?php echo getFormValue('shs_track') === 'Arts & Design' ? 'selected' : ''; ?>>Arts & Design</option>
                            </select>
                        </div>

                        <!-- SHS Strand -->
                        <div class="col-md-4 mb-3">
                            <label for="shs_strand" class="form-label">Strand</label>
                            <input type="text" class="form-control" id="shs_strand" name="shs_strand" 
                                   value="<?php echo htmlspecialchars(getFormValue('shs_strand')); ?>"
                                   placeholder="e.g., STEM, ABM, HUMSS">
                        </div>

                        <!-- SHS Semester -->
                        <div class="col-md-4 mb-3">
                            <label for="shs_semester" class="form-label">Semester</label>
                            <select class="form-select" id="shs_semester" name="shs_semester">
                                <option value="">-- Select --</option>
                                <option value="1st" <?php echo getFormValue('shs_semester') === '1st' ? 'selected' : ''; ?>>1st Semester</option>
                                <option value="2nd" <?php echo getFormValue('shs_semester') === '2nd' ? 'selected' : ''; ?>>2nd Semester</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
