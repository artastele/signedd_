<!-- Step 6: Learning Modality -->
<div class="form-step" id="step-6">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-laptop"></i> Step 6: Learning Modality
            </h4>
            
            <div class="alert alert-info">
                <strong><i class="bi bi-info-circle"></i> Select all learning modalities that apply</strong>
                <p class="mb-0">You can choose multiple options based on your preference and availability.</p>
            </div>

            <!-- Learning Modality Options -->
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Available Learning Modalities</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_modular_print" 
                                       name="modality_modular_print" value="1"
                                       <?php echo isChecked('modality_modular_print') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_modular_print">
                                    <strong>Modular (Print)</strong>
                                    <br><small class="text-muted">Printed learning modules delivered to home</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_modular_digital" 
                                       name="modality_modular_digital" value="1"
                                       <?php echo isChecked('modality_modular_digital') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_modular_digital">
                                    <strong>Modular (Digital)</strong>
                                    <br><small class="text-muted">Digital learning modules via email/USB</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_online" 
                                       name="modality_online" value="1"
                                       <?php echo isChecked('modality_online') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_online">
                                    <strong>Online</strong>
                                    <br><small class="text-muted">Internet-based learning via video conferencing</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_educational_tv" 
                                       name="modality_educational_tv" value="1"
                                       <?php echo isChecked('modality_educational_tv') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_educational_tv">
                                    <strong>Educational TV</strong>
                                    <br><small class="text-muted">TV-based instruction via DepEd TV</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_radio" 
                                       name="modality_radio" value="1"
                                       <?php echo isChecked('modality_radio') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_radio">
                                    <strong>Radio-Based Instruction</strong>
                                    <br><small class="text-muted">Learning via radio broadcasts</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_blended" 
                                       name="modality_blended" value="1"
                                       <?php echo isChecked('modality_blended') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_blended">
                                    <strong>Blended Learning</strong>
                                    <br><small class="text-muted">Combination of online and offline methods</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_face_to_face" 
                                       name="modality_face_to_face" value="1"
                                       <?php echo isChecked('modality_face_to_face') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_face_to_face">
                                    <strong>Face-to-Face</strong>
                                    <br><small class="text-muted">In-person classroom instruction</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferred Distance Learning Modality -->
            <div class="card bg-light">
                <div class="card-body">
                    <label for="preferred_distance_modality" class="form-label">
                        <strong>Preferred Distance Learning Modality</strong> (If face-to-face is not available)
                    </label>
                    <select class="form-select" id="preferred_distance_modality" name="preferred_distance_modality">
                        <option value="">-- Select Preferred Modality --</option>
                        <option value="Modular (Print)" <?php echo getFormValue('preferred_distance_modality') === 'Modular (Print)' ? 'selected' : ''; ?>>Modular (Print)</option>
                        <option value="Modular (Digital)" <?php echo getFormValue('preferred_distance_modality') === 'Modular (Digital)' ? 'selected' : ''; ?>>Modular (Digital)</option>
                        <option value="Online" <?php echo getFormValue('preferred_distance_modality') === 'Online' ? 'selected' : ''; ?>>Online</option>
                        <option value="Educational TV" <?php echo getFormValue('preferred_distance_modality') === 'Educational TV' ? 'selected' : ''; ?>>Educational TV</option>
                        <option value="Radio-Based Instruction" <?php echo getFormValue('preferred_distance_modality') === 'Radio-Based Instruction' ? 'selected' : ''; ?>>Radio-Based Instruction</option>
                        <option value="Blended" <?php echo getFormValue('preferred_distance_modality') === 'Blended' ? 'selected' : ''; ?>>Blended</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
