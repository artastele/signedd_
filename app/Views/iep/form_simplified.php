<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5 (SIMPLIFIED)
// Last modified: 2026-05-13
// Part of: SPED LMS — IEP Form (Upload Only System)

$pageTitle = 'IEP — Individualized Education Plan';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-3">

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['iep_errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0" style="border-left:4px solid #a01422!important;">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($_SESSION['iep_errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['iep_errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row g-3 align-items-start">
        <div class="col-12">
            <?php
            $iepId = (int) $iep['id'];
            $showSigningControls = $showSigningControls ?? false;
            $allSignaturesCaptured = $allSignaturesCaptured ?? false;
            $signingFrozen = (($iep['status'] ?? '') === 'signing');
            $signatoriesReadOnly = $readOnly || ($signingFrozen && in_array($userRole ?? '', ['sped_teacher', 'admin'], true));
            $viewerDigitalSlotComplete = false;
            if (($iep['status'] ?? '') === 'signing' && !empty($signatories) && isset($userRole)) {
                $slotByAppRole = [
                    'principal' => 'school_head',
                    'guidance'  => 'guidance_counselor',
                    'parent'    => 'parent_guardian',
                ];
                $wantRole = $slotByAppRole[$userRole] ?? '';
                if ($wantRole !== '') {
                    foreach ($signatories as $sr) {
                        if (($sr['signatory_role'] ?? '') === $wantRole && trim((string) ($sr['signature_image_path'] ?? '')) !== '') {
                            $viewerDigitalSlotComplete = true;
                            break;
                        }
                    }
                }
            }
            $signatoryRoleLabels = [
                'parent_guardian'    => 'Parents / Guardian',
                'guidance_counselor' => 'Guidance Counselor / Teacher',
                'teacher'            => 'Teacher/s',
                'sned_teacher'       => 'SNEd Teacher',
                'school_head'        => 'School Head',
                'ilrc_supervisor'    => 'ILRC Supervisor',
            ];
            $navActive = 'form';
            $showWorkspaceLink = in_array($userRole ?? '', ['sped_teacher', 'admin'], true);
            require __DIR__ . '/partials/iep_p5_p6_nav_bar.php';
            ?>

            <?php require __DIR__ . '/partials/iep_form_sections_1_4.php'; ?>
            <?php require __DIR__ . '/partials/iep_form_section_5_steps.php'; ?>

            <form id="iepForm" method="POST" action="<?= $basePath ?>/iep/submitIEP" enctype="multipart/form-data">
                <input type="hidden" name="iep_id" value="<?= $iep['id'] ?>">
                <input type="hidden" name="re_evaluation_date" id="reEvalHiddenForSubmit" value="<?= htmlspecialchars($iep['re_evaluation_date'] ?? '') ?>">

                <!-- Signatories -->
                <div class="card mb-3" style="border-left:4px solid #1e4072;">
                    <div class="card-header py-2" style="background:#1e4072;color:white;">
                        <h5 class="mb-0 fs-6"><i class="bi bi-people me-2"></i>Signatories</h5>
                    </div>
                    <div class="card-body py-2 px-2 px-md-3">

                        <?php
                        $signatoryLookup = [];
                        if (!empty($signatories)) {
                            foreach ($signatories as $sig) {
                                $signatoryLookup[$sig['signatory_role']] = $sig['signatory_name'];
                            }
                        }
                        ?>

                        <div class="row g-2">
                            <?php foreach ($signatoryRoleLabels as $roleKey => $roleLabel): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border" style="border-color:#dee2e6!important;">
                                        <div class="card-body py-2 px-2">
                                            <?php if (!$signatoriesReadOnly): ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input signatory-checkbox"
                                                           type="checkbox"
                                                           name="signatory_<?= $roleKey ?>"
                                                           id="sig_<?= $roleKey ?>"
                                                           value="1"
                                                           <?= isset($signatoryLookup[$roleKey]) ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-semibold" for="sig_<?= $roleKey ?>">
                                                        <?= htmlspecialchars($roleLabel) ?>
                                                    </label>
                                                </div>
                                                <input type="text"
                                                       class="form-control form-control-sm signatory-name"
                                                       name="signatory_name_<?= $roleKey ?>"
                                                       placeholder="Full name"
                                                       value="<?= htmlspecialchars($signatoryLookup[$roleKey] ?? '') ?>"
                                                       <?= isset($signatoryLookup[$roleKey]) ? '' : 'disabled' ?>>
                                            <?php else: ?>
                                                <?php if (isset($signatoryLookup[$roleKey])): ?>
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-check-circle-fill mt-1" style="color:#3b6d11;"></i>
                                                        <div>
                                                            <div class="fw-semibold small text-muted"><?= htmlspecialchars($roleLabel) ?></div>
                                                            <div><?= htmlspecialchars($signatoryLookup[$roleKey]) ?></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-start gap-2 text-muted">
                                                        <i class="bi bi-dash-circle mt-1"></i>
                                                        <div>
                                                            <div class="fw-semibold small"><?= htmlspecialchars($roleLabel) ?></div>
                                                            <div class="small">Not signed</div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!$readOnly && ($iep['status'] ?? '') === 'draft'): ?>
                        <div class="mt-3 pt-3 border-top" id="f2fSigningProofWrap">
                            <label class="form-label small fw-semibold mb-1" style="color:#1e4072;" for="meetingSigningProof">
                                <i class="bi bi-camera me-1"></i>Face-to-face signing proof <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                <input type="file" name="meeting_signing_proof" id="meetingSigningProof"
                                       class="form-control form-control-sm flex-grow-1" style="min-width:180px;" accept=".pdf,.jpg,.jpeg,.png,image/*,application/pdf">
                                <input type="file" id="meetingSigningProofCam" class="d-none" accept="image/*" capture="environment" aria-hidden="true">
                                <button type="button" class="btn btn-sm btn-outline-secondary d-md-none" id="meetingProofTakePhotoBtn" title="Use device camera">
                                    <i class="bi bi-camera-fill me-1"></i>Take photo
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Upload a scan or photo of the signed Part III when you use <strong>Meeting record</strong> below. JPG, PNG, or PDF — max 10MB. On a phone, use <strong>Take photo</strong> or <strong>Choose file</strong>.</small>
                        </div>
                        <?php elseif (!empty($iep['signed_document_path']) && ($iep['signing_method'] ?? '') === 'print_upload'): ?>
                        <div class="mt-3 pt-3 border-top">
                            <span class="small fw-semibold" style="color:#1e4072;">Face-to-face signing proof</span>
                            <div class="mt-1">
                                <a href="<?= htmlspecialchars(rtrim((string) $basePath, '/') . '/' . ltrim(str_replace('\\', '/', (string) $iep['signed_document_path']), '/'), ENT_QUOTES, 'UTF-8') ?>"
                                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Open uploaded proof
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Finalize -->
                <?php if (!$readOnly && $iep['status'] === 'draft'): ?>
                    <div class="card mb-3" style="border-left:4px solid #a01422;">
                        <div class="card-body py-3">
                            <div class="text-start mb-2 mx-auto" style="max-width:480px;">
                                <div class="fw-semibold small mb-2">Finalize Part III</div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="iep_finalize_mode" id="finMeet" value="meeting_record" checked>
                                    <label class="form-check-label small" for="finMeet"><strong>Meeting record</strong> — paper signing today; mark signed and notify.</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="iep_finalize_mode" id="finDig" value="digital_collect">
                                    <label class="form-check-label small" for="finDig"><strong>Digital signatures</strong> — collect sign links first.</label>
                                </div>
                            </div>
                            <div class="text-center">
                            <button type="submit" id="submitBtn" class="btn btn-sm px-4"
                                    style="background:#a01422;border-color:#a01422;color:white;">
                                <i class="bi bi-check-circle me-2"></i>Continue
                            </button>
                            </div>
                        </div>
                    </div>
                <?php elseif (($iep['status'] ?? '') === 'signing'): ?>
                    <div class="card mb-3" style="border-left:4px solid #ffc107;">
                        <div class="card-body text-center py-3">
                            <span class="badge px-3 py-2 bg-warning text-dark">
                                <i class="bi bi-hourglass-split me-2"></i>Awaiting digital signatures
                            </span>
                            <?php if (!empty($viewerDigitalSlotComplete)): ?>
                                <p class="small text-success mt-3 mb-0 fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i>Your signature for this IEP is on file. The IEP stays here until every required signer completes and the SPED teacher finalizes it.
                                </p>
                            <?php else: ?>
                                <p class="small text-muted mt-3 mb-0">
                                    This status applies to the whole IEP until all required digital signatures are collected and the SPED teacher finalizes.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif (($iep['status'] ?? '') === 'signed'): ?>
                    <div class="card mb-3" style="border-left:4px solid #3b6d11;">
                        <div class="card-body text-center py-3">
                            <span class="badge px-3 py-2" style="background:#3b6d11;">
                                <i class="bi bi-check-circle-fill me-2"></i>IEP signed
                            </span>
                            <?php if (!empty($iep['signed_at'])): ?>
                                <p class="text-muted small mt-2 mb-0">
                                    Submitted on <?= date('F j, Y g:i A', strtotime($iep['signed_at'])) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($iep['signed_document_path']) && ($iep['signing_method'] ?? '') === 'print_upload'): ?>
                                <p class="small mt-3 mb-0">
                                    <a href="<?= htmlspecialchars(rtrim((string) $basePath, '/') . '/' . ltrim(str_replace('\\', '/', (string) $iep['signed_document_path']), '/'), ENT_QUOTES, 'UTF-8') ?>"
                                       target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-paperclip me-1"></i>Meeting signing proof (uploaded)
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </form>

            <?php if (!empty($inlineSignSlot) && is_array($inlineSignSlot)): ?>
            <div class="card mb-4 border-primary" style="border-left:4px solid #a01422;" id="iepInlineSignCard">
                <div class="card-header text-white py-2" style="background:#1e4072;">
                    <h5 class="mb-0 fs-6"><i class="bi bi-pen me-2"></i>Your digital signature</h5>
                </div>
                <div class="card-body py-3">
                    <p class="small text-muted mb-2">
                        <?= htmlspecialchars($signatoryRoleLabels[$inlineSignSlot['signatory_role']] ?? ucwords(str_replace('_', ' ', (string) $inlineSignSlot['signatory_role']))) ?>
                        — <?= htmlspecialchars((string) ($inlineSignSlot['signatory_name'] ?? '')) ?>
                    </p>
                    <p class="small mb-2">Draw below (finger or mouse), then submit. You can still use the copied link on another device if needed.</p>
                    <div class="text-center">
                        <canvas id="iepInlineSigCanvas" width="600" height="200"
                                style="border:2px solid #1e4072;border-radius:6px;max-width:100%;touch-action:none;height:200px;width:100%;max-width:640px;"></canvas>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="iepInlineSigClear"><i class="bi bi-eraser me-1"></i>Clear</button>
                        <button type="button" class="btn btn-sm text-white" id="iepInlineSigSubmit" style="background:#a01422;"><i class="bi bi-check-circle me-1"></i>Submit signature</button>
                    </div>
                    <div id="iepInlineSigMsg" class="small text-danger mt-2 text-center"></div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
            <script>
            (function () {
                var canvas = document.getElementById('iepInlineSigCanvas');
                if (!canvas || typeof SignaturePad === 'undefined') return;
                var pad = new SignaturePad(canvas, { penColor: '#1e4072' });
                function resize() {
                    var ratio = Math.max(window.devicePixelRatio || 1, 1);
                    var w = canvas.offsetWidth;
                    canvas.width = w * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    pad.clear();
                }
                window.addEventListener('resize', resize);
                resize();
                document.getElementById('iepInlineSigClear')?.addEventListener('click', function () { pad.clear(); });
                document.getElementById('iepInlineSigSubmit')?.addEventListener('click', function () {
                    var msg = document.getElementById('iepInlineSigMsg');
                    if (msg) msg.textContent = '';
                    if (pad.isEmpty()) {
                        if (msg) msg.textContent = 'Please draw your signature first.';
                        return;
                    }
                    var fd = new FormData();
                    fd.append('iep_id', String(<?= (int) $iep['id'] ?>));
                    fd.append('signatory_id', String(<?= (int) $inlineSignSlot['id'] ?>));
                    fd.append('signature_data', pad.toDataURL('image/png'));
                    fetch(<?= json_encode($basePath . '/iep/save-signature', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>, { method: 'POST', body: fd, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.success) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'success', title: 'Signature saved', timer: 1500, showConfirmButton: false })
                                        .then(function () { location.reload(); });
                                } else {
                                    location.reload();
                                }
                            } else if (msg) {
                                msg.textContent = data.message || 'Could not save signature.';
                            }
                        })
                        .catch(function () {
                            if (msg) msg.textContent = 'Network error.';
                        });
                });
            })();
            </script>
            <?php endif; ?>

            <?php if (!empty($showSigningControls) && $showSigningControls): ?>
            <div class="card mb-4 border-info" style="border-left:4px solid #0dcaf0;">
                <div class="card-header" style="background:#0c5460;color:white;">
                    <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Digital signing status</h5>
                </div>
                <div class="card-body py-2">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr><th>Role</th><th>Name</th><th>Status</th><th>Sign link (copy)</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($signatories as $sig): ?>
                                <?php
                                $roleLabel = $signatoryRoleLabels[$sig['signatory_role']] ?? ucwords(str_replace('_', ' ', (string) $sig['signatory_role']));
                                $hasPath = !empty($sig['signature_image_path']) && trim((string) $sig['signature_image_path']) !== '';
                                $digitalInviteRoles = ['parent_guardian', 'guidance_counselor', 'school_head', 'sned_teacher'];
                                $rk = (string) ($sig['signatory_role'] ?? '');
                                $ss = (string) ($sig['send_status'] ?? '');
                                $digitalSigning = (($iep['status'] ?? '') === 'signing' && ($iep['signing_method'] ?? '') === 'digital');
                                $pend = $digitalSigning
                                    && in_array($rk, $digitalInviteRoles, true)
                                    && !$hasPath
                                    && ($ss === 'pending' || $ss === '' || $ss === 'not_sent');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($roleLabel) ?></td>
                                    <td><?= htmlspecialchars((string) ($sig['signatory_name'] ?? '')) ?></td>
                                    <td>
                                        <?php if ($hasPath): ?>
                                            <span class="badge bg-success">Signed</span>
                                        <?php elseif ($pend): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">On file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($pend):
                                            $signUrl = $basePath . '/iep/sign/' . (int) $iep['id'] . '/' . (int) $sig['id'];
                                            ?>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($signUrl) ?>">
                                                <button type="button" class="btn btn-outline-secondary copy-sign-link">Copy</button>
                                            </div>
                                            <a class="btn btn-sm btn-outline-primary mt-1" href="<?= htmlspecialchars($signUrl) ?>">Open sign page</a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <form method="post" action="<?= htmlspecialchars($basePath) ?>/iep/finalize-digital" class="mt-3">
                        <input type="hidden" name="iep_id" value="<?= (int) $iep['id'] ?>">
                        <button type="submit" class="btn btn-success" <?= !empty($allSignaturesCaptured) && $allSignaturesCaptured ? '' : 'disabled' ?>>
                            <i class="bi bi-check2-all me-1"></i>Finalize IEP (all signatures received)
                        </button>
                        <?php if (empty($allSignaturesCaptured) || !$allSignaturesCaptured): ?>
                            <span class="text-muted small ms-2">Enabled when every signatory row is complete.</span>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <script>
            document.querySelectorAll('.copy-sign-link').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var inp = this.closest('.input-group').querySelector('input');
                    if (!inp) return;
                    inp.select();
                    document.execCommand('copy');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Link copied', showConfirmButton: false, timer: 2000 });
                    }
                });
            });
            </script>
            <?php endif; ?>

            <?php
            if (!empty($iepEditLogs) && in_array($userRole ?? '', ['sped_teacher', 'admin'], true)) {
                require __DIR__ . '/partials/iep_form_edit_history.php';
            }
            ?>

        </div>

    </div><!-- /row -->
