<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Learner Progress View (matching reference design)

$pageTitle = 'My Progress — SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$pct = ($overallTotal > 0) ? round(($overallComplete / $overallTotal) * 100) : 0;

if ($pct === 0)     $progressMsg = "Keep going! You're doing great! 🎉";
elseif ($pct < 50)  $progressMsg = "You are doing great! Keep going! 💪";
elseif ($pct < 100) $progressMsg = "Almost there! You are doing amazing! 🌟";
else                $progressMsg = "You finished everything! You are a superstar! 🏆";

$r = 54; $circ = round(2 * M_PI * $r, 2);
$dashOff = round($circ * (1 - $pct / 100), 2);

// Count submissions for stat card
$totalSubs = 0;
if (!empty($recentGrades)) $totalSubs = count($recentGrades);
?>
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div style="max-width:820px;">

<style>
.lp-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.lp-head h1{font-size:1.3rem;font-weight:700;color:#1e4072;display:flex;align-items:center;gap:8px;margin:0}
.lp-back{display:inline-flex;align-items:center;gap:6px;background:#1e4072;color:#fff;border-radius:20px;padding:8px 18px;font-size:.88rem;font-weight:600;text-decoration:none;border:none;cursor:pointer}
.lp-back:hover{opacity:.85;color:#fff}

.lp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
.lp-stat{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:20px 12px;text-align:center;border:2px solid #ddd}
.lp-stat--star{border-color:#222}
.lp-stat--done{border-color:#3b6d11}
.lp-stat--mat {border-color:#4a90d9}
.lp-stat--sub {border-color:#ef9f27}
.lp-stat__ico{font-size:2rem;display:block;margin-bottom:8px}
.lp-stat__num{font-size:2rem;font-weight:700;color:#222;line-height:1}
.lp-stat__lbl{font-size:.82rem;color:#6c757d;margin-top:5px}

.lp-ring-card{background:#fff;border-radius:16px;border:1px solid #e8e8e8;padding:24px 20px;margin-bottom:22px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.lp-ring-card h2{font-size:1rem;font-weight:700;color:#2c2c2c;margin-bottom:16px;display:flex;align-items:center;justify-content:center;gap:6px}
.lp-ring-msg{font-size:.93rem;color:#6c757d;margin-top:12px}

.lp-section{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:18px;margin-bottom:18px}
.lp-section h3{font-size:.95rem;font-weight:700;color:#1e4072;margin:0 0 14px;display:flex;align-items:center;gap:7px}
.dtable{width:100%;border-collapse:collapse;font-size:.88rem}
.dtable th{background:#1e4072;color:#fff;padding:9px 12px;text-align:left;font-weight:600}
.dtable td{padding:10px 12px;border-bottom:1px solid #f0f0f0;color:#2c2c2c}
.dtable tr:last-child td{border-bottom:none}
.dtable tr:nth-child(even) td{background:#f9f9f9}
.dbar{height:8px;border-radius:4px;background:#e0e0e0;overflow:hidden;min-width:70px}
.dbar-f{height:100%;background:#a01422;border-radius:4px}
.sbadge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:700;color:#fff}
.sbadge-hi{background:#3b6d11}.sbadge-mi{background:#e67e22}.sbadge-lo{background:#a01422}

@media(max-width:600px){
  .lp-stats{grid-template-columns:1fr 1fr}
  .lp-head{flex-direction:column;align-items:flex-start}
}
</style>

<div class="lp-head">
  <h1>⭐ My Progress</h1>
  <a href="<?php echo $basePath; ?>/learning/dashboard" class="lp-back">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<!-- 4 Stat cards — matching reference exactly -->
<div class="lp-stats">
  <div class="lp-stat lp-stat--star">
    <span class="lp-stat__ico">⭐</span>
    <div class="lp-stat__num"><?php echo number_format($totalStars ?? 0); ?></div>
    <div class="lp-stat__lbl">Total Stars</div>
  </div>
  <div class="lp-stat lp-stat--done">
    <span class="lp-stat__ico">✅</span>
    <div class="lp-stat__num"><?php echo (int)$overallComplete; ?></div>
    <div class="lp-stat__lbl">Completed</div>
  </div>
  <div class="lp-stat lp-stat--mat">
    <span class="lp-stat__ico">📚</span>
    <div class="lp-stat__num"><?php echo (int)$overallTotal; ?></div>
    <div class="lp-stat__lbl">Total Materials</div>
  </div>
  <div class="lp-stat lp-stat--sub">
    <span class="lp-stat__ico">📝</span>
    <div class="lp-stat__num"><?php echo $totalSubs; ?></div>
    <div class="lp-stat__lbl">Submissions</div>
  </div>
</div>

<!-- Overall progress ring -->
<div class="lp-ring-card">
  <h2>📊 Overall Progress</h2>
  <svg width="140" height="140" viewBox="0 0 130 130" role="img" aria-label="<?php echo $pct; ?> percent complete">
    <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#e9e9e9" stroke-width="12"/>
    <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#ef9f27" stroke-width="12"
            stroke-dasharray="<?php echo $circ; ?>" stroke-dashoffset="<?php echo $dashOff; ?>"
            stroke-linecap="round" transform="rotate(-90 65 65)"/>
    <text x="65" y="68" text-anchor="middle" font-size="22" font-weight="700" fill="#ef9f27"><?php echo $pct; ?>%</text>
  </svg>
  <div class="lp-ring-msg"><?php echo htmlspecialchars($progressMsg); ?></div>
</div>

<!-- Domain progress (kept but simplified for PWD friendliness) -->
<?php if (!empty($domainProgress)): ?>
<div class="lp-section">
  <h3><i class="bi bi-grid"></i> Progress by Domain</h3>
  <div style="overflow-x:auto;">
  <table class="dtable">
    <thead><tr><th>Domain</th><th>Done</th><th>Progress</th><th>Avg Score</th></tr></thead>
    <tbody>
    <?php foreach ($domainProgress as $dp):
      $dpPct = ($dp['total'] > 0) ? round(($dp['completed'] / $dp['total']) * 100) : 0;
    ?>
    <tr>
      <td><?php echo htmlspecialchars($dp['domain']); ?></td>
      <td><?php echo (int)$dp['completed']; ?> / <?php echo (int)$dp['total']; ?></td>
      <td><div class="dbar"><div class="dbar-f" style="width:<?php echo $dpPct; ?>%"></div></div>
          <span style="font-size:.73rem;color:#6c757d;"><?php echo $dpPct; ?>%</span></td>
      <td><?php if ($dp['avg_score'] !== null):
            $avg = (float)$dp['avg_score'];
            $cls = $avg >= 80 ? 'sbadge-hi' : ($avg >= 50 ? 'sbadge-mi' : 'sbadge-lo');
          ?><span class="sbadge <?php echo $cls; ?>"><?php echo round($avg, 1); ?>%</span>
          <?php else: ?><span style="color:#aaa;">—</span><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<!-- Recent grades -->
<?php if (!empty($recentGrades)): ?>
<div class="lp-section">
  <h3><i class="bi bi-star"></i> Recent Grades</h3>
  <div style="overflow-x:auto;">
  <table class="dtable">
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
<div class="lp-section" style="text-align:center;color:#6c757d;padding:32px 20px;">
  <span style="font-size:2.5rem;display:block;margin-bottom:10px;">📝</span>
  <p>No grades yet. Your teacher will grade your submissions here.</p>
</div>
<?php endif; ?>

</div>
</div><!-- /.main-content -->

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
