<?php
/**
 * IEP Form — Section 5: IEP Steps (max 10)
 * Crimson #a01422 / Navy #1e4072
 */
$wsUrl = $basePath . '/iep/implementation/workspace/' . (int) $iep['id'];

?>
<div class="card shadow-sm mb-3 iep-steps-compact" id="iepSection5Steps">
    <div class="card-header text-white py-2" style="background-color:#1e4072;">
        <h5 class="mb-0 fs-6"><i class="bi bi-list-ol me-2"></i>IEP steps</h5>
    </div>
    <div class="card-body p-2 p-md-3">
        <?php if ($readOnly): ?>
            <div class="alert alert-secondary small mb-3">This section is read-only.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 table-sm" id="iepStepsTable" style="--bs-table-border-color:#dee2e6;font-size:0.8125rem;">
                <thead>
                    <tr style="background-color:#1e4072;color:#fff;">
                        <th style="width:44px;">#</th>
                        <th style="min-width:200px;">Step objectives</th>
                        <th style="min-width:160px;">Observation</th>
                        <th style="min-width:200px;">Plan of activities / lesson plan</th>
                        <th style="min-width:160px;">Materials</th>
                        <th style="min-width:160px;">Instructional evaluation</th>
                        <th style="min-width:120px;">Duration of LP</th>
                    </tr>
                </thead>
                <tbody id="iepStepsTbody">
                    <?php foreach ($iepSteps as $st): ?>
                        <?php
                        $sid = (int) $st['id'];
                        $obsUnlocked = (int) $st['observation_unlocked'] === 1;
                        $lps = $st['lesson_plans'] ?? [];
                        $mats = $st['materials'] ?? [];
                        $objective = (string) ($st['step_objective'] ?? '');
                        $durationLp = (string) ($st['duration_lp'] ?? '');
                        $evaluation = (string) ($st['instructional_evaluation'] ?? '');
                        $obsText = (string) ($st['observation'] ?? '');
                        ?>
                        <tr data-step-id="<?= $sid ?>" style="background-color:<?= ((int) $st['step_number'] % 2 === 0) ? '#f9f9f9' : '#fff' ?>;">
                            <td class="text-center step-num-cell"><?= (int) $st['step_number'] ?></td>
                            <td>
                                <?php if ($readOnly): ?>
                                    <div><?= nl2br(htmlspecialchars($objective)) ?></div>
                                <?php else: ?>
                                    <textarea class="form-control form-control-sm step-objective" rows="2" name="step_objective[]" placeholder="Enabling objective"><?= htmlspecialchars($objective) ?></textarea>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($readOnly): ?>
                                    <?php if ($obsUnlocked): ?>
                                        <?= nl2br(htmlspecialchars($obsText)) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Available after implementation begins</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($obsUnlocked): ?>
                                        <textarea class="form-control form-control-sm step-observation" rows="2" name="step_observation[]"><?= htmlspecialchars($obsText) ?></textarea>
                                    <?php else: ?>
                                        <div class="small text-muted mb-1 p-2 rounded" style="background:#f9f9f9;border-left:3px solid #1e4072;">Available after implementation begins</div>
                                        <textarea class="form-control form-control-sm step-observation d-none" rows="1" readonly aria-hidden="true"><?= htmlspecialchars($obsText) ?></textarea>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <ul class="list-unstyled mb-1 lp-list">
                                    <?php foreach ($lps as $lp): ?>
                                        <?php
                                        $lpDoc = trim((string) ($lp['document_path'] ?? ''));
                                        $viewHref = '';
                                        if ($lpDoc !== '') {
                                            $viewHref = rtrim((string) $basePath, '/') . '/' . ltrim(str_replace('\\', '/', $lpDoc), '/');
                                        }
                                        ?>
                                        <li class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                            <a href="<?= htmlspecialchars($wsUrl) ?>#lp-<?= (int) $lp['id'] ?>" target="_blank" rel="noopener" style="color:#1e4072;"><?= htmlspecialchars($lp['title'] ?: ('LP #' . (int) $lp['id'])) ?></a>
                                            <span class="badge small" style="background:<?= ($lp['status'] ?? '') === 'published' ? '#3b6d11' : '#c49a1a' ?>;color:#fff;"><?= htmlspecialchars($lp['status'] ?? '') ?></span>
                                            <?php if (($lp['status'] ?? '') === 'published' && $viewHref !== ''): ?>
                                                <a class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:0.7rem;" href="<?= htmlspecialchars($viewHref) ?>" target="_blank" rel="noopener">View</a>
                                            <?php endif; ?>
                                            <?php if (!$readOnly): ?>
                                                <button type="button" class="btn btn-link btn-sm p-0 btn-unlink-lp" style="color:#a01422;" data-lp-id="<?= (int) $lp['id'] ?>" title="Unlink">×</button>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if (!$readOnly): ?>
                                    <button type="button" class="btn btn-sm btn-open-lp-drawer mb-1" data-step-id="<?= $sid ?>"
                                            style="background:#a01422;color:#fff;border:none;">
                                        <i class="bi bi-plus-lg me-1"></i>Create lesson plan
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?php if (empty($mats)): ?>
                                    <div class="text-muted mb-1"><?= empty($lps) ? 'No materials yet — link a lesson plan to this step, then add materials in the workspace.' : 'No materials on linked lesson plans yet.' ?></div>
                                    <?php if (!$readOnly): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($wsUrl) ?>" target="_blank" rel="noopener">Go to workspace</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <ul class="list-unstyled mb-1">
                                        <?php foreach ($mats as $m): ?>
                                            <li>
                                                <?php if (!empty($m['file_path'])): ?>
                                                    <a href="<?= $basePath ?>/file/download/lesson_material/<?= (int) $m['id'] ?>" target="_blank" rel="noopener" style="color:#1e4072;"><?= htmlspecialchars($m['title'] ?: 'Material') ?></a>
                                                <?php elseif (!empty($m['external_url'])): ?>
                                                    <a href="<?= htmlspecialchars($m['external_url']) ?>" target="_blank" rel="noopener" style="color:#1e4072;"><?= htmlspecialchars($m['title'] ?: 'Link') ?></a>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($m['title'] ?: 'Material') ?>
                                                <?php endif; ?>
                                                <span class="badge small" style="background:#1e4072;color:#fff;"><?= htmlspecialchars($m['material_type'] ?? '') ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php if (!$readOnly): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($wsUrl) ?>" target="_blank" rel="noopener">Go to workspace →</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($readOnly): ?>
                                    <?= nl2br(htmlspecialchars($evaluation)) ?>
                                    <button type="button" class="btn btn-sm btn-step-progress mt-1" data-step-id="<?= $sid ?>"
                                            style="border:1px solid #1e4072;color:#1e4072;background:#fff;width:100%;">
                                        View progress
                                    </button>
                                <?php else: ?>
                                    <textarea class="form-control form-control-sm step-evaluation" rows="2" name="step_evaluation[]"><?= htmlspecialchars($evaluation) ?></textarea>
                                    <button type="button" class="btn btn-sm btn-step-progress mt-1" data-step-id="<?= $sid ?>"
                                            style="border:1px solid #1e4072;color:#1e4072;background:#fff;width:100%;">
                                        View progress
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($readOnly): ?>
                                    <?= nl2br(htmlspecialchars($durationLp)) ?>
                                <?php else: ?>
                                    <textarea class="form-control form-control-sm step-strategies" rows="2" name="step_strategies[]" placeholder="e.g. 4 weeks"><?= htmlspecialchars($durationLp) ?></textarea>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!$readOnly): ?>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="iepStepsAddRow"><i class="bi bi-plus-lg me-1"></i>Add step</button>
                <button type="button" class="btn btn-sm text-white" style="background-color:#a01422;" id="iepStepsSaveBtn"><i class="bi bi-save me-1"></i>Save steps</button>
            </div>
            <input type="hidden" name="steps_json" id="iepStepsJsonPayload" value="">
        <?php endif; ?>
    </div>
