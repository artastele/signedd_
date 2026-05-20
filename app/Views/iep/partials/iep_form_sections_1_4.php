<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-14
// Part of: SPED LMS — IEP form Sections 1–4 (header, domains, core, PDSP reference)

$fullNameDefault = trim($studentData['student_name'] ?? '');
if ($fullNameDefault === '' && !empty($studentData)) {
    $parts = array_filter([
        $studentData['first_name'] ?? '',
        $studentData['middle_name'] ?? '',
        $studentData['last_name'] ?? '',
    ]);
    $fullNameDefault = trim(implode(' ', $parts));
}

$dob = $studentData['birth_date'] ?? $studentData['date_of_birth'] ?? null;
$ageDefault = '';
if (!empty($dob)) {
    try {
        $d0 = new DateTime($dob);
        $ageDefault = (string) $d0->diff(new DateTime('today'))->y;
    } catch (Throwable $e) {
        $ageDefault = '';
    }
}

$hLearnerName = $iep['header_learner_name'] ?? $fullNameDefault;
$hAge         = $iep['header_learner_age'] ?? $ageDefault;
$hLrn         = $iep['header_lrn'] ?? ($studentData['lrn'] ?? '');
$hSection     = $iep['header_section'] ?? '';
$hTeacher     = $iep['header_teacher_name'] ?? ($iep['drafted_by_name'] ?? '');
$hSchool      = $iep['header_school_name'] ?? '';
$hGrade       = $iep['header_grade_level'] ?? ($studentData['grade_level_to_enroll'] ?? '');

