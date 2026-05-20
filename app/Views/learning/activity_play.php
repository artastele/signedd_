<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Activity Play View (Learner Side)

$pageTitle    = htmlspecialchars($activity['title'] ?? 'Activity') . ' — SPED LMS';
$basePath     = defined('BASE_PATH') ? BASE_PATH : '';
$actId        = (int)($activity['id'] ?? 0);
$actType      = $activity['activity_type'] ?? '';
$actData      = $activity['activity_data'] ?? [];
$hasSub       = !empty($submission['submission_id']);
$lessonPlanId = (int)($activity['lesson_plan_id'] ?? 0);
$typeLabel    = ucwords(str_replace('_', ' ', $actType));
$isViewOnly   = in_array($actType, ['flashcards', 'image_label'], true);
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div style="max-width:720px;">
<style>
.learner-body{background:#f5f5f5;margin:0;padding:0;}
.learner-topbar{background:#1e4072;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,.18);}
.learner-topbar .logo{font-weight:700;font-size:1.1rem;color:#fff;text-decoration:none;display:flex;align-items:center;gap:8px;}
.learner-topbar .topbar-actions{display:flex;align-items:center;gap:10px;}
.btn-topbar{background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;padding:8px 14px;font-size:.9rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;min-height:40px;cursor:pointer;}
.btn-logout{background:#a01422;color:#fff;border:none;border-radius:6px;padding:8px 14px;font-size:.9rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;min-height:40px;cursor:pointer;}
.btn-logout:hover{background:#8a1220;color:#fff;}
.learner-main{max-width:700px;margin:0 auto;padding:20px 16px 80px;}
.btn-back{display:inline-flex;align-items:center;gap:6px;color:#1e4072;text-decoration:none;font-size:.9rem;font-weight:600;padding:6px 0;margin-bottom:12px;}
.btn-back:hover{color:#a01422;}
.activity-header{margin-bottom:20px;}
.activity-header h1{font-size:1.3rem;font-weight:700;color:#1e4072;margin:8px 0 4px;}
.type-badge{display:inline-block;padding:3px 10px;border-radius:4px;font-size:.75rem;font-weight:600;color:#fff;}
.type-multiple_choice{background:#1e4072;}.type-true_false{background:#3b6d11;}.type-fill_in_blanks{background:#a01422;}
.type-matching{background:#6c757d;}.type-drag_drop_sort{background:#e67e22;}.type-image_label{background:#8e44ad;}
.type-flashcards{background:#2980b9;}.type-sequencing{background:#16a085;}
.instructions-box{background:#e8edf5;border-left:4px solid #1e4072;border-radius:6px;padding:12px 16px;margin-bottom:16px;font-size:.95rem;}
.activity-card{background:#fff;border-radius:10px;padding:16px;margin-bottom:16px;box-shadow:0 1px 4px rgba(0,0,0,.07);}
.question-text{font-size:1rem;font-weight:600;color:#2c2c2c;margin-bottom:12px;}
.option-card{display:flex;align-items:center;gap:12px;padding:12px 16px;border:2px solid #e0e0e0;border-radius:8px;margin-bottom:8px;cursor:pointer;min-height:48px;font-size:.95rem;transition:border-color .2s,background .2s;}
.option-card:hover{border-color:#1e4072;background:#f0f4fa;}
.option-card.selected{border-color:#a01422;background:#fdf0f1;}
.option-card input[type=radio]{accent-color:#a01422;width:18px;height:18px;flex-shrink:0;}
.tf-card{display:flex;align-items:center;justify-content:center;gap:12px;padding:16px;border:2px solid #e0e0e0;border-radius:8px;margin-bottom:8px;cursor:pointer;min-height:56px;font-size:1.05rem;font-weight:600;transition:border-color .2s,background .2s;}
.tf-card:hover{border-color:#1e4072;background:#f0f4fa;}
.tf-card.selected{border-color:#a01422;background:#fdf0f1;}
.fib-sentence{font-size:1rem;line-height:2.2;margin-bottom:12px;}
.fib-input{border:none;border-bottom:2px solid #1e4072;background:transparent;padding:2px 6px;font-size:1rem;width:120px;outline:none;}
.fib-input:focus{border-bottom-color:#a01422;}
.matching-row{display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap;}
.matching-left{flex:1;min-width:120px;font-weight:600;font-size:.95rem;}
.matching-select{flex:1;min-width:140px;padding:10px 12px;border:2px solid #e0e0e0;border-radius:8px;font-size:.9rem;min-height:44px;}
.matching-select:focus{border-color:#a01422;outline:none;}
.drag-list{list-style:none;padding:0;margin:0;}
.drag-item{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fff;border:2px solid #e0e0e0;border-radius:8px;margin-bottom:8px;cursor:grab;font-size:.95rem;min-height:48px;}
.drag-item:hover{border-color:#1e4072;}
.drag-item.dragging{opacity:.5;border-color:#a01422;}
.drag-handle{color:#aaa;font-size:1.1rem;}
.flashcard-container{perspective:800px;margin-bottom:20px;min-height:180px;}
.flashcard{width:100%;min-height:180px;position:relative;transform-style:preserve-3d;transition:transform .5s;cursor:pointer;}
.flashcard.flipped{transform:rotateY(180deg);}
.flashcard-face{position:absolute;inset:0;backface-visibility:hidden;border-radius:12px;display:flex;align-items:center;justify-content:center;padding:24px;font-size:1.1rem;font-weight:600;text-align:center;}
.flashcard-front{background:#1e4072;color:#fff;}
.flashcard-back{background:#a01422;color:#fff;transform:rotateY(180deg);}
.flashcard-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;}
.btn-flip{display:flex;align-items:center;justify-content:center;gap:8px;flex:1;min-height:48px;background:#1e4072;color:#fff;border:none;border-radius:8px;font-size:.95rem;font-weight:600;cursor:pointer;}
.btn-flip:hover{background:#163260;}
.btn-fc-nav{display:flex;align-items:center;justify-content:center;min-height:48px;min-width:48px;background:#e8edf5;color:#1e4072;border:none;border-radius:8px;font-size:.95rem;font-weight:600;cursor:pointer;}
.fc-counter{font-size:.9rem;color:#6c757d;font-weight:600;}
.image-label-wrapper{position:relative;display:inline-block;max-width:100%;}
.image-label-wrapper img{max-width:100%;border-radius:8px;display:block;}
.label-dot{position:absolute;width:28px;height:28px;border-radius:50%;background:#a01422;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;transform:translate(-50%,-50%);border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);}
.label-inputs{margin-top:16px;}
.label-input-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.label-input-num{width:28px;height:28px;border-radius:50%;background:#a01422;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.label-input{flex:1;padding:10px 12px;border:2px solid #e0e0e0;border-radius:8px;font-size:.9rem;min-height:44px;}
.label-input:focus{border-color:#a01422;outline:none;}
.btn-submit{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:52px;background:#a01422;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:16px;}
.btn-submit:hover{background:#8a1220;}
.btn-submit:disabled{background:#ccc;cursor:not-allowed;}
.result-box{border-radius:10px;padding:20px;text-align:center;margin-top:16px;}
.result-box.excellent{background:#e8f5e9;border:2px solid #3b6d11;}
.result-box.good{background:#fff8e1;border:2px solid #e67e22;}
.result-box.needs-work{background:#fdf0f1;border:2px solid #a01422;}
.result-score{font-size:2rem;font-weight:700;margin-bottom:4px;}
.result-msg{font-size:1rem;color:#2c2c2c;}
.btn-back-lesson{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:48px;background:#1e4072;color:#fff;border:none;border-radius:8px;font-size:.95rem;font-weight:600;text-decoration:none;cursor:pointer;margin-top:12px;}
.btn-back-lesson:hover{background:#163260;color:#fff;}
.already-submitted{background:#e8f5e9;border:2px solid #3b6d11;border-radius:16px;padding:18px;text-align:center;margin-bottom:16px;}
.g-stars-result{display:flex;justify-content:center;gap:6px;margin:10px 0;font-size:2rem;}
.btn-i-read{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:56px;background:#3b6d11;color:#fff;border:none;border-radius:20px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:16px;}
.btn-i-read:hover{opacity:.88}
</style>

<a href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>" style="display:inline-flex;align-items:center;gap:6px;color:#1e4072;text-decoration:none;font-size:.9rem;font-weight:600;margin-bottom:14px;">
    <i class="bi bi-arrow-left"></i> Back to Lesson
</a>

<div class="activity-header">
    <span class="type-badge type-<?php echo htmlspecialchars($actType); ?>"><?php echo htmlspecialchars($typeLabel); ?></span>
    <h1><?php echo htmlspecialchars($activity['title']); ?></h1>
    <?php if (!empty($activity['due_date'])): ?>
    <div style="font-size:.85rem;color:#6c757d;"><i class="ti ti-clock"></i> Due: <?php echo date('M j, Y g:i A', strtotime($activity['due_date'])); ?></div>
    <?php endif; ?>
</div>

<?php if (!empty($activity['instructions'])): ?>
<div class="instructions-box">
    <strong><i class="ti ti-info-circle"></i> Instructions:</strong><br>
    <?php echo nl2br(htmlspecialchars($activity['instructions'])); ?>
</div>
<?php endif; ?>

<?php if ($hasSub && $actType !== 'flashcards'): ?>
<div class="already-submitted">
    <i class="ti ti-check" style="color:#3b6d11;font-size:1.5rem;"></i>
    <div style="font-weight:700;color:#3b6d11;margin-top:4px;">Already Submitted</div>
    <?php if ($submission['auto_score'] !== null): ?>
    <div style="font-size:.9rem;margin-top:4px;">Score: <strong><?php echo (int)$submission['auto_score']; ?></strong> / <?php echo (int)($activity['max_score'] ?? 0); ?></div>
    <?php endif; ?>
    <?php if (!empty($submission['score'])): ?>
    <div style="font-size:.9rem;color:#1e4072;margin-top:2px;">Teacher grade: <strong><?php echo (int)$submission['score']; ?></strong> / <?php echo (int)($submission['grade_max_score'] ?? $activity['max_score'] ?? 0); ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="activity-card" id="activityArea">

<?php if ($actType === 'multiple_choice'): ?>
    <?php foreach ($actData['questions'] ?? [] as $qi => $q): ?>
    <div style="margin-bottom:20px;">
        <div class="question-text"><?php echo ($qi+1) . '. ' . htmlspecialchars($q['text'] ?? $q['question'] ?? ''); ?></div>
        <?php foreach ($q['options'] ?? [] as $oi => $opt): ?>
        <div class="option-card" onclick="selectOption(this,<?php echo $qi; ?>,<?php echo $oi; ?>)" data-qi="<?php echo $qi; ?>" data-oi="<?php echo $oi; ?>">
            <input type="radio" name="q<?php echo $qi; ?>" value="<?php echo $oi; ?>" style="pointer-events:none;">
            <?php echo htmlspecialchars(is_array($opt) ? ($opt['text'] ?? '') : $opt); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$hasSub): ?><button class="btn-submit" id="submitBtn" onclick="submitAnswers()"><i class="ti ti-send"></i> Submit</button><?php endif; ?>

<?php elseif ($actType === 'true_false'): ?>
    <div class="question-text"><?php echo htmlspecialchars($actData['statement'] ?? $actData['question'] ?? ''); ?></div>
    <div class="tf-card" onclick="selectTF(this,'true')" id="tfTrue"><i class="ti ti-check" style="color:#3b6d11;font-size:1.3rem;"></i> True</div>
    <div class="tf-card" onclick="selectTF(this,'false')" id="tfFalse"><i class="ti ti-x" style="color:#a01422;font-size:1.3rem;"></i> False</div>
    <?php if (!$hasSub): ?><button class="btn-submit" id="submitBtn" onclick="submitAnswers()"><i class="ti ti-send"></i> Submit</button><?php endif; ?>

<?php elseif ($actType === 'fill_in_blanks'): ?>
    <?php foreach ($actData['sentences'] ?? [] as $si => $sentence): ?>
    <div class="fib-sentence">
        <?php
        $text  = $sentence['text'] ?? '';
        $parts = explode('___', $text);
        echo htmlspecialchars($parts[0]);
        echo '<input type="text" class="fib-input" data-si="' . $si . '" placeholder="...">';
        if (isset($parts[1])) echo htmlspecialchars($parts[1]);
        ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$hasSub): ?><button class="btn-submit" id="submitBtn" onclick="submitAnswers()"><i class="ti ti-send"></i> Submit</button><?php endif; ?>

<?php elseif ($actType === 'matching'): ?>
    <?php $pairs = $actData['pairs'] ?? []; $rights = array_column($pairs, 'right'); shuffle($rights); ?>
    <?php foreach ($pairs as $pi => $pair): ?>
    <div class="matching-row">
        <div class="matching-left"><?php echo htmlspecialchars($pair['left']); ?></div>
        <select class="matching-select" data-pi="<?php echo $pi; ?>">
            <option value="">Select</option>
            <?php foreach ($rights as $ri => $r): ?>
            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endforeach; ?>
    <?php if (!$hasSub): ?><button class="btn-submit" id="submitBtn" onclick="submitAnswers()"><i class="ti ti-send"></i> Submit</button><?php endif; ?>

<?php elseif ($actType === 'drag_drop_sort' || $actType === 'sequencing'): ?>
    <?php $items = $actData['items'] ?? $actData['steps'] ?? []; ?>
    <p style="font-size:.9rem;color:#6c757d;margin-bottom:10px;"><i class="ti ti-arrows-sort"></i> Drag items to arrange them in the correct order.</p>
    <ul class="drag-list" id="dragList">
        <?php foreach ($items as $ii => $item): ?>
        <li class="drag-item" draggable="true" data-index="<?php echo $ii; ?>">
            <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
            <?php echo htmlspecialchars(is_array($item) ? ($item['text'] ?? $item['label'] ?? '') : $item); ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php if (!$hasSub): ?><button class="btn-submit" id="submitBtn" onclick="submitAnswers()"><i class="ti ti-send"></i> Submit</button><?php endif; ?>

<?php elseif ($actType === 'image_label'): ?>
    <?php $imgPath = $actData['image_path'] ?? ''; $labels = $actData['labels'] ?? []; ?>
    <div class="image-label-wrapper">
        <img src="<?php echo htmlspecialchars($basePath . '/' . ltrim($imgPath, '/')); ?>" alt="Label this image">
        <?php foreach ($labels as $li => $lbl): ?>
        <div class="label-dot" style="left:<?php echo (float)($lbl['x'] ?? 0); ?>%;top:<?php echo (float)($lbl['y'] ?? 0); ?>%;"><?php echo $li+1; ?></div>
        <?php endforeach; ?>
    </div>
    <div class="label-inputs">
        <?php foreach ($labels as $li => $lbl): ?>
        <div class="label-input-row">
            <div class="label-input-num"><?php echo $li+1; ?></div>
            <input type="text" class="label-input" data-li="<?php echo $li; ?>" placeholder="Label <?php echo $li+1; ?>">
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$hasSub): ?><button class="btn-submit" id="submitBtn" onclick="submitAnswers()"><i class="ti ti-send"></i> Submit</button><?php endif; ?>

<?php elseif ($actType === 'flashcards'): ?>
    <?php $cards = $actData['cards'] ?? []; $totalCards = count($cards); ?>
    <?php if ($totalCards > 0): ?>
    <div class="flashcard-container">
        <div class="flashcard" id="flashcard" onclick="flipCard()">
            <div class="flashcard-face flashcard-front" id="fcFront"><?php echo htmlspecialchars($cards[0]['front'] ?? ''); ?></div>
            <div class="flashcard-face flashcard-back" id="fcBack"><?php echo htmlspecialchars($cards[0]['back'] ?? ''); ?></div>
        </div>
    </div>
    <div class="flashcard-nav">
        <button class="btn-fc-nav" onclick="prevCard()"><i class="ti ti-chevron-left"></i></button>
        <span class="fc-counter" id="fcCounter">1 / <?php echo $totalCards; ?></span>
        <button class="btn-fc-nav" onclick="nextCard()"><i class="ti ti-chevron-right"></i></button>
        <button class="btn-flip" onclick="flipCard()"><i class="ti ti-rotate"></i> Flip</button>
    </div>
    <div id="fcDoneArea" style="display:none;margin-top:16px;">
        <div class="result-box excellent"><div class="result-score"></div><div class="result-msg">Great job reviewing!</div></div>
        <a href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lessonPlanId; ?>" class="btn-back-lesson"><i class="ti ti-arrow-left"></i> Back to Lesson</a>
    </div>
    <button class="btn-submit btn-i-read" id="fcDoneBtn" onclick="markFlashcardsDone()">
      <i class="ti ti-check"></i> ✅ I read it!
    </button>
    <?php endif; ?>
<?php endif; ?>

</div>

<div id="resultArea" style="display:none;"></div>

</div>
</div><!-- /.main-content -->
<script>
const BASE = '<?php echo addslashes($basePath); ?>';
const ACTIVITY_ID = <?php echo $actId; ?>;
const ACTIVITY_TYPE = '<?php echo htmlspecialchars($actType); ?>';
const LESSON_PLAN_ID = <?php echo $lessonPlanId; ?>;
const CARDS_DATA = <?php echo json_encode($actData['cards'] ?? []); ?>;

const mcAnswers = {};
function selectOption(el, qi, oi) {
    document.querySelectorAll('[data-qi="' + qi + '"]').forEach(function(c){ c.classList.remove('selected'); });
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
    mcAnswers[qi] = oi;
}

let tfAnswer = null;
function selectTF(el, val) {
    document.querySelectorAll('.tf-card').forEach(function(c){ c.classList.remove('selected'); });
    el.classList.add('selected');
    tfAnswer = val;
}

let dragSrc = null;
document.addEventListener('DOMContentLoaded', function() {
    var list = document.getElementById('dragList');
    if (!list) return;
    list.addEventListener('dragstart', function(e) {
        dragSrc = e.target.closest('.drag-item');
        if (dragSrc) dragSrc.classList.add('dragging');
    });
    list.addEventListener('dragover', function(e) {
        e.preventDefault();
        var target = e.target.closest('.drag-item');
        if (target && target !== dragSrc) {
            var rect = target.getBoundingClientRect();
            if (e.clientY < rect.top + rect.height / 2) { list.insertBefore(dragSrc, target); }
            else { list.insertBefore(dragSrc, target.nextSibling); }
        }
    });
    list.addEventListener('dragend', function() {
        if (dragSrc) dragSrc.classList.remove('dragging');
        dragSrc = null;
    });
});

var fcIndex = 0, fcFlipped = false;
function flipCard() {
    var card = document.getElementById('flashcard');
    if (!card) return;
    fcFlipped = !fcFlipped;
    card.classList.toggle('flipped', fcFlipped);
}
function nextCard() {
    if (fcIndex < CARDS_DATA.length - 1) { fcIndex++; fcFlipped = false; updateCard(); }
    else { document.getElementById('fcDoneArea').style.display = 'block'; document.getElementById('fcDoneBtn').style.display = 'none'; }
}
function prevCard() {
    if (fcIndex > 0) { fcIndex--; fcFlipped = false; updateCard(); }
}
function updateCard() {
    var card = document.getElementById('flashcard');
    if (!card) return;
    card.classList.remove('flipped');
    document.getElementById('fcFront').textContent = CARDS_DATA[fcIndex].front || '';
    document.getElementById('fcBack').textContent  = CARDS_DATA[fcIndex].back  || '';
    document.getElementById('fcCounter').textContent = (fcIndex + 1) + ' / ' + CARDS_DATA.length;
}
function markFlashcardsDone() {
    document.getElementById('fcDoneArea').style.display = 'block';
    document.getElementById('fcDoneBtn').style.display = 'none';
    fetch(BASE + '/learning/activity/' + ACTIVITY_ID + '/submit', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({answers: {done: true}})
    }).catch(function(){});
}

function submitAnswers() {
    var answers = {};
    if (ACTIVITY_TYPE === 'multiple_choice') {
        answers = mcAnswers;
    } else if (ACTIVITY_TYPE === 'true_false') {
        if (tfAnswer === null) { Swal.fire({icon:'warning',title:'Select an answer',text:'Please choose True or False.',confirmButtonColor:'#a01422'}); return; }
        answers = {0: tfAnswer};
    } else if (ACTIVITY_TYPE === 'fill_in_blanks') {
        document.querySelectorAll('.fib-input').forEach(function(inp){ answers[inp.dataset.si] = inp.value.trim(); });
    } else if (ACTIVITY_TYPE === 'matching') {
        document.querySelectorAll('.matching-select').forEach(function(sel){ answers[sel.dataset.pi] = sel.value; });
    } else if (ACTIVITY_TYPE === 'drag_drop_sort' || ACTIVITY_TYPE === 'sequencing') {
        answers = Array.from(document.querySelectorAll('#dragList .drag-item')).map(function(li){ return parseInt(li.dataset.index); });
    } else if (ACTIVITY_TYPE === 'image_label') {
        document.querySelectorAll('.label-input').forEach(function(inp){ answers[inp.dataset.li] = inp.value.trim(); });
    }
    var btn = document.getElementById('submitBtn');
    if (btn) btn.disabled = true;
    Swal.fire({title:'Submitting...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); }});
    fetch(BASE + '/learning/activity/' + ACTIVITY_ID + '/submit', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({answers: answers})
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        Swal.close();
        if (!data.success) { Swal.fire({icon:'error',title:'Error',text:data.message||'Submission failed.',confirmButtonColor:'#a01422'}); if(btn)btn.disabled=false; return; }
        showResult(data);
    })
    .catch(function() {
        Swal.close();
        Swal.fire({icon:'error',title:'Network Error',text:'Could not submit. Please try again.',confirmButtonColor:'#a01422'});
        if(btn)btn.disabled=false;
    });
}

function showResult(data) {
    var area = document.getElementById('resultArea');
    if (!area) return;
    area.style.display = 'block';
    var backBtn = '<a href="' + BASE + '/learning/lesson/' + LESSON_PLAN_ID + '" class="btn-back-lesson"><i class="ti ti-arrow-left"></i> Back to Lesson</a>';
    if (data.auto_score !== null && data.auto_score !== undefined) {
        var max = data.max_score || 1;
        var pct = Math.round((data.auto_score / max) * 100);
        var stars = pct >= 90 ? 3 : (pct >= 70 ? 2 : 1);
        var starsHtml = '';
        for (var s = 1; s <= 3; s++) starsHtml += (s <= stars ? '⭐' : '☆');
        var cls = pct >= 80 ? 'excellent' : (pct >= 50 ? 'good' : 'needs-work');
        var msg = stars === 3 ? 'Amazing! You got ' + data.auto_score + ' / ' + max + '! 🎉'
                : stars === 2 ? 'Great job! You got ' + data.auto_score + ' / ' + max + '! 👍'
                : 'Good try! You got ' + data.auto_score + ' / ' + max + '. Keep practicing! 💪';
        area.innerHTML = '<div class="result-box ' + cls + '"><div class="g-stars-result">' + starsHtml + '</div><div class="result-score">' + data.auto_score + ' / ' + max + '</div><div class="result-msg">' + msg + '</div></div>' + backBtn;
    } else {
        area.innerHTML = '<div class="result-box excellent"><div class="result-score">✅</div><div class="result-msg">' + (data.message || 'Your answer has been sent! Your teacher will check it soon.') + '</div></div>' + backBtn;
    }
    var actArea = document.getElementById('activityArea');
    if (actArea) actArea.style.display = 'none';
}
</script>
</div></div><!-- /.main-content -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
