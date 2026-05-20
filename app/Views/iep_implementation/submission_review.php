<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-13
// Part of: SPED LMS — Teacher Submission Review (Read-Only)

$pageTitle = 'Submission Review — ' . htmlspecialchars($activity['title'] ?? '') . ' — SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-3" style="max-width:820px;">

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Header card -->
<div class="card mb-3" style="border-left:4px solid #1e4072;">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h5 class="fw-bold mb-1" style="color:#1e4072;">
                    <?php echo htmlspecialchars($activity['title']); ?>
                </h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge" style="background:#1e4072;font-size:.75rem;">
                        <?php echo ucwords(str_replace('_', ' ', $activity['activity_type'])); ?>
                    </span>
                    <span class="text-muted small">
                        <i class="ti ti-user me-1"></i>
                        <?php echo htmlspecialchars($student['student_name']); ?>
                        (LRN: <?php echo htmlspecialchars($student['lrn']); ?>)
                    </span>
                </div>
                <?php if (!empty($iep_id)): ?>
                <div class="mt-2">
                    <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/workspace/<?php echo (int)$iep_id; ?>"
                       class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;">
                        <i class="ti ti-layout-dashboard me-1"></i>IEP workspace
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php
            $dispMax = isset($displayMaxScore) ? (int)$displayMaxScore : (int)($activity['max_score'] ?? 0);
            $hasOfficial = $submission && $submission['score'] !== null && $submission['score'] !== '';
            ?>
            <?php if ($submission): ?>
                <?php if ($hasOfficial): ?>
                    <span class="badge fs-6 px-3 py-2" style="background:#3b6d11;">
                        Grade: <?php echo (int)$submission['score']; ?> / <?php echo (int)($submission['grade_max_score'] ?? $dispMax); ?>
                    </span>
                <?php elseif ($submission['auto_score'] !== null && $submission['auto_score'] !== ''): ?>
                    <span class="badge fs-6 px-3 py-2" style="background:#1e4072;">
                        Auto-score: <?php echo (int)$submission['auto_score']; ?> / <?php echo $dispMax > 0 ? $dispMax : (int)($activity['max_score'] ?? 0); ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary fs-6 px-3 py-2">Not auto-scored</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge bg-secondary fs-6 px-3 py-2">Not Submitted</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$submission): ?>
    <div class="alert" style="background:#fff3cd;border:1px solid #ffc107;color:#856404;">
        <i class="ti ti-info-circle me-2"></i>
        This learner has not submitted this activity yet.
    </div>
<?php else: ?>

<?php
$actType  = $activity['activity_type'];
$actData  = $activity['activity_data'];
$answers  = json_decode($submission['answers'] ?? '{}', true) ?? [];
?>

