<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Step 17 — Parent: one child's scores/XP/stars — NO lesson content shown

$pageTitle = htmlspecialchars($studentName) . "'s Progress — SPED LMS";
require_once __DIR__ . '/../layouts/header.php';

$r = 54; $circ = round(2 * M_PI * $r, 2);
$dashOff = round($circ * (1 - $pct / 100), 2);

if ($pct === 0)     $note = "Let's get started! Lessons are ready.";
elseif ($pct < 50)  $note = "Great start! Keep going!";
elseif ($pct < 100) $note = "Almost there! Doing amazing!";
else                $note = "Finished everything! 🏆";
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
<div class="main-content">
<style>
.ps-back{display:inline-flex;align-items:center;gap:6px;color:#1e4072;text-decoration:none;font-size:.88rem;font-weight:600;margin-bottom:16px}
.ps-back:hover{color:#a01422}
.ps-head{font-size:1.2rem;font-weight:700;color:#1e4072;margin-bottom:18px}
.ps-ring{background:#fff;border-radius:16px;border:1px solid #e8e8e8;padding:22px;text-align:center;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.ps-ring h2{font-size:.95rem;font-weight:700;color:#2c2c2c;margin-bottom:14px}
.ps-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
.ps-stat{background:#fff;border-radius:14px;padding:16px 10px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06);border:2px solid #ddd}
.ps-stat--xp  {border-color:#a01422}
.ps-stat--star{border-color:#ef9f27}
.ps-stat--done{border-color:#3b6d11}
.ps-stat--tot {border-color:#4a90d9}
.ps-stat .ico{font-size:1.5rem;display:block;margin-bottom:6px}
.ps-stat .val{font-size:1.6rem;font-weight:700;color:#222;line-height:1}
.ps-stat .lbl{font-size:.75rem;color:#6c757d;margin-top:4px}
.ps-section{background:#fff;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:16px}
.ps-section h3{font-size:.9rem;font-weight:700;color:#1e4072;margin:0 0 12px;display:flex;align-items:center;gap:6px}
.gtable{width:100%;border-collapse:collapse;font-size:.86rem}
.gtable th{background:#1e4072;color:#fff;padding:8px 12px;text-align:left;font-weight:600}
.gtable td{padding:9px 12px;border-bottom:1px solid #f0f0f0;color:#2c2c2c}
.gtable tr:last-child td{border-bottom:none}
.gtable tr:nth-child(even) td{background:#f9f9f9}
.sbadge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:700;color:#fff}
.sbadge-hi{background:#3b6d11}.sbadge-mi{background:#e67e22}.sbadge-lo{background:#a01422}
.ps-note{background:#e8f0e0;border-radius:12px;padding:14px 18px;color:#3b6d11;font-size:.92rem;font-weight:600;text-align:center;margin-bottom:18px}
@media(max-width:600px){.ps-stats{grid-template-columns:1fr 1fr}}
</style>

<a href="<?php echo $basePath; ?>/parent/child-progress" class="ps-back"><i class="bi bi-arrow-left"></i> Back</a>
<div class="ps-head">📊 <?php echo htmlspecialchars($studentName); ?>'s Progress</div>

<!-- Progress ring -->
<div class="ps-ring">
  <h2>Overall Progress</h2>
  <svg width="130" height="130" viewBox="0 0 130 130" role="img" aria-label="<?php echo $pct; ?> percent complete">
    <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#e9e9e9" stroke-width="12"/>
    <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#ef9f27" stroke-width="12"
            stroke-dasharray="<?php echo $circ; ?>" stroke-dashoffset="<?php echo $dashOff; ?>"
            stroke-linecap="round" transform="rotate(-90 65 65)"/>
    <text x="65" y="68" text-anchor="middle" font-size="22" font-weight="700" fill="#ef9f27"><?php echo $pct; ?>%</text>
  </svg>
</div>

<div class="ps-note"><?php echo htmlspecialchars($note); ?></div>

<!-- Stats row -->
<div class="ps-stats">
  <div class="ps-stat ps-stat--xp"><span class="ico">🏆</span><div class="val"><?php echo $totalXP; ?></div><div class="lbl">Total XP</div></div>
  <div class="ps-stat ps-stat--star"><span class="ico">⭐</span><div class="val"><?php echo $totalStars; ?></div><div class="lbl">Stars Earned</div></div>
  <div class="ps-stat ps-stat--done"><span class="ico">✅</span><div class="val"><?php echo $overallComplete; ?></div><div class="lbl">Completed</div></div>
  <div class="ps-stat ps-stat--tot"><span class="ico">📚</span><div class="val"><?php echo $overallTotal; ?></div><div class="lbl">Total Activities</div></div>
</div>

<!-- Grades — scores only, no lesson content -->
<?php if (!empty($recentGrades)): ?>
<div class="ps-section">
  <h3><i class="bi bi-star"></i> Activity Scores</h3>
  <div style="overflow-x:auto;">
  <table class="gtable">
    <thead><tr><th>Lesson Plan</th><th>Activity</th><th>Score</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($recentGrades as $g):
      $score = (int)($g['score'] ?? 0);
      $max   = (int)($g['max_score'] ?? 1);
      $gpct  = $max > 0 ? round(($score / $max) * 100) : 0;
      $gcls  = $gpct >= 80 ? 'sbadge-hi' : ($gpct >= 50 ? 'sbadge-mi' : 'sbadge-lo');
    ?>
    <tr>
      <td><?php echo htmlspecialchars($g['lesson_plan_title']); ?></td>
      <td><?php echo htmlspecialchars($g['activity_title']); ?></td>
      <td><span class="sbadge <?php echo $gcls; ?>"><?php echo $score; ?>/<?php echo $max; ?></span></td>
      <td style="white-space:nowrap;"><?php echo !empty($g['graded_at']) ? date('M j, Y', strtotime($g['graded_at'])) : '—'; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php else: ?>
<div class="ps-section" style="text-align:center;color:#6c757d;padding:28px;">
  <span style="font-size:2rem;display:block;margin-bottom:8px;">📝</span>
  <p style="font-size:.9rem;">No graded activities yet. Check back after the teacher grades submitted work.</p>
</div>
<?php endif; ?>

</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