</div>

<?php if (!$readOnly): ?>
<!-- Lesson plan: create / link (modal) -->
<div class="modal fade" id="iepLpModal" tabindex="-1" aria-labelledby="iepLpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white py-2" style="background-color:#1e4072;">
                <h6 class="modal-title mb-0" id="iepLpModalLabel">Lesson plan for this step</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="iepLpDrawerStepId" value="">
                <input type="hidden" id="iepLpDrawerTargetStepNumber" value="">

                <ul class="nav nav-pills gap-2 mb-2 small flex-nowrap" role="tablist" id="iepLpDrawerModeNav">
                    <li class="nav-item flex-fill">
                        <button type="button" class="nav-link w-100 active fw-semibold py-2" id="iepLpTabCreate"
                                style="border:2px solid #1e4072;color:#1e4072;background:#fff;">Create new</button>
                    </li>
                    <li class="nav-item flex-fill">
                        <button type="button" class="nav-link w-100 fw-semibold py-2" id="iepLpTabLink"
                                style="border:2px solid #1e4072;color:#1e4072;background:#fff;">Link existing</button>
                    </li>
                </ul>

                <div id="iepLpPaneCreate">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-0" style="color:#1e4072;">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="iepLpDrawerTitle" placeholder="Lesson plan title" autocomplete="off">
                    </div>
                    <div class="border rounded p-2 mb-2" style="border-color:#1e4072!important;">
                        <div class="small fw-semibold mb-1" style="color:#1e4072;"><i class="bi bi-file-earmark-word me-1"></i>Use a DepEd template</div>
                        <p class="small text-muted mb-2 mb-0">Download a DLL or DLP (.docx), complete it offline, then optionally attach the scanned or exported PDF below before you create.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($basePath . '/iep/implementation/template/dll', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                <i class="bi bi-download me-1"></i>DLL template
                            </a>
                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($basePath . '/iep/implementation/template/dlp', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                <i class="bi bi-download me-1"></i>DLP template
                            </a>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-0" for="iepLpCreateOptionalFile" style="color:#1e4072;">Attach completed plan <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="file" class="form-control form-control-sm" id="iepLpCreateOptionalFile" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*">
                        <small class="text-muted">PDF or image after you fill the template. You can skip this and upload later from the workspace.</small>
                    </div>
                    <button type="button" class="btn btn-sm text-white w-100" style="background-color:#a01422;" id="iepLpDrawerSubmit"><i class="bi bi-check-lg me-1"></i>Create &amp; link</button>
                </div>

                <div id="iepLpPaneLink" class="d-none">
                    <div class="border rounded p-2 mb-3" style="border-color:#a01422;border-width:2px!important;">
                        <div class="small fw-semibold mb-2" style="color:#1e4072;"><i class="bi bi-cloud-upload me-1"></i>Upload an existing lesson plan</div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-0" for="iepLpLinkUploadTitle" style="color:#1e4072;">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="iepLpLinkUploadTitle" placeholder="Lesson plan title" autocomplete="off">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-0" for="iepLpLinkUploadFile" style="color:#1e4072;">File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" id="iepLpLinkUploadFile" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*">
                            <small class="text-muted">PDF or image, max 10MB.</small>
                        </div>
                        <button type="button" class="btn btn-sm text-white w-100" style="background-color:#a01422;" id="iepLpLinkUploadSubmit"><i class="bi bi-cloud-upload me-1"></i>Upload &amp; link</button>
                    </div>
                    <hr class="my-3">
                    <p class="small fw-semibold mb-1" style="color:#1e4072;">Or link a plan already on this IEP</p>
                    <p class="small text-muted mb-2">These lesson plans are not linked to this step yet.</p>
                    <label class="form-label small fw-semibold" style="color:#1e4072;">Search</label>
                    <input type="search" class="form-control form-control-sm mb-2" id="iepLpPickFilter" placeholder="Filter by title…" autocomplete="off">
                    <label class="form-label small fw-semibold" style="color:#1e4072;">Lesson plan</label>
                    <select class="form-select form-select-sm" id="iepLpPickSelect" size="6" style="min-height:160px;"></select>
                    <button type="button" class="btn btn-sm text-white w-100 mt-2" style="background-color:#1e4072;" id="iepLpDrawerLinkSubmit"><i class="bi bi-link-45deg me-1"></i>Link to this step</button>
                </div>

                <div id="iepLpDrawerMsg" class="small mt-2 text-danger"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Progress offcanvas (read for teacher/parent with iep.view) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="iepStepProgressOffcanvas" style="width:420px;">
    <div class="offcanvas-header text-white" style="background-color:#1e4072;">
        <h6 class="offcanvas-title mb-0">Step progress</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div id="iepStepProgressBody" class="small text-muted">Select a step and tap View progress.</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
(function(){
    if (typeof bootstrap === 'undefined') {
        console.error('IEP Section 5: Bootstrap JS not loaded; step buttons will not work.');
        return;
    }
    var iepId = <?= (int) $iep['id'] ?>;
    var basePath = <?= json_encode($basePath) ?>;
    var readOnly = <?= $readOnly ? 'true' : 'false' ?>;
    var wsUrl = <?= json_encode($wsUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var lpPickerCache = [];

    function setActiveLpTab(mode) {
        var tCreate = document.getElementById('iepLpTabCreate');
        var tLink = document.getElementById('iepLpTabLink');
        var pCreate = document.getElementById('iepLpPaneCreate');
        var pLink = document.getElementById('iepLpPaneLink');
        if (!tCreate || !tLink || !pCreate || !pLink) return;
        var isCreate = mode === 'create';
        tCreate.classList.toggle('active', isCreate);
        tLink.classList.toggle('active', !isCreate);
        tCreate.style.background = isCreate ? '#a01422' : '#fff';
        tCreate.style.color = isCreate ? '#fff' : '#1e4072';
        tCreate.style.borderColor = isCreate ? '#a01422' : '#1e4072';
        tLink.style.background = !isCreate ? '#a01422' : '#fff';
        tLink.style.color = !isCreate ? '#fff' : '#1e4072';
        tLink.style.borderColor = !isCreate ? '#a01422' : '#1e4072';
        pCreate.classList.toggle('d-none', !isCreate);
        pLink.classList.toggle('d-none', isCreate);
        if (!isCreate) {
            loadLessonPlanOptions();
        }
    }

    function applyLpPickerFilter() {
        var filtEl = document.getElementById('iepLpPickFilter');
        var q = filtEl ? (filtEl.value || '').toLowerCase().trim() : '';
        var sel = document.getElementById('iepLpPickSelect');
        if (!sel) return;
        sel.innerHTML = '';
        var n = 0;
        lpPickerCache.forEach(function (lp) {
            var title = (lp.title || '').toLowerCase();
            if (q && title.indexOf(q) === -1) return;
            var opt = document.createElement('option');
            opt.value = String(lp.id);
            opt.textContent = (lp.title || ('LP #' + lp.id)) + ' — ' + (lp.status || '') + ' — ' + (lp.domain_label || '');
            sel.appendChild(opt);
            n++;
        });
        if (n === 0) {
            var o = document.createElement('option');
            o.value = '';
            o.disabled = true;
            o.textContent = lpPickerCache.length ? 'No matches. Try another search.' : 'No other plans to link — use Upload above or Create new (template).';
            sel.appendChild(o);
        }
    }

    function loadLessonPlanOptions() {
        var sid = parseInt(document.getElementById('iepLpDrawerStepId').value || '0', 10) || 0;
        var msg = document.getElementById('iepLpDrawerMsg');
        if (msg) msg.textContent = '';
        fetch(basePath + '/iep/ajax/lesson-plans-for-step?iep_id=' + iepId + '&step_id=' + sid, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.success) {
                    if (msg) msg.textContent = j.message || 'Could not load lesson plans.';
                    lpPickerCache = [];
                    applyLpPickerFilter();
                    return;
                }
                lpPickerCache = j.lesson_plans || [];
                applyLpPickerFilter();
            }).catch(function () {
                if (msg) msg.textContent = 'Network error loading lesson plans.';
                lpPickerCache = [];
                applyLpPickerFilter();
            });
    }

    function renumberRows() {
        var rows = document.querySelectorAll('#iepStepsTbody tr');
        rows.forEach(function(tr, idx) {
            var c = tr.querySelector('.step-num-cell');
            if (c) c.textContent = String(idx + 1);
        });
    }

    function gatherStepsPayload() {
        var rows = document.querySelectorAll('#iepStepsTbody tr');
        var out = [];
        rows.forEach(function(tr, idx) {
            var id = parseInt(tr.getAttribute('data-step-id') || '0', 10) || 0;
            out.push({
                id: id,
                step_number: idx + 1,
                domain: '',
                objective: tr.querySelector('.step-objective') ? tr.querySelector('.step-objective').value : '',
                strategies: tr.querySelector('.step-strategies') ? tr.querySelector('.step-strategies').value : '',
                evaluation: tr.querySelector('.step-evaluation') ? tr.querySelector('.step-evaluation').value : '',
                observation: (function () {
                    var ta = tr.querySelector('.step-observation');
                    if (!ta || ta.classList.contains('d-none')) {
                        return '';
                    }
                    return ta.value || '';
                })()
            });
        });
        return out;
    }

    function stepNumberFromTr(tr) {
        var tbody = document.getElementById('iepStepsTbody');
        if (!tbody || !tr) return 1;
        var rows = tbody.querySelectorAll('tr');
        for (var i = 0; i < rows.length; i++) {
            if (rows[i] === tr) return i + 1;
        }
        return 1;
    }

    function applyStepIdsFromResponse(steps) {
        if (!steps || !steps.length) return;
        var tbody = document.getElementById('iepStepsTbody');
        if (!tbody) return;
        var trs = tbody.querySelectorAll('tr');
        steps.forEach(function (s) {
            var idx = (parseInt(s.step_number, 10) || 0) - 1;
            if (idx >= 0 && trs[idx]) {
                trs[idx].setAttribute('data-step-id', String(s.id));
            }
        });
    }

    var progressOffEl = document.getElementById('iepStepProgressOffcanvas');
    var progressOff = progressOffEl ? bootstrap.Offcanvas.getOrCreateInstance(progressOffEl) : null;

    function bindStepRow(tr) {
        tr.querySelectorAll('.btn-open-lp-drawer').forEach(function(btn){
            btn.addEventListener('click', function(){
                var sid = parseInt(tr.getAttribute('data-step-id')||'0',10)||0;
                document.getElementById('iepLpDrawerStepId').value = String(sid);
                document.getElementById('iepLpDrawerTargetStepNumber').value = String(stepNumberFromTr(tr));
                document.getElementById('iepLpDrawerTitle').value = '';
                var msgEl = document.getElementById('iepLpDrawerMsg');
                if (msgEl) msgEl.textContent = '';
                var fileCreate = document.getElementById('iepLpCreateOptionalFile');
                if (fileCreate) fileCreate.value = '';
                var linkTitle = document.getElementById('iepLpLinkUploadTitle');
                if (linkTitle) linkTitle.value = '';
                var linkFile = document.getElementById('iepLpLinkUploadFile');
                if (linkFile) linkFile.value = '';
                var pf = document.getElementById('iepLpPickFilter');
                if (pf) pf.value = '';
                setActiveLpTab('create');
                var modalEl = document.getElementById('iepLpModal');
                if (!modalEl) return;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        });
        tr.querySelectorAll('.btn-unlink-lp').forEach(function(btn){
            btn.addEventListener('click', function(){
                var lpId = parseInt(btn.getAttribute('data-lp-id')||'0',10);
                var sid = parseInt(tr.getAttribute('data-step-id')||'0',10)||0;
                var fd = new FormData();
                fd.append('iep_id', String(iepId));
                fd.append('step_id', String(sid));
                fd.append('lesson_plan_id', String(lpId));
                fetch(basePath + '/iep/ajax/unlink-lesson-plan', {method:'POST', body: fd, credentials:'same-origin'})
                    .then(function(r){ return r.json(); })
                    .then(function(j){
                        if (j.success) location.reload();
                        else alert(j.message || 'Failed to unlink');
                    }).catch(function(){ alert('Network error'); });
            });
        });
        tr.querySelectorAll('.btn-step-progress').forEach(function(btn){
            btn.addEventListener('click', function(){
                var trProg = btn.closest('tr');
                var sid = parseInt(trProg.getAttribute('data-step-id') || btn.getAttribute('data-step-id') || '0', 10) || 0;
                var body = document.getElementById('iepStepProgressBody');
                body.innerHTML = '<div class="text-muted">Loading…</div>';
                if (progressOff) progressOff.show();

                function loadProgress(progressSid) {
                    fetch(basePath + '/iep/ajax/step-progress/' + progressSid + '?iep_id=' + iepId, {credentials:'same-origin'})
                        .then(function(r){ return r.json(); })
                        .then(function(j){
                            if (!j.success) { body.innerHTML = '<div class="text-danger">'+(j.message||'Failed')+'</div>'; return; }
                            var rows = j.rows || [];
                            if (!rows.length) { body.innerHTML = '<div class="text-muted">No submissions yet.</div>'; return; }
                            var html = '<ul class="list-group list-group-flush">';
                            rows.forEach(function(r){
                                html += '<li class="list-group-item small">'+
                                    '<div><strong>'+(r.submitted_at||'')+'</strong> — '+(r.status||'')+'</div>'+
                                    (r.notes ? '<div class="mt-1">'+String(r.notes).replace(/</g,'&lt;')+'</div>' : '')+
                                    '</li>';
                            });
                            html += '</ul>';
                            body.innerHTML = html;
                        }).catch(function(){ body.innerHTML = '<div class="text-danger">Network error</div>'; });
                }

                if (sid <= 0) {
                    var fd0 = new FormData();
                    fd0.append('iep_id', String(iepId));
                    fd0.append('steps_json', JSON.stringify(gatherStepsPayload()));
                    fetch(basePath + '/iep/save-steps', {method:'POST', body: fd0, credentials:'same-origin'})
                        .then(function(r){ return r.json(); })
                        .then(function(j){
                            if (!j.success) {
                                body.innerHTML = '<div class="text-danger">'+(j.message||'Could not save steps')+'</div>';
                                return;
                            }
                            applyStepIdsFromResponse(j.steps || []);
                            var tnum = stepNumberFromTr(trProg);
                            var newId = 0;
                            (j.steps || []).forEach(function(s) {
                                if ((parseInt(s.step_number, 10) || 0) === tnum) newId = parseInt(s.id, 10) || 0;
                            });
                            if (newId <= 0) {
                                body.innerHTML = '<div class="text-danger">Could not resolve this step.</div>';
                                return;
                            }
                            loadProgress(newId);
                        }).catch(function(){ body.innerHTML = '<div class="text-danger">Network error</div>'; });
                    return;
                }
                loadProgress(sid);
            });
        });
    }

    if (!readOnly) {
        var addRowBtn = document.getElementById('iepStepsAddRow');
        if (addRowBtn) addRowBtn.addEventListener('click', function() {
            var tbody = document.getElementById('iepStepsTbody');
            if (tbody.querySelectorAll('tr').length >= 10) {
                if (typeof Swal !== 'undefined') Swal.fire({icon:'info',title:'Limit reached',text:'Maximum of 10 steps.'});
                return;
            }
            var tr = document.createElement('tr');
            tr.setAttribute('data-step-id', '0');
            tr.style.backgroundColor = (tbody.querySelectorAll('tr').length % 2 === 0) ? '#f9f9f9' : '#fff';
            tr.innerHTML =
                '<td class="text-center step-num-cell"></td>'+
                '<td><textarea class="form-control form-control-sm step-objective" rows="2" name="step_objective[]" placeholder="Enabling objective"></textarea></td>'+
                '<td><div class="small text-muted mb-1 p-2 rounded" style="background:#f9f9f9;border-left:3px solid #1e4072;">Available after implementation begins</div>'+
                '<textarea class="form-control form-control-sm step-observation d-none" rows="1" readonly aria-hidden="true"></textarea></td>'+
                '<td class="small"><ul class="list-unstyled mb-1 lp-list"></ul>'+
                '<button type="button" class="btn btn-sm btn-open-lp-drawer mb-1" style="background:#a01422;color:#fff;border:none;"><i class="bi bi-plus-lg me-1"></i>Create lesson plan</button></td>'+
                '<td class="small"><div class="text-muted mb-1">No materials yet — link a lesson plan, then add materials in the workspace.</div><a class="btn btn-sm btn-outline-primary" href="'+wsUrl+'" target="_blank" rel="noopener">Go to workspace</a></td>'+
                '<td><textarea class="form-control form-control-sm step-evaluation" rows="2" name="step_evaluation[]"></textarea>'+
                '<button type="button" class="btn btn-sm btn-step-progress mt-1" style="border:1px solid #1e4072;color:#1e4072;background:#fff;width:100%;">View progress</button></td>'+
                '<td><textarea class="form-control form-control-sm step-strategies" rows="2" name="step_strategies[]" placeholder="e.g. 4 weeks"></textarea></td>';
            tbody.appendChild(tr);
            renumberRows();
            bindStepRow(tr);
        });

        document.querySelectorAll('#iepStepsTbody tr').forEach(bindStepRow);

        var drawerSubmit = document.getElementById('iepLpDrawerSubmit');
        if (drawerSubmit) drawerSubmit.addEventListener('click', function(){
            var sid = parseInt(document.getElementById('iepLpDrawerStepId').value||'0',10)||0;
            var title = document.getElementById('iepLpDrawerTitle').value.trim();
            var msgEl = document.getElementById('iepLpDrawerMsg');
            if (msgEl) msgEl.textContent = '';
            if (!title) { if (msgEl) msgEl.textContent = 'Title is required.'; return; }
            var fileEl = document.getElementById('iepLpCreateOptionalFile');
            var file = (fileEl && fileEl.files && fileEl.files[0]) ? fileEl.files[0] : null;
            var fd = new FormData();
            fd.append('iep_id', String(iepId));
            fd.append('step_id', String(sid));
            fd.append('title', title);
            fd.append('domain', '');
            fd.append('description', '');
            if (sid <= 0) {
                var tsn = parseInt(document.getElementById('iepLpDrawerTargetStepNumber').value || '0', 10) || 0;
                if (tsn < 1) tsn = 1;
                fd.append('steps_json', JSON.stringify(gatherStepsPayload()));
                fd.append('target_step_number', String(tsn));
            }
            fetch(basePath + '/iep/ajax/lesson-plan-for-step', {method:'POST', body: fd, credentials:'same-origin'})
                .then(function(r){ return r.json(); })
                .then(function(j){
                    if (!j.success) {
                        if (msgEl) msgEl.textContent = j.message || 'Failed';
                        return;
                    }
                    var lpId = parseInt(j.lesson_plan_id || '0', 10) || 0;
                    if (!file || lpId <= 0) {
                        location.reload();
                        return;
                    }
                    var ufd = new FormData();
                    ufd.append('iep_id', String(iepId));
                    ufd.append('lesson_plan_id', String(lpId));
                    ufd.append('document', file);
                    return fetch(basePath + '/iep/ajax/lesson-plan-upload', {method:'POST', body: ufd, credentials:'same-origin'})
                        .then(function(r2){ return r2.json(); })
                        .then(function(j2){
                            if (!j2.success) {
                                if (msgEl) msgEl.textContent = (j2.message || 'Lesson plan created but upload failed.') + ' You can upload from the workspace.';
                                return;
                            }
                            location.reload();
                        });
                }).catch(function(){ if (msgEl) msgEl.textContent = 'Network error'; });
        });

        function submitLinkTabUpload() {
            var sid = parseInt(document.getElementById('iepLpDrawerStepId').value || '0', 10) || 0;
            var titleEl = document.getElementById('iepLpLinkUploadTitle');
            var fileEl = document.getElementById('iepLpLinkUploadFile');
            var msgEl = document.getElementById('iepLpDrawerMsg');
            if (msgEl) msgEl.textContent = '';
            var title = titleEl ? titleEl.value.trim() : '';
            var file = (fileEl && fileEl.files && fileEl.files[0]) ? fileEl.files[0] : null;
            if (!title) {
                if (msgEl) msgEl.textContent = 'Enter a title for this lesson plan.';
                return;
            }
            if (!file) {
                if (msgEl) msgEl.textContent = 'Choose a PDF or image file.';
                return;
            }
            var fd = new FormData();
            fd.append('iep_id', String(iepId));
            fd.append('step_id', String(sid));
            fd.append('title', title);
            fd.append('domain', '');
            fd.append('description', '');
            if (sid <= 0) {
                var tsnU = parseInt(document.getElementById('iepLpDrawerTargetStepNumber').value || '0', 10) || 0;
                if (tsnU < 1) tsnU = 1;
                fd.append('steps_json', JSON.stringify(gatherStepsPayload()));
                fd.append('target_step_number', String(tsnU));
            }
            fetch(basePath + '/iep/ajax/lesson-plan-for-step', {method: 'POST', body: fd, credentials: 'same-origin'})
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j.success) {
                        if (msgEl) msgEl.textContent = j.message || 'Could not create lesson plan.';
                        return;
                    }
                    var lpId = parseInt(j.lesson_plan_id || '0', 10) || 0;
                    if (lpId <= 0) {
                        if (msgEl) msgEl.textContent = 'Could not resolve new lesson plan.';
                        return;
                    }
                    var ufd = new FormData();
                    ufd.append('iep_id', String(iepId));
                    ufd.append('lesson_plan_id', String(lpId));
                    ufd.append('document', file);
                    return fetch(basePath + '/iep/ajax/lesson-plan-upload', {method: 'POST', body: ufd, credentials: 'same-origin'})
                        .then(function (r2) { return r2.json(); })
                        .then(function (j2) {
                            if (!j2.success) {
                                if (msgEl) msgEl.textContent = (j2.message || 'Created plan but upload failed.') + ' You can retry from the workspace.';
                                return;
                            }
                            location.reload();
                        });
                }).catch(function () { if (msgEl) msgEl.textContent = 'Network error'; });
        }

        var linkUploadBtn = document.getElementById('iepLpLinkUploadSubmit');
        if (linkUploadBtn) linkUploadBtn.addEventListener('click', submitLinkTabUpload);

        var tabCreate = document.getElementById('iepLpTabCreate');
        var tabLink = document.getElementById('iepLpTabLink');
        if (tabCreate) tabCreate.addEventListener('click', function () { setActiveLpTab('create'); });
        if (tabLink) tabLink.addEventListener('click', function () { setActiveLpTab('link'); });
        var pickFilter = document.getElementById('iepLpPickFilter');
        if (pickFilter) pickFilter.addEventListener('input', applyLpPickerFilter);

        var linkSubmit = document.getElementById('iepLpDrawerLinkSubmit');
        if (linkSubmit) linkSubmit.addEventListener('click', function () {
            var sid = parseInt(document.getElementById('iepLpDrawerStepId').value || '0', 10) || 0;
            var sel = document.getElementById('iepLpPickSelect');
            var lpId = sel ? (parseInt(sel.value || '0', 10) || 0) : 0;
            var msg = document.getElementById('iepLpDrawerMsg');
            if (msg) msg.textContent = '';
            if (!lpId) {
                if (msg) msg.textContent = 'Select a lesson plan from the list.';
                return;
            }
            var fd = new FormData();
            fd.append('iep_id', String(iepId));
            fd.append('step_id', String(sid));
            fd.append('lesson_plan_id', String(lpId));
            if (sid <= 0) {
                var tsn2 = parseInt(document.getElementById('iepLpDrawerTargetStepNumber').value || '0', 10) || 0;
                if (tsn2 < 1) tsn2 = 1;
                fd.append('steps_json', JSON.stringify(gatherStepsPayload()));
                fd.append('target_step_number', String(tsn2));
            }
            fetch(basePath + '/iep/ajax/link-lesson-plan-to-step', {method: 'POST', body: fd, credentials: 'same-origin'})
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j.success) {
                        location.reload();
                    } else if (msg) {
                        msg.textContent = j.message || 'Failed to link';
                    }
                }).catch(function () {
                    if (msg) msg.textContent = 'Network error';
                });
        });

        setActiveLpTab('create');

        var saveStepsBtn = document.getElementById('iepStepsSaveBtn');
        if (saveStepsBtn) saveStepsBtn.addEventListener('click', function(){
            var payload = gatherStepsPayload();
            document.getElementById('iepStepsJsonPayload').value = JSON.stringify(payload);
            var fd = new FormData();
            fd.append('iep_id', String(iepId));
            fd.append('steps_json', JSON.stringify(payload));
            fetch(basePath + '/iep/save-steps', {method:'POST', body: fd, credentials:'same-origin'})
                .then(function(r){ return r.json(); })
                .then(function(j){
                    if (j.success) {
                        if (typeof Swal !== 'undefined') Swal.fire({icon:'success',title:'Saved',timer:1200,showConfirmButton:false});
                        location.reload();
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire({icon:'error',title:'Error',text:j.message||'Save failed'});
                        else alert(j.message||'Save failed');
                    }
                }).catch(function(){
                    if (typeof Swal !== 'undefined') Swal.fire({icon:'error',title:'Network error'});
                    else alert('Network error');
                });
        });
    } else {
        document.querySelectorAll('#iepStepsTbody tr').forEach(bindStepRow);
    }

})();
});
</script>
<style>
.iep-steps-compact #iepStepsTable > :not(caption) > * > * { padding: 0.35rem 0.45rem; vertical-align: middle; }
.iep-steps-compact #iepStepsTable .form-control-sm { min-height: calc(1.4em + 0.3rem); }
</style>