</div><!-- /container-fluid -->
</div><!-- /main-content -->

<?php require __DIR__ . '/partials/iep_pdsp_reference_drawer.php'; ?>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:white;">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>IEP Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="min-height:500px;">
                <div id="documentViewer" class="text-center p-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var evInp = document.getElementById('iepReEvalDateInput');
    var evHid = document.getElementById('reEvalHiddenForSubmit');
    function syncReEvalToSubmit() {
        if (evInp && evHid) {
            evHid.value = evInp.value;
        }
    }
    if (evInp) {
        evInp.addEventListener('input', syncReEvalToSubmit);
        evInp.addEventListener('change', syncReEvalToSubmit);
    }
    syncReEvalToSubmit();

    // ── Signatory checkbox toggle ──────────────────────────────
    document.querySelectorAll('.signatory-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const nameInput = this.closest('.card-body').querySelector('.signatory-name');
            if (!nameInput) return;
            nameInput.disabled = !this.checked;
            if (!this.checked) nameInput.value = '';
            else nameInput.focus();
        });
    });

    // Meeting record vs digital — show F2F proof upload only for meeting record
    function syncF2fProofVisibility() {
        var wrap = document.getElementById('f2fSigningProofWrap');
        var meet = document.getElementById('finMeet');
        if (!wrap || !meet) return;
        wrap.style.display = meet.checked ? '' : 'none';
    }
    var finMeetEl = document.getElementById('finMeet');
    var finDigEl = document.getElementById('finDig');
    if (finMeetEl) finMeetEl.addEventListener('change', syncF2fProofVisibility);
    if (finDigEl) finDigEl.addEventListener('change', syncF2fProofVisibility);
    syncF2fProofVisibility();

    var camInp = document.getElementById('meetingSigningProofCam');
    var mainInp = document.getElementById('meetingSigningProof');
    var takeBtn = document.getElementById('meetingProofTakePhotoBtn');
    if (takeBtn && camInp && mainInp) {
        takeBtn.addEventListener('click', function () { camInp.click(); });
        camInp.addEventListener('change', function () {
            if (!camInp.files || !camInp.files[0]) return;
            try {
                var dt = new DataTransfer();
                dt.items.add(camInp.files[0]);
                mainInp.files = dt.files;
            } catch (e) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'info', title: 'Use Choose file', text: 'Pick the photo from your gallery using Choose file.', confirmButtonColor: '#1e4072' });
                } else {
                    alert('Please use Choose file and pick the photo from your gallery.');
                }
            }
            camInp.value = '';
        });
    }

    // ── Form submit validation ─────────────────────────────────
    const form = document.getElementById('iepForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            var evInp2 = document.getElementById('iepReEvalDateInput');
            var evHid2 = document.getElementById('reEvalHiddenForSubmit');
            if (evInp2 && evHid2) {
                evHid2.value = evInp2.value;
            }
            const errors = [];
            var domainCount = <?= (int) count($iepDomains ?? []) ?>;
            if (domainCount < 1) {
                errors.push('Add at least one developmental domain in Sections 3–4 (use Save there first if you just added tags).');
            }
            if (!<?= !empty($hasStepObjective) ? 'true' : 'false' ?>) {
                errors.push('Fill in at least one step objective in Section 5 (use Save steps if needed).');
            }

            // Re-evaluation date (mirrored from Section 4 hidden field)
            const reEval = document.getElementById('reEvalHiddenForSubmit');
            if (reEval && !reEval.value) {
                errors.push('Please set a re-evaluation date in Section 4 above, then save or sync before submitting.');
            }

            // 3. At least one signatory with a name
            const checked = document.querySelectorAll('.signatory-checkbox:checked');
            let hasValidSig = false;
            checked.forEach(function (cb) {
                const nameInput = cb.closest('.card-body').querySelector('.signatory-name');
                if (nameInput && nameInput.value.trim()) hasValidSig = true;
            });
            if (!hasValidSig) {
                errors.push('Please select at least one signatory and enter their name.');
            }

            if (errors.length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Please fix the following',
                    html: '<ul class="text-start mb-0">' + errors.map(function(e){ return '<li>' + e + '</li>'; }).join('') + '</ul>',
                    confirmButtonColor: '#a01422'
                });
                return false;
            }

            // Confirmation
            e.preventDefault();
            var digEl = document.getElementById('finDig');
            var useDigital = digEl && digEl.checked;
            Swal.fire({
                title: useDigital ? 'Send the IEP for digital signatures?' : 'Mark the IEP as signed (meeting record)?',
                html: useDigital
                    ? 'Invitees get an in-app notification (and email when available) with a link to sign. You must return here and press <strong>Finalize IEP</strong> after everyone signs.'
                    : 'Guidance, Principal, and Parent will be notified. You can keep editing the living document after signing; changes are logged.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#a01422',
                cancelButtonColor: '#1e4072',
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});

function viewDocument(path, type) {
    if (type === 'pdf') {
        // Avoid <embed>/<iframe> PDF viewers: Chrome may replace the child document
        // with chrome-error://… and then block further navigations (and embedded
        // IDE browsers report "Domains, protocols and ports must match").
        var w = window.open(path, '_blank', 'noopener,noreferrer');
        if (!w || w.closed) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Pop-up blocked',
                    html: 'Allow pop-ups for this site, or use <strong>Download</strong> to open the PDF.',
                    confirmButtonColor: '#a01422'
                });
            } else {
                alert('Pop-up blocked. Allow pop-ups for this site or use Download to open the PDF.');
            }
        }
        return;
    }
    const viewer = document.getElementById('documentViewer');
    if (!viewer) return;

    viewer.innerHTML = '<img src="' + path.replace(/"/g, '&quot;') + '" class="img-fluid" alt="IEP Document" style="max-height:80vh;">';

    new bootstrap.Modal(document.getElementById('documentModal')).show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
