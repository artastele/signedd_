<!-- Step 2: Current & Permanent Address -->
<div class="form-step" id="step-2">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-geo-alt-fill"></i> Step 2: Address Information
            </h4>
            
            <!-- CURRENT ADDRESS -->
            <h5 class="text-secondary mb-3">Current Address</h5>
            
            <div class="row">
                <!-- House No/Street -->
                <div class="col-md-12 mb-3">
                    <label for="current_house_no" class="form-label">House No. / Street / Sitio / Purok</label>
                    <input type="text" class="form-control" id="current_house_no" name="current_house_no" 
                           value="<?php echo htmlspecialchars(getFormValue('current_house_no')); ?>"
                           placeholder="e.g., 123 Main Street, Purok 5">
                </div>
            </div>

            <div class="row">
                <!-- Province -->
                <div class="col-md-4 mb-3">
                    <label for="current_province" class="form-label">Province</label>
                    <select class="form-select" id="current_province" name="current_province">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <!-- City/Municipality -->
                <div class="col-md-4 mb-3">
                    <label for="current_city" class="form-label">City / Municipality</label>
                    <select class="form-select" id="current_city" name="current_city">
                        <option value="">Select province first</option>
                    </select>
                </div>

                <!-- Barangay -->
                <div class="col-md-4 mb-3">
                    <label for="current_barangay" class="form-label">Barangay</label>
                    <select class="form-select" id="current_barangay" name="current_barangay">
                        <option value="">Select city first</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Zip Code -->
                <div class="col-md-12 mb-3">
                    <label for="current_zip_code" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="current_zip_code" name="current_zip_code" 
                           value="<?php echo htmlspecialchars(getFormValue('current_zip_code')); ?>"
                           placeholder="e.g., 8000" maxlength="4">
                </div>
            </div>

            <hr class="my-4">

            <!-- PERMANENT ADDRESS -->
            <h5 class="text-secondary mb-3">Permanent Address</h5>
            
            <!-- Same as Current Address Checkbox -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="same_as_current_address" 
                       name="same_as_current_address" value="1"
                       <?php echo isChecked('same_as_current_address') ? 'checked' : ''; ?>
                       onchange="togglePermanentAddress()">
                <label class="form-check-label" for="same_as_current_address">
                    <strong>Same as Current Address</strong>
                </label>
            </div>

            <!-- Permanent Address Fields (hidden if same as current) -->
            <div id="permanent_address_fields" style="display: <?php echo isChecked('same_as_current_address') ? 'none' : 'block'; ?>">
                <div class="row">
                    <!-- House No/Street -->
                    <div class="col-md-12 mb-3">
                        <label for="permanent_house_no" class="form-label">House No. / Street / Sitio / Purok</label>
                        <input type="text" class="form-control" id="permanent_house_no" name="permanent_house_no" 
                               value="<?php echo htmlspecialchars(getFormValue('permanent_house_no')); ?>"
                               placeholder="e.g., 456 Secondary Street, Purok 3">
                    </div>
                </div>

                <div class="row">
                    <!-- Province -->
                    <div class="col-md-4 mb-3">
                        <label for="permanent_province" class="form-label">Province</label>
                        <select class="form-select" id="permanent_province" name="permanent_province">
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <!-- City/Municipality -->
                    <div class="col-md-4 mb-3">
                        <label for="permanent_city" class="form-label">City / Municipality</label>
                        <select class="form-select" id="permanent_city" name="permanent_city">
                            <option value="">Select province first</option>
                        </select>
                    </div>

                    <!-- Barangay -->
                    <div class="col-md-4 mb-3">
                        <label for="permanent_barangay" class="form-label">Barangay</label>
                        <select class="form-select" id="permanent_barangay" name="permanent_barangay">
                            <option value="">Select city first</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Zip Code -->
                    <div class="col-md-12 mb-3">
                        <label for="permanent_zip_code" class="form-label">Zip Code</label>
                        <input type="text" class="form-control" id="permanent_zip_code" name="permanent_zip_code" 
                               value="<?php echo htmlspecialchars(getFormValue('permanent_zip_code')); ?>"
                               placeholder="e.g., 8000" maxlength="4">
                    </div>
                </div>
            </div>

            <script>
            // Toggle permanent address fields
            function togglePermanentAddress() {
                const checkbox = document.getElementById('same_as_current_address');
                const fields = document.getElementById('permanent_address_fields');
                fields.style.display = checkbox.checked ? 'none' : 'block';
            }

            // Pre-fill current address if data exists
            document.addEventListener('DOMContentLoaded', function() {
                const currentProvince = '<?php echo htmlspecialchars(getFormValue('current_province')); ?>';
                const currentCity = '<?php echo htmlspecialchars(getFormValue('current_city')); ?>';
                const currentBarangay = '<?php echo htmlspecialchars(getFormValue('current_barangay')); ?>';
                
                if (currentProvince) {
                    setTimeout(() => {
                        document.getElementById('current_province').value = currentProvince;
                        document.getElementById('current_province').dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            if (currentCity) {
                                document.getElementById('current_city').value = currentCity;
                                document.getElementById('current_city').dispatchEvent(new Event('change'));
                                
                                setTimeout(() => {
                                    if (currentBarangay) {
                                        document.getElementById('current_barangay').value = currentBarangay;
                                    }
                                }, 500);
                            }
                        }, 500);
                    }, 1000);
                }

                // Pre-fill permanent address if data exists and not same as current
                const permanentProvince = '<?php echo htmlspecialchars(getFormValue('permanent_province')); ?>';
                const permanentCity = '<?php echo htmlspecialchars(getFormValue('permanent_city')); ?>';
                const permanentBarangay = '<?php echo htmlspecialchars(getFormValue('permanent_barangay')); ?>';
                
                if (permanentProvince && !document.getElementById('same_as_current_address').checked) {
                    setTimeout(() => {
                        document.getElementById('permanent_province').value = permanentProvince;
                        document.getElementById('permanent_province').dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            if (permanentCity) {
                                document.getElementById('permanent_city').value = permanentCity;
                                document.getElementById('permanent_city').dispatchEvent(new Event('change'));
                                
                                setTimeout(() => {
                                    if (permanentBarangay) {
                                        document.getElementById('permanent_barangay').value = permanentBarangay;
                                    }
                                }, 500);
                            }
                        }, 500);
                    }, 1500);
                }
            });
            </script>
        </div>
    </div>
</div>