$core = $iepCore ?? ['developmental_domain' => '', 'priority_needs' => '', 'terminal_objectives' => ''];
$domainList = array_column($iepDomains ?? [], 'domain_name');
$domainJson = json_encode($domainList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
$pdspDomainRows = $pdspDomainRows ?? [];
$depedStandardDomains = [
    'Perceptuo-Cognitive',
    'Psychosocial',
    'Socio-Emotional',
    'Psychomotor',
    'Daily Living Skills',
    'Communication & Language',
];
?>
<div class="iep-print-surface mb-4">

    <!-- Section 1 — Page header -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1" style="color:#1e4072;font-weight:600;">
                <i class="bi bi-file-earmark-medical me-2"></i>IEP — Individualized Education Plan
            </h1>
            <p class="text-muted mb-2">
                <strong style="color:#2c2c2c;"><?= htmlspecialchars((string) $hLearnerName) ?></strong>
                <?php if ($hLrn !== '' && $hLrn !== null): ?>
                    <span class="text-muted">&nbsp;·&nbsp;LRN <?= htmlspecialchars((string) $hLrn) ?></span>
                <?php endif; ?>
            </p>
            <span class="badge me-1 rounded-pill" style="background:#1e4072;color:#fff;"><?= htmlspecialchars((string) ($iep['school_year'] ?? '')) ?></span>
            <?php
            $st = $iep['status'] ?? 'draft';
            if ($st === 'draft') {
                $stBg = '#5a6670';
                $stLabel = 'Draft';
            } elseif ($st === 'signing') {
                $stBg = '#ef9f27';
                $stLabel = 'Signing';
            } elseif ($st === 'signed') {
                $stBg = '#3b6d11';
                $stLabel = 'Signed';
            } else {
                $stBg = '#6c757d';
                $stLabel = ucfirst((string) $st);
            }
            ?>
            <span class="badge rounded-pill" style="background:<?= $stBg ?>;color:#fff;"><?= htmlspecialchars($stLabel) ?></span>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="<?= htmlspecialchars($basePath) ?>/iep/print/<?= (int) $iep['id'] ?>" target="_blank" rel="noopener"
               class="btn btn-sm" style="border:2px solid #1e4072;color:#1e4072;background:#fff;">
                <i class="bi bi-printer me-1"></i>Print
            </a>
            <a href="<?= htmlspecialchars($basePath) ?>/iep" class="btn btn-sm" style="border:2px solid #1e4072;color:#1e4072;background:#fff;">
                <i class="bi bi-arrow-left me-1"></i>Repository
            </a>
        </div>
    </div>

    <?php if (!empty($reevalBanner)): ?>
        <div class="alert mb-4 rounded-0 border-0 text-white" style="background:#a01422;">
            <i class="bi bi-calendar-x me-2"></i>
            Re-evaluation date has passed. Please review and update this IEP.
        </div>
    <?php endif; ?>

<?php if (!empty($readOnly)): ?>

    <div class="card mb-4" style="border-left:4px solid #a01422;">
        <div class="card-header text-white" style="background:#1e4072;">
            <h2 class="h6 mb-0">Learner &amp; school header</h2>
        </div>
        <div class="card-body small">
            <div class="row g-2">
                <div class="col-md-6"><span class="text-muted">Learner</span><div><?= htmlspecialchars((string) $hLearnerName) ?></div></div>
                <div class="col-md-3"><span class="text-muted">Age</span><div><?= htmlspecialchars((string) $hAge) ?></div></div>
                <div class="col-md-3"><span class="text-muted">LRN</span><div><?= htmlspecialchars((string) $hLrn) ?></div></div>
                <div class="col-md-4"><span class="text-muted">Section</span><div><?= htmlspecialchars((string) $hSection) ?></div></div>
                <div class="col-md-4"><span class="text-muted">Teacher</span><div><?= htmlspecialchars((string) $hTeacher) ?></div></div>
                <div class="col-md-4"><span class="text-muted">School</span><div><?= htmlspecialchars((string) $hSchool) ?></div></div>
                <div class="col-md-4"><span class="text-muted">School year</span><div><?= htmlspecialchars((string) ($iep['school_year'] ?? '')) ?></div></div>
                <div class="col-md-4"><span class="text-muted">Grade</span><div><?= htmlspecialchars((string) $hGrade) ?></div></div>
            </div>
        </div>
    </div>

    <div class="card mb-4" style="border-left:4px solid #a01422;">
        <div class="card-header text-white" style="background:#1e4072;">
            <h2 class="h6 mb-0">Domains &amp; core IEP fields</h2>
        </div>
        <div class="card-body small">
            <div class="mb-3">
                <span class="text-muted d-block mb-1">Developmental domains (tags)</span>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($domainList as $dn): ?>
                        <span class="badge px-3 py-2 rounded-pill" style="background:#1e4072;color:#fff;"><?= htmlspecialchars($dn) ?></span>
                    <?php endforeach; ?>
                    <?php if (empty($domainList)): ?><span class="text-muted small">No domains recorded.</span><?php endif; ?>
                </div>
            </div>
            <div class="mb-2"><span class="text-muted d-block">Priority needs</span><?= nl2br(htmlspecialchars((string) ($core['priority_needs'] ?? ''))) ?></div>
            <div class="mb-2"><span class="text-muted d-block">Terminal objectives</span><?= nl2br(htmlspecialchars((string) ($core['terminal_objectives'] ?? ''))) ?></div>
            <div><span class="text-muted d-block">Date of re-evaluation</span><?= !empty($iep['re_evaluation_date']) ? htmlspecialchars(date('F j, Y', strtotime($iep['re_evaluation_date']))) : '—' ?></div>
        </div>
    </div>

<?php else: ?>

    <form method="POST" action="<?= htmlspecialchars($basePath) ?>/iep/save-part1" id="iepPartOneForm" class="mb-4">
        <input type="hidden" name="iep_id" value="<?= (int) $iep['id'] ?>">
        <input type="hidden" name="domain_names_json" id="domainNamesJson" value='<?= htmlspecialchars($domainJson, ENT_QUOTES, 'UTF-8') ?>'>

        <div class="card mb-4" style="border-left:4px solid #a01422;">
            <div class="card-header text-white" style="background:#1e4072;">
                <h2 class="h6 mb-0"><i class="bi bi-person-lines-fill me-2"></i>Learner &amp; school header</h2>
            </div>
            <div class="card-body" style="background:#fff;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">Name of Learner</label>
                        <input type="text" class="form-control" name="header_learner_name"
                               value="<?= htmlspecialchars((string) $hLearnerName) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">Age</label>
                        <input type="text" class="form-control" name="header_learner_age"
                               value="<?= htmlspecialchars((string) $hAge) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">LRN</label>
                        <input type="text" class="form-control" name="header_lrn"
                               value="<?= htmlspecialchars((string) $hLrn) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">Section</label>
                        <input type="text" class="form-control" name="header_section"
                               value="<?= htmlspecialchars((string) $hSection) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">Name of Teacher</label>
                        <input type="text" class="form-control" name="header_teacher_name"
                               value="<?= htmlspecialchars((string) $hTeacher) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">School</label>
                        <input type="text" class="form-control" name="header_school_name" placeholder="School name"
                               value="<?= htmlspecialchars((string) $hSchool) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">School Year</label>
                        <input type="text" class="form-control" name="school_year"
                               value="<?= htmlspecialchars((string) ($iep['school_year'] ?? '')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" style="color:#1e4072;">Grade Level</label>
                        <input type="text" class="form-control" name="header_grade_level" placeholder="e.g. Grade 3"
                               value="<?= htmlspecialchars((string) $hGrade) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="border-left:4px solid #a01422;">
            <div class="card-header text-white" style="background:#1e4072;">
                <h2 class="h6 mb-0"><i class="bi bi-diagram-3 me-2"></i>Domains &amp; core IEP fields</h2>
            </div>
            <div class="card-body" style="background:#fff;">
                <div id="domainChipContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label small fw-semibold mb-0" style="color:#1e4072;" for="depedDomainPickSelect">Developmental domain / subject area</label>
                        <select class="form-select form-select-sm" id="depedDomainPickSelect">
                            <option value="">— Select domain —</option>
                            <?php foreach ($depedStandardDomains as $std): ?>
                                <option value="<?= htmlspecialchars($std) ?>"><?= htmlspecialchars($std) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3 d-grid">
                        <label class="form-label small d-none d-md-block mb-0">&nbsp;</label>
                        <button type="button" class="btn btn-sm text-white" id="depedDomainPickAdd" style="background:#1e4072;">Add domain</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold" style="color:#1e4072;">Priority needs</label>
                    <textarea class="form-control" rows="4" name="priority_needs"
                              placeholder="Skills and supports to emphasize"><?= htmlspecialchars((string) ($core['priority_needs'] ?? '')) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold" style="color:#1e4072;">Terminal objectives</label>
                    <textarea class="form-control" rows="3" name="terminal_objectives"
                              placeholder="Overall goals for the quarter"><?= htmlspecialchars((string) ($core['terminal_objectives'] ?? '')) ?></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-semibold" style="color:#1e4072;">Date of re-evaluation</label>
                    <input type="date" class="form-control" style="max-width:220px;" name="re_evaluation_date" id="iepReEvalDateInput"
                           value="<?= htmlspecialchars((string) ($iep['re_evaluation_date'] ?? '')) ?>">
                    <small class="text-muted d-block mt-1">Required before signing.</small>
                </div>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn py-2 fw-semibold" style="background:#a01422;color:#fff;border:none;">
                <i class="bi bi-save me-1"></i>Save
            </button>
        </div>
    </form>

<script>
(function () {
    var input = document.getElementById('domainNamesJson');
    var container = document.getElementById('domainChipContainer');
    var depedAdd = document.getElementById('depedDomainPickAdd');
    var depedSel = document.getElementById('depedDomainPickSelect');
    var form = document.getElementById('iepPartOneForm');
    if (!input || !container || !form) return;

    function readDomains() {
        try { return JSON.parse(input.value || '[]'); } catch (e) { return []; }
    }
    function writeDomains(arr) {
        input.value = JSON.stringify(arr);
    }
    function render() {
        var list = readDomains();
        container.innerHTML = '';
        list.forEach(function (name, idx) {
            var span = document.createElement('span');
            span.className = 'badge d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill';
            span.style.cssText = 'background:#1e4072;color:#fff;font-weight:500;';
            span.appendChild(document.createTextNode(name));
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm ms-1 p-0 border-0';
            btn.style.cssText = 'color:#ffb3b3;line-height:1;font-size:1.1rem;';
            btn.innerHTML = '&times;';
            btn.setAttribute('aria-label', 'Remove domain');
            (function (i) {
                btn.addEventListener('click', function () {
                    var cur = readDomains();
                    cur.splice(i, 1);
                    writeDomains(cur);
                    render();
                });
            })(idx);
            span.appendChild(btn);
            container.appendChild(span);
        });
    }
    function addDomainLabel(v) {
        v = (v || '').trim();
        if (!v) return;
        var cur = readDomains();
        if (cur.indexOf(v) === -1) cur.push(v);
        writeDomains(cur);
        render();
    }
    if (depedAdd && depedSel) {
        depedAdd.addEventListener('click', function () {
            addDomainLabel(depedSel.value || '');
            depedSel.selectedIndex = 0;
        });
    }
    form.addEventListener('submit', function (e) {
        var cur = readDomains();
        if (!cur.length) {
            e.preventDefault();
            alert('Please keep at least one domain tag, or add a domain before saving.');
        }
    });
    render();
})();
</script>

<?php endif; ?>

</div>
