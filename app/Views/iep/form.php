<?php
// DO NOT ALTER WITHOUT APPROVAL -- Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS -- IEP Form (Individualized Education Plan)

$pageTitle = 'IEP Form - SPED LMS';
$statusLabels = ['draft'=>'Draft','signing'=>'Signing','signed'=>'Signed','locked'=>'Locked'];
$statusColors = ['draft'=>'#6c757d','signing'=>'#ffc107','signed'=>'#3b6d11','locked'=>'#a01422'];
$currentStatus = $iep['status'] ?? 'draft';
$statusLabel   = $statusLabels[$currentStatus] ?? 'Draft';
$statusColor   = $statusColors[$currentStatus] ?? '#6c757d';
$isLocked      = in_array($currentStatus, ['signed','locked']);
$readOnly      = $readOnly ?? false;
$basePath      = BASE_PATH;

require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<!-- Re-evaluation passed banner -->
<?php if (!empty($iep['re_evaluation_date']) && strtotime($iep['re_evaluation_date']) < time() && !$isLocked): ?>
<div style="background:#a01422;color:white;padding:12px 24px;text-align:center;font-weight:600;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Re-evaluation date has passed (<?php echo date('M d, Y', strtotime($iep['re_evaluation_date'])); ?>).
    <?php if (!$readOnly): ?>
    <span class="ms-3 small">Update this IEP from <strong>View</strong> on the repository — the IEP stays on file as a living document.</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="main-content" id="iepFormContent">

    <!-- ===== SECTION 1: PAGE HEADER ===== -->
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h1 class="mb-1" style="color:#1e4072;">
                <i class="bi bi-file-earmark-medical me-2"></i>Individualized Education Plan
            </h1>
            <p class="mb-1 text-muted">
                <?php echo htmlspecialchars($iep['student_name']); ?>
                &nbsp;&bull;&nbsp; LRN: <strong><?php echo htmlspecialchars($iep['lrn']); ?></strong>
            </p>
            <span class="badge" style="background:#1e4072;font-size:.85rem;">
                <?php echo htmlspecialchars($iep['school_year']); ?>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge fs-6 px-3 py-2" style="background:<?php echo $statusColor; ?>;">
                <?php echo $statusLabel; ?>
            </span>
            <?php if ($isLocked): ?>
                <span class="badge fs-6 px-3 py-2" style="background:#3b6d11;">
                    <i class="bi bi-check-circle-fill me-1"></i>IEP Signed &#10003;
                </span>
            <?php endif; ?>
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:white;"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            <?php if (!$readOnly && !$isLocked): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSaveDraft">
                <i class="bi bi-floppy me-1"></i>Save Draft
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($_SESSION['iep_errors'])): ?>
    <div class="alert alert-danger border-0" style="border-left:4px solid #a01422 !important;">
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following before signing:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($_SESSION['iep_errors'] as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; unset($_SESSION['iep_errors']); ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form id="iepForm">
    <input type="hidden" id="iepId" value="<?php echo $iep['id']; ?>">

    <!-- ===== SECTION 2: HEADER FIELDS (auto-fill) ===== -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-person-fill me-2"></i>Learner Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Name of Learner</label>
                    <input type="text" class="form-control" id="f_student_name"
                           value="<?php echo htmlspecialchars($studentData['student_name'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Age</label>
                    <input type="number" class="form-control" id="f_age"
                           value="<?php echo htmlspecialchars($studentData['age'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">LRN</label>
                    <input type="text" class="form-control" id="f_lrn"
                           value="<?php echo htmlspecialchars($studentData['lrn'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Section</label>
                    <input type="text" class="form-control" id="f_section"
                           value="" placeholder="Enter section"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Name of Teacher</label>
                    <input type="text" class="form-control" id="f_teacher_name"
                           value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">School</label>
                    <input type="text" class="form-control" id="f_school"
                           value="" placeholder="Enter school name"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">School Year</label>
                    <input type="text" class="form-control" id="f_school_year" name="school_year"
                           value="<?php echo htmlspecialchars($iep['school_year'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Grade Level</label>
                    <input type="text" class="form-control" id="f_grade_level"
                           value="<?php echo htmlspecialchars($studentData['grade_level'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                </div>
            </div>
        </div>
    </div>


    <!-- ===== SECTION 3: CORE IEP FIELDS (domain as dropdown from PDSP) ===== -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Core IEP Fields</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Developmental Domain/s / Subject Area</label>
                    <?php
                    // Build domain options — from PDSP or DepEd standard fallback
                    $pdspDomainOptions = !empty($pdspDomains)
                        ? array_unique(array_column($pdspDomains, 'domain_name'))
                        : ['Perceptuo-Cognitive','Psychosocial','Socio-Emotional',
                           'Psychomotor','Daily Living Skills','Communication and Language'];

                    // Saved domains — from iep_domains table (multiple)
                    $savedDomains = !empty($domains) ? array_column($domains, 'domain_name') : [];
                    // Fallback: if iep_core has a single domain saved (old format)
                    if (empty($savedDomains) && !empty($core['developmental_domain'])) {
                        $savedDomains = [$core['developmental_domain']];
                    }
                    ?>
                    <?php if (!$readOnly): ?>
                    <!-- Domain chips container -->
                    <div id="domainChipsContainer" class="d-flex flex-wrap gap-2 mb-2 p-2 rounded"
                         style="min-height:42px;border:1px solid #dee2e6;background:#fff;">
                        <?php foreach ($savedDomains as $dn): ?>
                        <span class="domain-chip badge d-flex align-items-center gap-1 px-3 py-2"
                              data-name="<?php echo htmlspecialchars($dn); ?>"
                              style="background:#1e4072;color:white;font-size:.85rem;border-radius:20px;">
                            <?php echo htmlspecialchars($dn); ?>
                            <button type="button" class="btn-close btn-close-white ms-1"
                                    style="font-size:.55rem;" onclick="removeDomainChip(this)"
                                    title="Remove"></button>
                        </span>
                        <?php endforeach; ?>
                        <?php if (empty($savedDomains)): ?>
                        <span class="text-muted small align-self-center ps-1" id="domainPlaceholder">No domains added yet</span>
                        <?php endif; ?>
                    </div>
                    <!-- Add domain row -->
                    <div class="d-flex gap-2 align-items-center">
                        <select class="form-select" id="domainSelect" style="max-width:320px;">
                            <option value="">-- Select domain --</option>
                            <?php foreach ($pdspDomainOptions as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                            <?php endforeach; ?>
                            <option value="__custom__">Other (type below)</option>
                        </select>
                        <button type="button" class="btn btn-sm" style="background:#1e4072;color:white;white-space:nowrap;"
                                onclick="addDomainChip()">
                            <i class="bi bi-plus-lg me-1"></i>Add Domain
                        </button>
                    </div>
                    <!-- Custom domain input (shown when "Other" selected) -->
                    <div id="customDomainRow" class="d-flex gap-2 mt-2" style="display:none!important;">
                        <input type="text" class="form-control" id="customDomainInput"
                               placeholder="Type custom domain name..." style="max-width:320px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="addCustomDomain()">Add</button>
                    </div>
                    <small class="text-muted mt-1 d-block">Select a domain and click Add. You can add multiple.</small>
                    <?php else: ?>
                    <!-- Read-only: show chips without remove button -->
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($savedDomains as $dn): ?>
                        <span class="badge px-3 py-2" style="background:#1e4072;color:white;font-size:.85rem;border-radius:20px;">
                            <?php echo htmlspecialchars($dn); ?>
                        </span>
                        <?php endforeach; ?>
                        <?php if (empty($savedDomains)): ?>
                        <span class="text-muted small">No domains set</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Date of Re-evaluation <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="f_re_eval" name="re_evaluation_date"
                           value="<?php echo htmlspecialchars($iep['re_evaluation_date'] ?? ''); ?>"
                           <?php echo $readOnly ? 'readonly' : ''; ?>>
                    <small class="text-muted">Required before signing.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Priority Need/s</label>
                    <textarea class="form-control" id="f_priority_needs" name="priority_needs"
                              rows="4" placeholder="Describe the learner's priority needs..."
                              <?php echo $readOnly ? 'readonly' : ''; ?>><?php echo htmlspecialchars($core['priority_needs'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Terminal Objective/s</label>
                    <textarea class="form-control" id="f_terminal_obj" name="terminal_objectives"
                              rows="4" placeholder="State the terminal objectives..."
                              <?php echo $readOnly ? 'readonly' : ''; ?>><?php echo htmlspecialchars($core['terminal_objectives'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== SECTION 4: SPLIT VIEW — STEPS + PDSP/ASSESSMENT PANEL ===== -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Steps <small class="opacity-75 ms-2" style="font-size:.8rem;">Max 10 rows</small></h5>
        </div>
        <div class="card-body p-0">
            <div class="d-flex" id="splitViewContainer">
                <!-- LEFT: Steps Table (60%) -->
                <div style="flex:0 0 60%;min-width:0;overflow-x:auto;padding:16px;" id="stepsPanel">
                    <div class="table-responsive">
                    <table class="table table-bordered mb-2" id="stepsTable" style="min-width:700px;">
                        <thead style="background:#1e4072;color:white;">
                            <tr>
                                <th style="width:40px;">No.</th>
                                <th>Step Objectives</th>
                                <th>Observation</th>
                                <th>Plan of Activities / Lesson Plan</th>
                                <th>Materials</th>
                                <th>Instructional Evaluation</th>
                                <th>Duration of LP</th>
                                <?php if (!$readOnly): ?><th style="width:36px;"></th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="stepsBody">
                        <?php $stepsData = !empty($steps) ? $steps : [[]]; foreach ($stepsData as $i => $step): ?>
                        <tr class="step-row">
                            <td class="text-center fw-bold step-num" style="vertical-align:top;padding-top:10px;"><?php echo $i+1; ?></td>
                            <td><textarea class="form-control form-control-sm auto-expand step-objectives" rows="2" <?php echo $readOnly?'readonly':''; ?>><?php echo htmlspecialchars($step['objectives']??''); ?></textarea></td>
                            <td><textarea class="form-control form-control-sm auto-expand step-observation" rows="2" <?php echo $readOnly?'readonly':''; ?>><?php echo htmlspecialchars($step['observation']??''); ?></textarea></td>
                            <td><textarea class="form-control form-control-sm auto-expand step-activities" rows="2" <?php echo $readOnly?'readonly':''; ?>><?php echo htmlspecialchars($step['activities']??''); ?></textarea></td>
                            <td><textarea class="form-control form-control-sm auto-expand step-materials" rows="2" <?php echo $readOnly?'readonly':''; ?>><?php echo htmlspecialchars($step['materials']??''); ?></textarea></td>
                            <td><textarea class="form-control form-control-sm auto-expand step-evaluation" rows="2" <?php echo $readOnly?'readonly':''; ?>><?php echo htmlspecialchars($step['evaluation']??''); ?></textarea></td>
                            <td><input type="text" class="form-control form-control-sm step-duration" value="<?php echo htmlspecialchars($step['duration_lp']??''); ?>" placeholder="e.g. 30 mins" <?php echo $readOnly?'readonly':''; ?>></td>
                            <?php if (!$readOnly): ?><td style="vertical-align:top;padding-top:8px;"><button type="button" class="btn btn-sm btn-outline-danger remove-step-btn" onclick="removeStep(this)"><i class="bi bi-x-lg"></i></button></td><?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php if (!$readOnly): ?>
                    <button type="button" class="btn btn-sm" style="background:#1e4072;color:white;" id="addStepBtn" onclick="addStep()">
                        <i class="bi bi-plus-lg me-1"></i>Add Step
                    </button>
                    <small class="text-muted ms-2" id="stepCountNote"></small>
                    <?php endif; ?>
                </div>

                <!-- RIGHT: PDSP + Assessment Docs Panel (40%) -->
                <div style="flex:0 0 40%;min-width:0;border-left:3px solid #1e4072;max-height:600px;overflow-y:auto;background:#fff;" id="pdspPanel">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2"
                         style="background:#f5f5f5;border-bottom:1px solid #dee2e6;position:sticky;top:0;z-index:1;">
                        <strong style="color:#1e4072;font-size:.9rem;"><i class="bi bi-clipboard-data me-1"></i>PDSP &amp; Assessment Docs</strong>
                        <div class="d-flex gap-1 align-items-center">
                            <?php if (!empty($pdspRecord['signed_document_path'])): ?>
                            <a href="<?php echo $basePath.'/'.htmlspecialchars($pdspRecord['signed_document_path']); ?>"
                               target="_blank" class="btn btn-sm py-0 px-2" style="background:#a01422;color:white;font-size:.75rem;">
                                <i class="bi bi-file-earmark-pdf me-1"></i>View PDSP Doc
                            </a>
                            <a href="<?php echo $basePath.'/'.htmlspecialchars($pdspRecord['signed_document_path']); ?>"
                               download class="btn btn-sm py-0 px-2 btn-outline-secondary" style="font-size:.75rem;">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="pdspToggleBtn" onclick="togglePDSP()">
                                <i class="bi bi-chevron-right" id="pdspChevron"></i>
                            </button>
                        </div>
                    </div>
                    <div id="pdspContent" class="p-3">
                        <?php if (!empty($assessmentDocs)):
                            $docsBySvc = [];
                            foreach ($assessmentDocs as $doc) { $docsBySvc[$doc['service_name']][] = $doc; }
                        ?>
                        <h6 class="fw-bold mb-2" style="color:#1e4072;font-size:.85rem;"><i class="bi bi-folder2-open me-1"></i>Assessment Documents</h6>
                        <?php foreach ($docsBySvc as $svcName => $docs): ?>
                        <div class="mb-2">
                            <div class="fw-semibold" style="color:#a01422;font-size:.8rem;margin-bottom:4px;"><?php echo htmlspecialchars($svcName); ?></div>
                            <?php foreach ($docs as $doc):
                                $dp = $doc['file_path'];
                                if (strpos($dp,'uploads/')!==0) $dp='uploads/'.$dp;
                            ?>
                            <div class="d-flex align-items-center gap-2 p-2 rounded mb-1" style="background:#f9f9f9;font-size:.8rem;">
                                <i class="bi bi-file-earmark-text" style="color:#1e4072;"></i>
                                <span class="flex-grow-1 text-truncate"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                                <a href="<?php echo $basePath.'/'.$dp; ?>" target="_blank" class="btn btn-sm py-0 px-1" style="background:#1e4072;color:white;font-size:.7rem;"><i class="bi bi-eye"></i></a>
                                <a href="<?php echo $basePath.'/'.$dp; ?>" download class="btn btn-sm py-0 px-1 btn-outline-secondary" style="font-size:.7rem;"><i class="bi bi-download"></i></a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted text-center py-3" style="font-size:.85rem;">No assessment documents found.</p>
                        <?php endif; ?>
                        <?php if (empty($pdspRecord['signed_document_path']) && empty($assessmentDocs)): ?>
                        <p class="text-muted text-center py-2" style="font-size:.85rem;">No PDSP or assessment documents available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile: View docs button -->
    <div class="d-none" id="mobilePdspBtn">
        <button type="button" class="btn btn-sm mb-3" style="background:#1e4072;color:white;"
                data-bs-toggle="modal" data-bs-target="#pdspModal">
            <i class="bi bi-clipboard-data me-1"></i>View PDSP &amp; Assessment Docs
        </button>
    </div>
    <?php if (!$readOnly): ?>
    <!-- ===== SECTION 5: SIGNING METHOD ===== -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Signing Method</h5>
        </div>
        <div class="card-body">
            <?php
            $methodLocked  = !empty($signatories) && !empty(array_filter($signatories, fn($s) => !empty($s['signature_image_path'])));
            $currentMethod = $iep['signing_method'] ?? '';
            ?>
            <?php if ($methodLocked): ?>
            <div class="alert alert-info mb-3 py-2"><i class="bi bi-lock-fill me-2"></i>Signing method locked: <strong><?php echo $currentMethod==='digital'?'Digital Signatures':'Print & Upload'; ?></strong></div>
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <div id="card_print_upload" onclick="<?php echo $methodLocked ? '' : "selectMethod('print_upload')"; ?>"
                         style="border:2px solid <?php echo $currentMethod==='print_upload'?'#a01422':'#1e4072'; ?>;border-radius:8px;padding:20px;cursor:<?php echo $methodLocked?'default':'pointer'; ?>;transition:all .2s;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-printer" style="font-size:2rem;color:<?php echo $currentMethod==='print_upload'?'#a01422':'#1e4072'; ?>;"></i>
                            <div>
                                <div class="fw-bold" style="color:<?php echo $currentMethod==='print_upload'?'#a01422':'#1e4072'; ?>;">Print &amp; Upload</div>
                                <small class="text-muted">Print the IEP, sign physically, upload signed copy</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="card_digital" onclick="<?php echo $methodLocked ? '' : "selectMethod('digital')"; ?>"
                         style="border:2px solid <?php echo $currentMethod==='digital'?'#a01422':'#1e4072'; ?>;border-radius:8px;padding:20px;cursor:<?php echo $methodLocked?'default':'pointer'; ?>;transition:all .2s;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-vector-pen" style="font-size:2rem;color:<?php echo $currentMethod==='digital'?'#a01422':'#1e4072'; ?>;"></i>
                            <div>
                                <div class="fw-bold" style="color:<?php echo $currentMethod==='digital'?'#a01422':'#1e4072'; ?>;">Digital Signatures</div>
                                <small class="text-muted">Send to signatories to sign digitally from their devices</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="signingMethod" name="signing_method" value="<?php echo htmlspecialchars($currentMethod); ?>">
        </div>
    </div>

    <!-- ===== SECTION 6: SIGNATORIES ===== -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Signatories</h5>
        </div>
        <div class="card-body">
            <?php
            $sigByRole = [];
            foreach ($signatories as $s) { $sigByRole[$s['signatory_role']] = $s; }

            // Slots config: label, auto-fill user, whether F2F applies
            $slots = [
                'parent_guardian'    => ['label' => 'Parents / Guardian',       'user' => $linkedParent,    'f2f' => true],
                'guidance_counselor' => ['label' => 'Guidance Counselor',       'user' => $linkedGuidance,  'f2f' => true],
                'school_head'        => ['label' => 'School Head',              'user' => $linkedPrincipal, 'f2f' => true],
                'sned_teacher'       => ['label' => 'SPED Teacher',             'user' => ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name'] ?? ''], 'f2f' => false],
                'teacher'            => ['label' => 'Teacher/s (optional)',     'user' => null, 'f2f' => true],
                'ilrc_supervisor'    => ['label' => 'ILRC Supervisor (optional)','user' => null, 'f2f' => true],
            ];
            ?>

            <?php if ($currentMethod === 'digital' && $iep['status'] === 'signing'): ?>
            <!-- DIGITAL: Status display — show who signed, who is pending -->
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Draft sent. Signatories sign from their own portals. Signatures appear here automatically.
            </p>
            <div class="row g-2">
            <?php foreach ($slots as $roleKey => $cfg):
                $sig      = $sigByRole[$roleKey] ?? null;
                $name     = $sig['signatory_name'] ?? ($cfg['user']['name'] ?? '—');
                $isSigned = $sig && !empty($sig['signature_image_path']);
                $isF2F    = $sig && $sig['signature_image_path'] === 'f2f_signed';

                // Map signatory role to system role for sign link
                $roleToSystem = [
                    'parent_guardian'    => 'parent',
                    'guidance_counselor' => 'guidance',
                    'school_head'        => 'principal',
                    'sned_teacher'       => 'sped_teacher',
                ];
                $systemRole = $roleToSystem[$roleKey] ?? null;
                $isCurrentUserSlot = ($systemRole === $role);
            ?>
                <div class="col-md-6">
                    <div class="p-2 rounded d-flex align-items-center gap-2"
                         style="border:1px solid <?php echo $isSigned ? '#3b6d11' : '#dee2e6'; ?>;background:<?php echo $isSigned ? '#f0fff0' : '#fafafa'; ?>;">
                        <?php if ($isSigned): ?>
                            <i class="bi bi-check-circle-fill" style="color:#3b6d11;font-size:1.1rem;flex-shrink:0;"></i>
                        <?php else: ?>
                            <i class="bi bi-circle" style="color:#aaa;font-size:1.1rem;flex-shrink:0;"></i>
                        <?php endif; ?>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold small" style="color:#1e4072;"><?php echo htmlspecialchars($name); ?></div>
                            <div class="text-muted" style="font-size:.75rem;"><?php echo $cfg['label']; ?></div>
                        </div>
                        <?php if ($isSigned && !$isF2F && !empty($sig['signature_image_path'])): ?>
                        <img src="<?php echo $basePath.'/'.htmlspecialchars($sig['signature_image_path']); ?>"
                             alt="Sig" style="max-height:36px;border:1px solid #dee2e6;border-radius:3px;flex-shrink:0;">
                        <?php elseif ($isSigned): ?>
                        <span class="badge" style="background:#3b6d11;font-size:.7rem;">Signed</span>
                        <?php elseif ($sig && $isCurrentUserSlot && !$readOnly): ?>
                        <!-- Show Sign button for the current user's own slot -->
                        <a href="<?php echo $basePath; ?>/iep/sign/<?php echo $iep['id']; ?>/<?php echo $sig['id']; ?>"
                           class="btn btn-sm" style="background:#a01422;color:white;font-size:.75rem;white-space:nowrap;">
                            <i class="bi bi-pen me-1"></i>Sign
                        </a>
                        <?php else: ?>
                        <span class="badge bg-secondary" style="font-size:.7rem;">Pending</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <?php elseif ($currentMethod === 'print_upload'): ?>
            <!-- PRINT & UPLOAD: Name inputs + F2F checklist -->
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Enter each signatory's name, then check the box after they sign on the physical document.
            </p>
            <div class="row g-2">
            <?php foreach ($slots as $roleKey => $cfg):
                $sig      = $sigByRole[$roleKey] ?? null;
                $autoName = $sig['signatory_name'] ?? ($cfg['user']['name'] ?? '');
                $isSigned = $sig && !empty($sig['signature_image_path']);
                $isF2F    = $sig && $sig['signature_image_path'] === 'f2f_signed';
            ?>
                <div class="col-md-6">
                    <div class="p-2 rounded" style="border:1px solid <?php echo $isF2F ? '#3b6d11' : '#dee2e6'; ?>;background:#fafafa;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="fw-semibold mb-0 small" style="color:#1e4072;"><?php echo $cfg['label']; ?></label>
                            <?php if ($isF2F): ?>
                            <span class="badge" style="background:#3b6d11;font-size:.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Signed</span>
                            <?php endif; ?>
                        </div>
                        <input type="text" class="form-control form-control-sm signatory-name mb-1"
                               data-role="<?php echo $roleKey; ?>"
                               data-sig-id="<?php echo $sig['id'] ?? ''; ?>"
                               value="<?php echo htmlspecialchars($autoName); ?>"
                               placeholder="Enter name..."
                               <?php echo $isF2F ? 'readonly' : ''; ?>>
                        <?php if ($cfg['f2f'] && $sig && !$isF2F): ?>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox"
                                   id="f2f_<?php echo $sig['id']; ?>"
                                   onchange="markF2FSigned(this, <?php echo $iep['id']; ?>, <?php echo $sig['id']; ?>)">
                            <label class="form-check-label text-muted" style="font-size:.75rem;"
                                   for="f2f_<?php echo $sig['id']; ?>">
                                Signed on physical document
                            </label>
                        </div>
                        <?php elseif (!$sig): ?>
                        <small class="text-muted" style="font-size:.75rem;">Save draft first.</small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <?php else: ?>
            <!-- NO METHOD SELECTED: Show name inputs only -->
            <p class="text-muted small mb-3">Select a signing method above, then save draft to enable sending.</p>
            <div class="row g-2">
            <?php foreach ($slots as $roleKey => $cfg):
                $sig      = $sigByRole[$roleKey] ?? null;
                $autoName = $sig['signatory_name'] ?? ($cfg['user']['name'] ?? '');
            ?>
                <div class="col-md-6">
                    <div class="p-2 rounded" style="border:1px solid #dee2e6;background:#fafafa;">
                        <label class="fw-semibold mb-1 small d-block" style="color:#1e4072;"><?php echo $cfg['label']; ?></label>
                        <input type="text" class="form-control form-control-sm signatory-name"
                               data-role="<?php echo $roleKey; ?>"
                               data-sig-id="<?php echo $sig['id'] ?? ''; ?>"
                               value="<?php echo htmlspecialchars($autoName); ?>"
                               placeholder="Enter name...">
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <small class="text-muted mt-2 d-block" style="font-size:.8rem;">At least one signatory name required before signing.</small>
        </div>
    </div>

    <!-- ===== SECTION 6b: SPED TEACHER SIGNATURE PAD ===== -->
    <?php $snedSig = $sigByRole['sned_teacher'] ?? null; $snedSigned = $snedSig && !empty($snedSig['signature_image_path']); ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-pen me-2"></i>SPED Teacher Signature</h5>
            <?php if ($snedSigned): ?>
            <span class="badge" style="background:#3b6d11;"><i class="bi bi-check-circle-fill me-1"></i>Signed</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if ($snedSigned && $snedSig['signature_image_path'] !== 'f2f_signed'): ?>
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#f0fff0;border:1px solid #3b6d11;">
                <div>
                    <div class="fw-semibold" style="color:#3b6d11;">Signature saved</div>
                    <img src="<?php echo $basePath.'/'.htmlspecialchars($snedSig['signature_image_path']); ?>"
                         alt="SNEd Signature" style="max-height:70px;margin-top:6px;border:1px solid #dee2e6;border-radius:4px;">
                </div>
            </div>
            <?php elseif (!$readOnly): ?>
            <p class="text-muted small mb-2">Draw your signature below. This will be saved immediately.</p>
            <canvas id="sigPad_sned_teacher"
                    style="border:2px solid #1e4072;border-radius:6px;width:100%;height:150px;touch-action:none;display:block;max-width:600px;"></canvas>
            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" onclick="clearSigPad('sned_teacher')">
                    <i class="bi bi-eraser me-1"></i>Clear
                </button>
                <button type="button" class="btn" style="background:#a01422;color:white;"
                        onclick="saveSigPad('sned_teacher', <?php echo $iep['id']; ?>, <?php echo $snedSig['id'] ?? 0; ?>)">
                    <i class="bi bi-check-circle me-1"></i>Save My Signature
                </button>
            </div>
            <?php if (!$snedSig): ?>
            <small class="text-muted d-block mt-2">Save draft first to enable signing.</small>
            <?php endif; ?>
            <?php else: ?>
            <p class="text-muted small">No signature recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== SECTION 6c: SEND IEP DRAFT ===== -->
    <div class="card mb-4" id="sendDraftSection">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-send me-2"></i>Send IEP Draft</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Send this IEP draft to <strong>Parent, Guidance Counselor, and School Head</strong> so they can view and sign from their portals.
                <?php if ($iep['status'] === 'signing'): ?>
                <span class="badge ms-2" style="background:#3b6d11;"><i class="bi bi-check-circle-fill me-1"></i>Already sent</span>
                <?php endif; ?>
            </p>
            <button type="button" class="btn" style="background:#1e4072;color:white;"
                    onclick="sendIEPDraft(<?php echo $iep['id']; ?>)" id="sendDraftBtn">
                <i class="bi bi-send me-2"></i>Send Draft to Signatories
            </button>
            <div id="sendDraftResult" class="mt-2"></div>
        </div>
    </div>

    <!-- ===== SECTION 7: UPLOAD SIGNED DOCUMENT (print_upload only) ===== -->
    <div class="card mb-4" id="uploadSection" style="display:<?php echo $currentMethod==='print_upload'?'block':'none'; ?>;">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Signed Document</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($iep['signed_document_path'])): ?>
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#f0fff0;border:1px solid #3b6d11;">
                <i class="bi bi-file-earmark-check-fill" style="color:#3b6d11;font-size:1.5rem;"></i>
                <div><div class="fw-semibold" style="color:#3b6d11;">Document uploaded</div>
                <small class="text-muted"><?php echo basename($iep['signed_document_path']); ?></small></div>
                <a href="<?php echo $basePath.'/'.htmlspecialchars($iep['signed_document_path']); ?>" target="_blank"
                   class="btn btn-sm ms-auto" style="background:#1e4072;color:white;"><i class="bi bi-eye me-1"></i>View</a>
            </div>
            <?php else: ?>
            <div id="uploadZone" style="border:2px dashed #a01422;border-radius:8px;padding:40px;text-align:center;cursor:pointer;"
                 onclick="document.getElementById('signedDocInput').click()"
                 ondragover="event.preventDefault();this.style.background='#fff5f5';"
                 ondragleave="this.style.background='';" ondrop="handleDocDrop(event)">
                <i class="bi bi-cloud-upload" style="font-size:2.5rem;color:#a01422;"></i>
                <p class="mt-2 mb-1 fw-semibold" style="color:#a01422;">Click or drag to upload signed document</p>
                <small class="text-muted">Accepts jpg, png, pdf -- max 10MB</small>
            </div>
            <input type="file" id="signedDocInput" accept=".jpg,.jpeg,.png,.pdf" class="d-none" onchange="uploadSignedDoc(this.files[0])">
            <div id="uploadResult" class="mt-2"></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== SECTION 8: ACTION BAR (Save Draft + Mark as Signed) ===== -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <button type="button" class="btn w-100 fw-semibold" id="btnSaveDraft"
                            style="background:#1e4072;color:white;padding:14px;">
                        <i class="bi bi-floppy me-2"></i>Save Draft
                    </button>
                    <small class="text-muted d-block text-center mt-1">Save current progress</small>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn w-100 fw-bold" id="markSignedBtn"
                            style="background:#a01422;color:white;padding:14px;" onclick="confirmMarkSigned()">
                        <i class="bi bi-check-circle-fill me-2"></i>Mark as Signed
                    </button>
                    <small class="text-muted d-block text-center mt-1">Locks the IEP after signing</small>
                </div>
            </div>
            <div class="digital-only mt-3 text-center" style="display:<?php echo $currentMethod==='digital'?'block':'none'; ?>;">
                <div class="form-check d-inline-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" id="acknowledgePending" value="1">
                    <label class="form-check-label text-muted small" for="acknowledgePending">
                        Acknowledge pending digital signatures and proceed anyway
                    </label>
                </div>
            </div>
        </div>
    </div>

    <form id="markSignedForm" method="POST" action="<?php echo $basePath; ?>/iep/mark-signed">
        <input type="hidden" name="iep_id" value="<?php echo $iep['id']; ?>">
        <input type="hidden" name="acknowledge_pending" id="hiddenAcknowledge" value="0">
    </form>

    <?php else: ?>
    <div class="alert" style="background:#f0fff0;border:1px solid #3b6d11;color:#3b6d11;">
        <i class="bi bi-check-circle-fill me-2"></i><strong>IEP Signed and Locked.</strong> No further edits allowed.
    </div>
    <?php endif; // !$readOnly ?>

    </form>
</div><!-- /main-content -->

<!-- PDSP Modal (mobile) -->
<div class="modal fade" id="pdspModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:white;">
                <h5 class="modal-title"><i class="bi bi-clipboard-data me-2"></i>PDSP &amp; Assessment Docs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($pdspRecord['signed_document_path'])): ?>
                <div class="mb-3">
                    <a href="<?php echo $basePath.'/'.htmlspecialchars($pdspRecord['signed_document_path']); ?>"
                       target="_blank" class="btn btn-sm" style="background:#a01422;color:white;">
                        <i class="bi bi-file-earmark-pdf me-1"></i>View PDSP Document
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($assessmentDocs)): ?>
                <h6 style="color:#1e4072;">Assessment Documents</h6>
                <?php foreach ($assessmentDocs as $doc):
                    $dp = $doc['file_path'];
                    if (strpos($dp,'uploads/')!==0) $dp='uploads/'.$dp;
                ?>
                <div class="d-flex align-items-center gap-2 p-2 rounded mb-1" style="background:#f9f9f9;font-size:.85rem;">
                    <i class="bi bi-file-earmark-text" style="color:#1e4072;"></i>
                    <span class="flex-grow-1"><?php echo htmlspecialchars($doc['original_name']); ?></span>
                    <span class="text-muted small"><?php echo htmlspecialchars($doc['service_name']); ?></span>
                    <a href="<?php echo $basePath.'/'.$dp; ?>" target="_blank" class="btn btn-sm py-0 px-2" style="background:#1e4072;color:white;font-size:.75rem;"><i class="bi bi-eye"></i></a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const BASE      = '<?php echo $basePath; ?>';
const IEP_ID    = <?php echo $iep['id']; ?>;
const READ_ONLY = <?php echo $readOnly ? 'true' : 'false'; ?>;

// ---- Domain chips ----
function getDomains() {
    return Array.from(document.querySelectorAll('.domain-chip')).map(c => c.dataset.name).filter(Boolean);
}

function getDevDomain() {
    const domains = getDomains();
    return domains.length > 0 ? domains.join(', ') : '';
}

function addDomainChip() {
    const sel = document.getElementById('domainSelect');
    if (!sel || !sel.value) return;
    if (sel.value === '__custom__') {
        document.getElementById('customDomainRow').style.display = 'flex';
        document.getElementById('customDomainInput').focus();
        return;
    }
    insertChip(sel.value);
    sel.value = '';
}

function addCustomDomain() {
    const input = document.getElementById('customDomainInput');
    const val = input.value.trim();
    if (!val) return;
    insertChip(val);
    input.value = '';
    document.getElementById('customDomainRow').style.display = 'none';
    document.getElementById('domainSelect').value = '';
}

function insertChip(name) {
    if (getDomains().includes(name)) { showToast('Domain already added', 'warning'); return; }
    const container = document.getElementById('domainChipsContainer');
    const placeholder = document.getElementById('domainPlaceholder');
    if (placeholder) placeholder.remove();
    const span = document.createElement('span');
    span.className = 'domain-chip badge d-flex align-items-center gap-1 px-3 py-2';
    span.dataset.name = name;
    span.style.cssText = 'background:#1e4072;color:white;font-size:.85rem;border-radius:20px;';
    span.innerHTML = name + '<button type="button" class="btn-close btn-close-white ms-1" style="font-size:.55rem;" onclick="removeDomainChip(this)" title="Remove"></button>';
    container.appendChild(span);
}

function removeDomainChip(btn) {
    btn.closest('.domain-chip').remove();
    const container = document.getElementById('domainChipsContainer');
    if (!container.querySelector('.domain-chip')) {
        const ph = document.createElement('span');
        ph.id = 'domainPlaceholder';
        ph.className = 'text-muted small align-self-center ps-1';
        ph.textContent = 'No domains added yet';
        container.appendChild(ph);
    }
}

let stepCount = document.querySelectorAll('.step-row').length;
const MAX_STEPS = 10;

function updateStepNumbers() {
    document.querySelectorAll('.step-row').forEach((row, i) => { row.querySelector('.step-num').textContent = i+1; });
    stepCount = document.querySelectorAll('.step-row').length;
    const note = document.getElementById('stepCountNote');
    if (note) note.textContent = stepCount + ' / ' + MAX_STEPS + ' rows';
    document.querySelectorAll('.remove-step-btn').forEach((btn,i,arr) => { btn.disabled = arr.length===1; });
    const addBtn = document.getElementById('addStepBtn');
    if (addBtn) addBtn.disabled = stepCount >= MAX_STEPS;
}

function addStep() {
    if (stepCount >= MAX_STEPS) return;
    const tbody = document.getElementById('stepsBody');
    const tr = document.createElement('tr');
    tr.className = 'step-row';
    tr.innerHTML = `<td class="text-center fw-bold step-num" style="vertical-align:top;padding-top:10px;"></td>
        <td><textarea class="form-control form-control-sm auto-expand step-objectives" rows="2"></textarea></td>
        <td><textarea class="form-control form-control-sm auto-expand step-observation" rows="2"></textarea></td>
        <td><textarea class="form-control form-control-sm auto-expand step-activities" rows="2"></textarea></td>
        <td><textarea class="form-control form-control-sm auto-expand step-materials" rows="2"></textarea></td>
        <td><textarea class="form-control form-control-sm auto-expand step-evaluation" rows="2"></textarea></td>
        <td><input type="text" class="form-control form-control-sm step-duration" placeholder="e.g. 30 mins"></td>
        <td style="vertical-align:top;padding-top:8px;"><button type="button" class="btn btn-sm btn-outline-danger remove-step-btn" onclick="removeStep(this)"><i class="bi bi-x-lg"></i></button></td>`;
    tbody.appendChild(tr);
    updateStepNumbers();
    tr.querySelectorAll('.auto-expand').forEach(ta => ta.addEventListener('input', autoExpand));
}

function removeStep(btn) {
    if (document.querySelectorAll('.step-row').length <= 1) return;
    btn.closest('.step-row').remove();
    updateStepNumbers();
}

function getSteps() {
    return Array.from(document.querySelectorAll('.step-row')).map(row => ({
        objectives:  row.querySelector('.step-objectives')?.value  || '',
        observation: row.querySelector('.step-observation')?.value || '',
        activities:  row.querySelector('.step-activities')?.value  || '',
        materials:   row.querySelector('.step-materials')?.value   || '',
        evaluation:  row.querySelector('.step-evaluation')?.value  || '',
        duration_lp: row.querySelector('.step-duration')?.value    || '',
    }));
}

function autoExpand() { this.style.height='auto'; this.style.height=this.scrollHeight+'px'; }

function selectMethod(method) {
    document.getElementById('signingMethod').value = method;
    ['print_upload','digital'].forEach(m => {
        const card = document.getElementById('card_'+m);
        const color = m===method ? '#a01422' : '#1e4072';
        card.style.borderColor = color;
        card.querySelector('.fw-bold').style.color = color;
        card.querySelector('i').style.color = color;
    });
    const up = document.getElementById('uploadSection');
    if (up) up.style.display = method==='print_upload' ? 'block' : 'none';
    document.querySelectorAll('.digital-only').forEach(el => { el.style.display = method==='digital' ? 'block' : 'none'; });
}

function togglePDSP() {
    const content = document.getElementById('pdspContent');
    const chevron = document.getElementById('pdspChevron');
    const panel   = document.getElementById('pdspPanel');
    if (content.style.display==='none') {
        content.style.display='block'; chevron.className='bi bi-chevron-right'; panel.style.flex='0 0 40%';
    } else {
        content.style.display='none'; chevron.className='bi bi-chevron-left'; panel.style.flex='0 0 36px';
    }
}

function handleResize() {
    const c=document.getElementById('pdspContent'), p=document.getElementById('pdspPanel'), m=document.getElementById('mobilePdspBtn');
    if (!c) return;
    if (window.innerWidth < 1200) { c.style.display='none'; if(p) p.style.display='none'; if(m) m.classList.remove('d-none'); }
    else { c.style.display='block'; if(p) p.style.display='block'; if(m) m.classList.add('d-none'); }
}
window.addEventListener('resize', handleResize);
handleResize();

function collectFormData() {
    const sigs = Array.from(document.querySelectorAll('.signatory-name')).map(i=>({role:i.dataset.role,name:i.value.trim()})).filter(s=>s.name);
    const fd = new FormData();
    fd.append('iep_id',               IEP_ID);
    fd.append('school_year',          document.getElementById('f_school_year')?.value||'');
    fd.append('re_evaluation_date',   document.getElementById('f_re_eval')?.value||'');
    fd.append('signing_method',       document.getElementById('signingMethod')?.value||'');
    fd.append('developmental_domain', getDevDomain());
    fd.append('priority_needs',       document.getElementById('f_priority_needs')?.value||'');
    fd.append('terminal_objectives',  document.getElementById('f_terminal_obj')?.value||'');
    fd.append('domains',              JSON.stringify(getDomains()));
    fd.append('steps',                JSON.stringify(getSteps()));
    fd.append('signatories',          JSON.stringify(sigs));
    return fd;
}

document.getElementById('btnSaveDraft')?.addEventListener('click', () => saveDraft(false));

function saveDraft(silent=false) {
    return new Promise(resolve => {
        fetch(BASE+'/iep/save-draft', {method:'POST', body:collectFormData()})
            .then(r=>r.json())
            .then(data => {
                if (!silent) showToast(data.success?'Draft saved':(data.message||'Save failed'), data.success?'success':'danger');
                resolve(data.success);
            })
            .catch(()=>{ if(!silent) showToast('Save failed','danger'); resolve(false); });
    });
}

if (!READ_ONLY) setInterval(()=>saveDraft(true), 60000);

function handleDocDrop(e) {
    e.preventDefault(); document.getElementById('uploadZone').style.background='';
    const file=e.dataTransfer.files[0]; if(file) uploadSignedDoc(file);
}

function uploadSignedDoc(file) {
    if (!file) return;
    if (!['image/jpeg','image/png','application/pdf'].includes(file.type)) { showToast('Only jpg, png, pdf allowed','danger'); return; }
    if (file.size > 10*1024*1024) { showToast('File too large (max 10MB)','danger'); return; }
    const fd=new FormData(); fd.append('iep_id',IEP_ID); fd.append('signed_doc',file);
    fetch(BASE+'/iep/upload-signed-doc',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
        if(data.success) document.getElementById('uploadResult').innerHTML='<div class="alert alert-success py-2 mt-2"><i class="bi bi-check-circle me-2"></i>Uploaded: '+data.filename+'</div>';
        else showToast(data.message||'Upload failed','danger');
    });
}

function sendSignatureRequest(iepId, signatoryId, recipientId) {
    if (!signatoryId) { showToast('Save draft first to enable sending','warning'); return; }
    saveDraft(true).then(()=>{
        const fd=new FormData(); fd.append('iep_id',iepId); fd.append('signatory_id',signatoryId);
        if (recipientId && recipientId!=='null') fd.append('recipient_id',recipientId);
        fetch(BASE+'/iep/send-signature-request',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
            showToast(data.message||(data.success?'Sent':'Failed'), data.success?'success':'danger');
        });
    });
}

function sendIEPDraft(iepId) {
    // Client-side validation before sending
    const sigs = Array.from(document.querySelectorAll('.signatory-name')).filter(i => i.value.trim());
    if (sigs.length === 0) {
        showToast('Save draft with at least one signatory name first', 'warning');
        return;
    }
    const reEvalDate = document.getElementById('f_re_eval')?.value;
    if (!reEvalDate) {
        showToast('Re-evaluation date is required before sending', 'warning');
        return;
    }
    if (!confirm('Send IEP draft to Parent, Guidance, and School Head?\nThey will receive a notification to review and sign.')) return;

    saveDraft(true).then(() => {
        const btn = document.getElementById('sendDraftBtn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Sending...'; }
        const fd = new FormData();
        fd.append('iep_id', iepId);
        fetch(BASE + '/iep/send-draft', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                const result = document.getElementById('sendDraftResult');
                if (data.success) {
                    if (result) result.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle-fill me-2"></i>' + data.message + '</div>';
                    if (btn) { btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Sent'; btn.style.background = '#3b6d11'; }
                    showToast(data.message, 'success');
                    // Reload page after 2s to show updated status
                    setTimeout(() => location.reload(), 2000);
                } else {
                    if (result) result.innerHTML = '<div class="alert alert-danger py-2 mb-0">' + (data.message || 'Failed to send') + '</div>';
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send me-2"></i>Send Draft to Signatories'; }
                    showToast(data.message || 'Failed', 'danger');
                }
            })
            .catch(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-send me-2"></i>Send Draft to Signatories'; }
                showToast('Send failed', 'danger');
            });
    });
}

function markF2FSigned(checkbox, iepId, signatoryId) {
    if (!checkbox.checked) return;
    const fd = new FormData();
    fd.append('iep_id',       iepId);
    fd.append('signatory_id', signatoryId);
    fetch(BASE + '/iep/mark-f2f-signed', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Update badge
                const slot = checkbox.closest('.p-2');
                if (slot) {
                    const badge = slot.querySelector('.badge');
                    if (badge) { badge.style.background = '#3b6d11'; badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Signed (F2F)'; }
                    slot.style.borderColor = '#3b6d11';
                }
            } else {
                checkbox.checked = false;
                showToast(data.message || 'Failed', 'danger');
            }
        })
        .catch(() => { checkbox.checked = false; showToast('Failed', 'danger'); });
}