<!-- Submission details -->
<div class="card mb-3">
    <div class="card-header" style="background:#f8f9fa;font-weight:600;font-size:.9rem;">
        <i class="ti ti-clipboard-check me-2" style="color:#1e4072;"></i>Submitted Answers
        <span class="text-muted fw-normal ms-2" style="font-size:.8rem;">
            <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
        </span>
    </div>
    <div class="card-body">

    <?php if ($actType === 'multiple_choice'): ?>
        <?php foreach ($actData['questions'] ?? [] as $qi => $q): ?>
        <div class="mb-4">
            <div class="fw-semibold mb-2" style="color:#2c2c2c;">
                <?php echo ($qi + 1) . '. ' . htmlspecialchars($q['text'] ?? $q['question'] ?? ''); ?>
            </div>
            <?php
            $selectedOi = $answers[$qi] ?? $answers[(string)$qi] ?? null;
            foreach ($q['options'] ?? [] as $oi => $opt):
                $optText   = is_array($opt) ? ($opt['text'] ?? '') : $opt;
                $isCorrect = is_array($opt) ? !empty($opt['is_correct']) : false;
                $isChosen  = (string)$oi === (string)$selectedOi;
                $bg = $isCorrect ? '#e8f5e9' : ($isChosen && !$isCorrect ? '#fdf0f1' : '#f8f9fa');
                $border = $isCorrect ? '#3b6d11' : ($isChosen && !$isCorrect ? '#a01422' : '#dee2e6');
            ?>
            <div class="d-flex align-items-center gap-2 p-2 rounded mb-1"
                 style="background:<?php echo $bg; ?>;border:1.5px solid <?php echo $border; ?>;">
                <?php if ($isCorrect): ?>
                    <i class="ti ti-check" style="color:#3b6d11;"></i>
                <?php elseif ($isChosen): ?>
                    <i class="ti ti-x" style="color:#a01422;"></i>
                <?php else: ?>
                    <i class="ti ti-circle" style="color:#aaa;"></i>
                <?php endif; ?>
                <span style="font-size:.9rem;"><?php echo htmlspecialchars($optText); ?></span>
                <?php if ($isChosen): ?>
                    <span class="ms-auto badge" style="background:<?php echo $isCorrect ? '#3b6d11' : '#a01422'; ?>;">
                        Learner's answer
                    </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

    <?php elseif ($actType === 'true_false'): ?>
        <div class="mb-2 fw-semibold"><?php echo htmlspecialchars($actData['statement'] ?? $actData['question'] ?? ''); ?></div>
        <?php
        $correct = strtolower($actData['correct_answer'] ?? $actData['answer'] ?? '');
        $given   = strtolower($answers[0] ?? $answers['0'] ?? $answers['answer'] ?? '');
        $isRight = $given === $correct;
        ?>
        <div class="d-flex gap-3 mt-2">
            <?php foreach (['true', 'false'] as $opt): ?>
            <?php
            $isChosen  = $given === $opt;
            $isCorrect = $correct === $opt;
            $bg = $isCorrect ? '#e8f5e9' : ($isChosen && !$isCorrect ? '#fdf0f1' : '#f8f9fa');
            $border = $isCorrect ? '#3b6d11' : ($isChosen && !$isCorrect ? '#a01422' : '#dee2e6');
            ?>
            <div class="px-4 py-2 rounded fw-semibold"
                 style="background:<?php echo $bg; ?>;border:2px solid <?php echo $border; ?>;">
                <?php echo ucfirst($opt); ?>
                <?php if ($isChosen): ?> <i class="ti ti-<?php echo $isRight ? 'check' : 'x'; ?>"></i><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-2 small text-muted">
            Correct answer: <strong><?php echo ucfirst($correct); ?></strong>
            &nbsp;|&nbsp;
            Learner answered: <strong><?php echo $given ? ucfirst($given) : '(none)'; ?></strong>
            &nbsp;
            <?php if ($isRight): ?>
                <span class="badge" style="background:#3b6d11;">Correct</span>
            <?php else: ?>
                <span class="badge" style="background:#a01422;">Incorrect</span>
            <?php endif; ?>
        </div>

    <?php elseif ($actType === 'fill_in_blanks'): ?>
        <?php foreach ($actData['sentences'] ?? [] as $si => $sentence): ?>
        <?php
        $given   = trim($answers[$si] ?? $answers[(string)$si] ?? '');
        $correct = array_map('strtolower', array_map('trim', $sentence['answers'] ?? []));
        $isRight = in_array(strtolower($given), $correct);
        ?>
        <div class="mb-3 p-3 rounded" style="background:#f8f9fa;border:1px solid #dee2e6;">
            <div class="small text-muted mb-1">Sentence <?php echo $si + 1; ?>:</div>
            <div class="mb-1"><?php echo htmlspecialchars($sentence['text'] ?? ''); ?></div>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="small fw-semibold">Answer:</span>
                <span class="px-2 py-1 rounded" style="background:<?php echo $isRight ? '#e8f5e9' : '#fdf0f1'; ?>;border:1px solid <?php echo $isRight ? '#3b6d11' : '#a01422'; ?>;">
                    <?php echo $given ?: '<em class="text-muted">blank</em>'; ?>
                </span>
                <?php if ($isRight): ?>
                    <i class="ti ti-check" style="color:#3b6d11;"></i>
                <?php else: ?>
                    <i class="ti ti-x" style="color:#a01422;"></i>
                    <span class="small text-muted">Expected: <?php echo htmlspecialchars(implode(' / ', $sentence['answers'] ?? [])); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php elseif ($actType === 'matching'): ?>
        <table class="table table-sm table-bordered" style="font-size:.9rem;">
            <thead style="background:#1e4072;color:#fff;">
                <tr><th>Left</th><th>Learner's Match</th><th>Correct</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($actData['pairs'] ?? [] as $pi => $pair): ?>
            <?php
            $given   = $answers[$pi] ?? $answers[(string)$pi] ?? '';
            $isRight = strtolower(trim($given)) === strtolower(trim($pair['right']));
            ?>
            <tr>
                <td><?php echo htmlspecialchars($pair['left']); ?></td>
                <td style="color:<?php echo $isRight ? '#3b6d11' : '#a01422'; ?>;">
                    <?php echo htmlspecialchars($given ?: '(none)'); ?>
                </td>
                <td><?php echo htmlspecialchars($pair['right']); ?></td>
                <td class="text-center">
                    <i class="ti ti-<?php echo $isRight ? 'check' : 'x'; ?>"
                       style="color:<?php echo $isRight ? '#3b6d11' : '#a01422'; ?>;"></i>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($actType === 'drag_drop_sort' || $actType === 'sequencing'): ?>
        <?php
        $items        = $actData['items'] ?? $actData['steps'] ?? [];
        $correctOrder = $actData['correct_order'] ?? range(0, count($items) - 1);
        $givenOrder   = array_values($answers);
        $isRight      = array_map('strval', $givenOrder) === array_map('strval', $correctOrder);
        ?>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small fw-semibold mb-2 text-muted">Learner's order:</div>
                <?php foreach ($givenOrder as $idx => $itemIdx): ?>
                <div class="p-2 rounded mb-1" style="background:#f8f9fa;border:1px solid #dee2e6;">
                    <span class="badge me-2" style="background:#1e4072;"><?php echo $idx + 1; ?></span>
                    <?php
                    $item = $items[$itemIdx] ?? $items[$idx] ?? '';
                    echo htmlspecialchars(is_array($item) ? ($item['text'] ?? '') : $item);
                    ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-6">
                <div class="small fw-semibold mb-2 text-muted">Correct order:</div>
                <?php foreach ($correctOrder as $idx => $itemIdx): ?>
                <div class="p-2 rounded mb-1" style="background:#e8f5e9;border:1px solid #3b6d11;">
                    <span class="badge me-2" style="background:#3b6d11;"><?php echo $idx + 1; ?></span>
                    <?php
                    $item = $items[$itemIdx] ?? $items[$idx] ?? '';
                    echo htmlspecialchars(is_array($item) ? ($item['text'] ?? '') : $item);
                    ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mt-2">
            <?php if ($isRight): ?>
                <span class="badge" style="background:#3b6d11;">Correct order</span>
            <?php else: ?>
                <span class="badge" style="background:#a01422;">Incorrect order</span>
            <?php endif; ?>
        </div>

    <?php elseif ($actType === 'image_label'): ?>
        <?php $imgPath = $actData['image_path'] ?? ''; $labels = $actData['labels'] ?? []; ?>
        <?php if ($imgPath): ?>
        <div style="position:relative;display:inline-block;max-width:100%;margin-bottom:16px;">
            <img src="<?php echo htmlspecialchars($basePath . '/' . ltrim($imgPath, '/')); ?>"
                 style="max-width:100%;border-radius:8px;display:block;" alt="Label image">
            <?php foreach ($labels as $li => $lbl): ?>
            <div style="position:absolute;left:<?php echo (float)($lbl['x'] ?? 0); ?>%;top:<?php echo (float)($lbl['y'] ?? 0); ?>%;
                        width:28px;height:28px;border-radius:50%;background:#a01422;color:#fff;
                        display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;
                        transform:translate(-50%,-50%);border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);">
                <?php echo $li + 1; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <table class="table table-sm table-bordered" style="font-size:.9rem;">
            <thead style="background:#1e4072;color:#fff;">
                <tr><th>#</th><th>Learner's Answer</th><th>Correct Answer</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($labels as $li => $lbl): ?>
            <?php
            $given   = trim($answers[$li] ?? $answers[(string)$li] ?? '');
            $correct = trim($lbl['answer'] ?? '');
            $isRight = strtolower($given) === strtolower($correct);
            ?>
            <tr>
                <td><?php echo $li + 1; ?></td>
                <td style="color:<?php echo $isRight ? '#3b6d11' : '#a01422'; ?>;">
                    <?php echo htmlspecialchars($given ?: '(blank)'); ?>
                </td>
                <td><?php echo htmlspecialchars($correct); ?></td>
                <td class="text-center">
                    <i class="ti ti-<?php echo $isRight ? 'check' : 'x'; ?>"
                       style="color:<?php echo $isRight ? '#3b6d11' : '#a01422'; ?>;"></i>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($actType === 'flashcards'): ?>
        <div class="alert" style="background:#e8edf5;border:1px solid #1e4072;color:#1e4072;">
            <i class="ti ti-cards me-2"></i>
            Flashcards are view-only. The learner marked this as reviewed.
        </div>
        <?php foreach ($actData['cards'] ?? [] as $ci => $card): ?>
        <div class="d-flex gap-3 p-2 rounded mb-2" style="background:#f8f9fa;border:1px solid #dee2e6;">
            <div class="flex-fill p-2 rounded text-center" style="background:#1e4072;color:#fff;font-size:.9rem;">
                <?php echo htmlspecialchars($card['front'] ?? ''); ?>
            </div>
            <div class="d-flex align-items-center" style="color:#aaa;">→</div>
            <div class="flex-fill p-2 rounded text-center" style="background:#a01422;color:#fff;font-size:.9rem;">
                <?php echo htmlspecialchars($card['back'] ?? ''); ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php else: ?>
        <pre class="bg-light p-3 rounded small"><?php echo htmlspecialchars(json_encode($answers, JSON_PRETTY_PRINT)); ?></pre>
    <?php endif; ?>

    </div>
