<!-- Step 3: Parent/Guardian Information -->
<div class="form-step" id="step-3">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-people-fill"></i> Step 3: Parent / Guardian Information
            </h4>
            
            <!-- Father's Information -->
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Father's Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="father_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="father_last_name" name="father_last_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('father_last_name')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="father_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="father_first_name" name="father_first_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('father_first_name')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="father_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="father_middle_name" name="father_middle_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('father_middle_name')); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="father_contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="father_contact_number" name="father_contact_number" 
                                   value="<?php echo htmlspecialchars(getFormValue('father_contact_number')); ?>"
                                   placeholder="e.g., 09123456789">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mother's Information -->
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Mother's Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="mother_maiden_last_name" class="form-label">Maiden Last Name</label>
                            <input type="text" class="form-control" id="mother_maiden_last_name" name="mother_maiden_last_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('mother_maiden_last_name')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mother_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="mother_first_name" name="mother_first_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('mother_first_name')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mother_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="mother_middle_name" name="mother_middle_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('mother_middle_name')); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mother_contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="mother_contact_number" name="mother_contact_number" 
                                   value="<?php echo htmlspecialchars(getFormValue('mother_contact_number')); ?>"
                                   placeholder="e.g., 09123456789">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guardian's Information -->
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0">Guardian's Information <small class="text-muted">(If not parent)</small></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="guardian_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="guardian_last_name" name="guardian_last_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('guardian_last_name')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guardian_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="guardian_first_name" name="guardian_first_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('guardian_first_name')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="guardian_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="guardian_middle_name" name="guardian_middle_name" 
                                   value="<?php echo htmlspecialchars(getFormValue('guardian_middle_name')); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="guardian_contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="guardian_contact_number" name="guardian_contact_number" 
                                   value="<?php echo htmlspecialchars(getFormValue('guardian_contact_number')); ?>"
                                   placeholder="e.g., 09123456789">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