// ---- SNEd Teacher Signature Pad ----
const sigPads = {};
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('canvas[id^="sigPad_"]').forEach(canvas => {
        const roleKey = canvas.id.replace('sigPad_', '');
        // Set canvas resolution
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPads[roleKey] = new SignaturePad(canvas, { penColor: '#1e4072' });
    });
});

function clearSigPad(roleKey) {
    if (sigPads[roleKey]) sigPads[roleKey].clear();
}

function saveSigPad(roleKey, iepId, signatoryId) {
    const pad = sigPads[roleKey];
    if (!pad || pad.isEmpty()) { showToast('Please draw your signature first', 'warning'); return; }

    // If no signatoryId yet, save draft first to create the signatory record
    if (!signatoryId || signatoryId === 0) {
        saveDraft(false).then(() => {
            showToast('Draft saved. Please click Save Signature again.', 'info');
        });
        return;
    }

    const fd = new FormData();
    fd.append('iep_id',        iepId);
    fd.append('signatory_id',  signatoryId);
    fd.append('signature_data', pad.toDataURL('image/png'));
    fetch(BASE + '/iep/save-signature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Signature saved', 'success');
                // Replace pad with image
                const container = document.getElementById('sigPad_' + roleKey).closest('.mt-2');
                container.innerHTML = '<div class="p-2 rounded" style="background:#f0fff0;border:1px solid #3b6d11;">' +
                    '<small class="text-muted d-block mb-1">Signature:</small>' +
                    '<img src="' + BASE + '/' + data.path + '" style="max-height:60px;border:1px solid #dee2e6;border-radius:4px;"></div>';
                // Update badge
                const badge = container.closest('.p-3').querySelector('.badge');
                if (badge) { badge.style.background = '#3b6d11'; badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Signed'; }
            } else {
                showToast(data.message || 'Failed to save signature', 'danger');
            }
        });
}