</div>

<?php if (!empty($submission['remarks'])): ?>
<div class="card mb-3">
    <div class="card-header" style="background:#f8f9fa;font-weight:600;font-size:.9rem;">
        <i class="ti ti-message me-2" style="color:#1e4072;"></i>Teacher Remarks
    </div>
    <div class="card-body">
        <?php echo nl2br(htmlspecialchars($submission['remarks'])); ?>
    </div>
</div>
<?php endif; ?>

<?php
$maxForGrade = $dispMax > 0 ? $dispMax : (int)($activity['max_score'] ?? 0);
$needsConfirm = !empty($canGrade) && !$hasOfficial && $maxForGrade > 0;
?>
<?php if ($needsConfirm): ?>
<div class="card mb-3" style="border-left:4px solid #3b6d11;">
    <div class="card-body py-3">
        <h6 class="fw-semibold mb-2" style="color:#1e4072;">Confirm official grade</h6>
        <form method="POST" action="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/submission/<?php echo (int)$activity['id']; ?>/confirm-grade" class="row g-2 align-items-end">
            <input type="hidden" name="student_id" value="<?php echo (int)$student_id; ?>">
            <div class="col-auto">
                <label class="form-label small mb-0">Score (max <?php echo (int)$maxForGrade; ?>)</label>
                <input type="number" class="form-control form-control-sm" name="score" style="width:100px;"
                       min="0" max="<?php echo (int)$maxForGrade; ?>"
                       value="<?php echo ($submission['auto_score'] !== null && $submission['auto_score'] !== '') ? (int)$submission['auto_score'] : 0; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Remarks (optional)</label>
                <input type="text" class="form-control form-control-sm" name="remarks" maxlength="500" placeholder="Short note for the learner file">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm text-white" style="background:#3b6d11;border:none;">
                    Confirm grade
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php endif; // end if $submission ?>

</div>
</div><!-- /.main-content -->

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
