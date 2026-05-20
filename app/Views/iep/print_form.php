<?php
/**
 * Standalone printable IEP (DepEd-oriented layout, no app chrome).
 *
 * @var array<string,mixed> $iep
 * @var array<int,array<string,mixed>> $iepDomains
 * @var array<string,mixed> $iepCore
 * @var array<int,array<string,mixed>> $iepSteps
 * @var array<int,array<string,mixed>> $signatories
 * @var array<string,mixed> $studentData
 * @var string $basePath
 */
$hName   = trim((string) ($iep['header_learner_name'] ?? ''));
$hLrn    = trim((string) ($iep['header_lrn'] ?? ''));
$hSchool = trim((string) ($iep['header_school_name'] ?? ''));
$hYear   = trim((string) ($iep['school_year'] ?? ''));
$hGrade  = trim((string) ($iep['header_grade_level'] ?? ''));
$domainList = array_column($iepDomains ?? [], 'domain_name');
$core = $iepCore ?? ['priority_needs' => '', 'terminal_objectives' => ''];
$signatoryRoleLabels = [
    'parent_guardian'    => 'Parents / Guardian',
    'guidance_counselor' => 'Guidance Counselor / Teacher',
    'teacher'            => 'Teacher/s',
    'sned_teacher'       => 'SNEd Teacher',
    'school_head'        => 'School Head',
    'ilrc_supervisor'    => 'ILRC Supervisor',
];
$signatoryLookup = [];
foreach ($signatories ?? [] as $sig) {
    $signatoryLookup[$sig['signatory_role'] ?? ''] = $sig['signatory_name'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>IEP — <?= htmlspecialchars($hName ?: 'Learner') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --ink:#111; --rule:#333; --muted:#555; }
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; color: var(--ink); margin: 0; padding: 12mm 14mm; font-size: 11pt; line-height: 1.35; }
        h1 { font-size: 14pt; text-align: center; margin: 0 0 4mm; letter-spacing: 0.02em; }
        .sub { text-align: center; font-size: 10pt; color: var(--muted); margin-bottom: 8mm; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 6mm; }
        table.meta td { border: 1px solid var(--rule); padding: 4px 6px; vertical-align: top; width: 50%; font-size: 10.5pt; }
        .lbl { font-weight: 700; display: block; font-size: 9.5pt; color: var(--muted); margin-bottom: 2px; }
        h2 { font-size: 11.5pt; border-bottom: 1px solid var(--rule); padding-bottom: 2px; margin: 5mm 0 3mm; }
        .tags span { display: inline-block; border: 1px solid var(--rule); padding: 2px 8px; margin: 2px 4px 2px 0; font-size: 10pt; }
        .block { margin-bottom: 4mm; white-space: pre-wrap; }
        table.steps { width: 100%; border-collapse: collapse; font-size: 10pt; margin-top: 2mm; }
        table.steps th, table.steps td { border: 1px solid var(--rule); padding: 4px 5px; vertical-align: top; }
        table.steps th { background: #f0f0f0; font-weight: 700; }
        .sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4mm 6mm; margin-top: 3mm; font-size: 10pt; }
        .sig-box { border: 1px solid var(--rule); min-height: 22mm; padding: 4px 6px; }
        @media print {
            body { padding: 10mm; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
    <h1>INDIVIDUALIZED EDUCATION PROGRAM (IEP)</h1>
    <div class="sub">Republic of the Philippines · Department of Education · Learner-centered record</div>

    <table class="meta">
        <tr>
            <td>
                <span class="lbl">Learner name</span>
                <?= htmlspecialchars($hName) ?>
            </td>
            <td>
                <span class="lbl">LRN</span>
                <?= htmlspecialchars($hLrn) ?>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">School</span>
                <?= htmlspecialchars($hSchool) ?>
            </td>
            <td>
                <span class="lbl">School year / Grade</span>
                <?= htmlspecialchars($hYear) ?><?= $hGrade !== '' ? ' · ' . htmlspecialchars($hGrade) : '' ?>
            </td>
        </tr>
    </table>

    <h2>Developmental domains / subject areas</h2>
    <div class="tags">
        <?php foreach ($domainList as $dn): ?>
            <span><?= htmlspecialchars($dn) ?></span>
        <?php endforeach; ?>
        <?php if (empty($domainList)): ?>
            <span>—</span>
        <?php endif; ?>
    </div>

    <h2>Priority needs</h2>
    <div class="block"><?= nl2br(htmlspecialchars((string) ($core['priority_needs'] ?? ''))) ?></div>

    <h2>Terminal objectives</h2>
    <div class="block"><?= nl2br(htmlspecialchars((string) ($core['terminal_objectives'] ?? ''))) ?></div>

    <h2>Re-evaluation date</h2>
    <div class="block"><?= !empty($iep['re_evaluation_date']) ? htmlspecialchars(date('F j, Y', strtotime((string) $iep['re_evaluation_date']))) : '—' ?></div>

    <h2>IEP steps</h2>
    <table class="steps">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Step objectives</th>
                <th>Duration of LP</th>
                <th>Instructional evaluation</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($iepSteps as $st): ?>
            <tr>
                <td class="text-center"><?= (int) ($st['step_number'] ?? 0) ?></td>
                <td><?= nl2br(htmlspecialchars(trim((string) ($st['step_objective'] ?? '')))) ?></td>
                <td><?= nl2br(htmlspecialchars(trim((string) ($st['duration_lp'] ?? '')))) ?></td>
                <td><?= nl2br(htmlspecialchars(trim((string) ($st['instructional_evaluation'] ?? '')))) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Signatories</h2>
    <div class="sig-grid">
        <?php foreach ($signatoryRoleLabels as $roleKey => $roleLabel): ?>
            <div class="sig-box">
                <span class="lbl"><?= htmlspecialchars($roleLabel) ?></span>
                <?= isset($signatoryLookup[$roleKey]) && $signatoryLookup[$roleKey] !== ''
                    ? htmlspecialchars($signatoryLookup[$roleKey])
                    : '________________________' ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p style="margin-top:8mm;font-size:9pt;color:#444;">
        Generated from SPED LMS. Status: <?= htmlspecialchars((string) ($iep['status'] ?? '')) ?>
        <?php if (!empty($iep['signed_at'])): ?> · Signed <?= htmlspecialchars(date('Y-m-d', strtotime((string) $iep['signed_at']))) ?><?php endif; ?>
    </p>