function confirmMarkSigned() {
    saveDraft(true).then(()=>{
        const ack=document.getElementById('acknowledgePending');
        document.getElementById('hiddenAcknowledge').value=ack?.checked?'1':'0';
        if (confirm('Mark IEP as signed? This will lock the IEP until the re-evaluation date.\n\nConfirm to proceed.')) {
            document.getElementById('markSignedForm').submit();
        }
    });
}

function showToast(msg, type) {
    const colors={success:'#3b6d11',danger:'#a01422',warning:'#ffc107',info:'#1e4072'};
    const t=document.createElement('div');
    t.style.cssText='position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:6px;color:white;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.2);background:'+(colors[type]||'#1e4072');
    t.textContent=msg; document.body.appendChild(t); setTimeout(()=>t.remove(),3500);
}

document.addEventListener('DOMContentLoaded', function() {
    updateStepNumbers();
    document.querySelectorAll('.auto-expand').forEach(ta=>{ ta.addEventListener('input',autoExpand); autoExpand.call(ta); });

    // Domain select: show custom input when "Other" selected
    const domainSel = document.getElementById('domainSelect');
    if (domainSel) {
        domainSel.addEventListener('change', function() {
            const row = document.getElementById('customDomainRow');
            if (row) row.style.display = this.value === '__custom__' ? 'flex' : 'none';
            if (this.value === '__custom__') document.getElementById('customDomainInput')?.focus();
        });
    }
    const customInput = document.getElementById('customDomainInput');
    if (customInput) {
        customInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addCustomDomain(); }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>