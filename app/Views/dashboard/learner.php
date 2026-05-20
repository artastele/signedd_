<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Learner Dashboard (matching reference design)

$pageTitle = 'My Learning — SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

$basePath   = defined('BASE_PATH') ? BASE_PATH : '';
$firstName  = explode(' ', trim($studentName ?? 'Learner'))[0];
$pct        = ($overallTotal > 0) ? round(($overallComplete / $overallTotal) * 100) : 0;

if ($pct === 0)     $progressMsg = "Keep going! You're doing great! 🎉";
elseif ($pct < 50)  $progressMsg = "You are doing great! Keep going! 💪";
elseif ($pct < 100) $progressMsg = "Almost there! You are doing amazing! 🌟";
else                $progressMsg = "You finished everything! You are a superstar! 🏆";

if (!isset($badges))             $badges             = [];
if (!isset($totalStarsPossible)) $totalStarsPossible = 0;
if (!isset($avgScore))           $avgScore           = 0;

// SVG ring
$r = 54; $circ = round(2 * M_PI * $r, 2);
$dashOff = round($circ * (1 - $pct / 100), 2);
?>
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">

<style>
/* ── Learner-specific additions — Process 7 ── */
.lrn-banner{background:linear-gradient(90deg,#ef9f27 0%,#f5bc50 100%);border-radius:16px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:16px}
.lrn-banner__left{display:flex;align-items:center;gap:14px}
.lrn-banner__emoji{font-size:2rem;flex-shrink:0}
.lrn-banner__text{font-size:1.2rem;font-weight:700;color:#fff;line-height:1.3}
.lrn-banner__stars{background:rgba(255,255,255,.25);border-radius:12px;padding:10px 18px;display:flex;align-items:center;gap:8px;flex-shrink:0;color:#fff;font-size:1rem;font-weight:700}
.lrn-banner__stars span{font-size:1.4rem}

.lrn-ring-card{background:#fff;border-radius:16px;border:2px solid #222;padding:24px 20px;margin-bottom:22px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.lrn-ring-card h3{font-size:1rem;font-weight:700;color:#2c2c2c;margin-bottom:16px;display:flex;align-items:center;justify-content:center;gap:6px}
.lrn-ring-msg{font-size:.93rem;color:#6c757d;margin-top:12px}

.lrn-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.lrn-stat{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:20px 14px;text-align:center;border:2px solid #ddd}
.lrn-stat--mod {border-color:#4a90d9}
.lrn-stat--done{border-color:#3b6d11}
.lrn-stat--star{border-color:#ef9f27}
.lrn-stat__ico{font-size:2rem;margin-bottom:8px;display:block}
.lrn-stat__num{font-size:2.2rem;font-weight:700;color:#222;line-height:1}
.lrn-stat__lbl{font-size:.85rem;color:#6c757d;margin-top:5px}

.lrn-tabs{display:flex;border-bottom:2px solid #e8e8e8;margin-bottom:20px}
.lrn-tab{background:none;border:none;cursor:pointer;padding:11px 20px;font-size:.92rem;font-weight:600;color:#999;border-bottom:3px solid transparent;margin-bottom:-2px;transition:color .15s,border-color .15s}
.lrn-tab.active{color:#a01422;border-bottom-color:#a01422}
.lrn-tab:hover:not(.active){color:#555}
.lrn-panel{display:none}
.lrn-panel.active{display:block}

.lrn-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.lrn-lcard{background:#fff;border-radius:16px;border-top:4px solid #bbb;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:18px;display:flex;flex-direction:column}
.lrn-lcard--done{border-top-color:#3b6d11}
.lrn-lcard--prog{border-top-color:#ef9f27}
.lrn-domain{display:inline-block;padding:3px 10px;border-radius:20px;background:#1e4072;color:#fff;font-size:.7rem;font-weight:700;margin-bottom:8px}
.lrn-ltitle{font-size:.95rem;font-weight:600;color:#2c2c2c;margin-bottom:8px;flex:1}
.lrn-mini{height:8px;border-radius:4px;background:#e9e9e9;overflow:hidden;margin-bottom:4px}
.lrn-mini__f{height:100%;background:#a01422;border-radius:4px}
.lrn-status{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:12px;font-size:.72rem;font-weight:700;margin-bottom:10px}
.lrn-status--done{background:#e8f0e0;color:#3b6d11}
.lrn-status--prog{background:#faeeda;color:#854f0b}
.lrn-status--none{background:#f0f0f0;color:#666}
.lrn-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:52px;border-radius:20px;font-size:.92rem;font-weight:700;text-decoration:none;border:none;cursor:pointer;margin-top:auto}
.lrn-btn:hover{opacity:.88;text-decoration:none}
.lrn-btn--start,.lrn-btn--cont{background:#a01422;color:#fff}
.lrn-btn--done{background:#3b6d11;color:#fff}

.lrn-prog-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0}
.lrn-prog-stat{background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.07);text-align:center;border:2px solid #ddd}
.lrn-prog-stat--star{border-color:#222}
.lrn-prog-stat--done{border-color:#3b6d11}
.lrn-prog-stat--mat {border-color:#4a90d9}
.lrn-prog-stat--sub {border-color:#ef9f27}
.lrn-prog-stat .ico{font-size:1.6rem;display:block;margin-bottom:6px}
.lrn-prog-stat .val{font-size:1.8rem;font-weight:700;color:#222;line-height:1}
.lrn-prog-stat .lbl{font-size:.8rem;color:#6c757d;margin-top:4px}
.lrn-prog-msg{background:#e8f0e0;border-radius:14px;padding:16px;color:#3b6d11;font-size:.95rem;font-weight:600;text-align:center}

.lrn-shelf{display:flex;flex-wrap:wrap;gap:16px;padding:4px 0}
.lrn-badge{display:flex;flex-direction:column;align-items:center;width:88px;text-align:center}
.lrn-badge__circle{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:6px}
.lrn-badge__circle i{font-size:1.6rem}
.lrn-badge--earned .lrn-badge__circle{background:#faeeda}
.lrn-badge--earned .lrn-badge__circle i{color:#854f0b}
.lrn-badge--locked{opacity:.38}
.lrn-badge--locked .lrn-badge__circle{background:#f0f0f0}
.lrn-badge--locked .lrn-badge__circle i{color:#bbb}
.lrn-badge__name{font-size:.75rem;font-weight:600;color:#2c2c2c;line-height:1.3}

@media(max-width:768px){
  .lrn-grid{grid-template-columns:1fr}
  .lrn-stats{grid-template-columns:repeat(3,1fr)}
  .lrn-banner__text{font-size:1rem}
  .lrn-banner__stars{padding:8px 12px}
}
@media(max-width:480px){
  .lrn-stats{grid-template-columns:1fr 1fr}
  .lrn-tab{padding:10px 12px;font-size:.84rem}
}
</style>

<!-- Welcome banner -->
<div class="lrn-banner">
  <div class="lrn-banner__left">
    <div class="lrn-banner__emoji">👋</div>
    <div class="lrn-banner__text">
      <?php if ($pct === 100): ?>
        Amazing, <?php echo htmlspecialchars($firstName); ?>! You finished everything! 🏆
      <?php else: ?>
        Hi <?php echo htmlspecialchars($firstName); ?>!<br>
        <span style="font-size:.95rem;font-weight:500;">Ready to learn something awesome today?</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="lrn-banner__stars">
    <span>⭐</span> <?php echo number_format($totalStars ?? 0); ?> Stars
  </div>
</div>

<!-- Progress ring card -->
<div class="lrn-ring-card">
  <h3>📊 My Progress</h3>
  <svg width="130" height="130" viewBox="0 0 130 130" role="img" aria-label="<?php echo $pct; ?> percent complete">
    <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#e9e9e9" stroke-width="12"/>
    <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#ef9f27" stroke-width="12"
            stroke-dasharray="<?php echo $circ; ?>" stroke-dashoffset="<?php echo $dashOff; ?>"
            stroke-linecap="round" transform="rotate(-90 65 65)"/>
    <text x="65" y="68" text-anchor="middle" font-size="22" font-weight="700" fill="#ef9f27"><?php echo $pct; ?>%</text>
  </svg>
  <div class="lrn-ring-msg"><?php echo htmlspecialchars($progressMsg); ?></div>
</div>

<!-- Stat cards -->
<div class="lrn-stats">
  <div class="lrn-stat lrn-stat--mod">
    <span class="lrn-stat__ico">📚</span>
    <div class="lrn-stat__num"><?php echo count($lessonPlans ?? []); ?></div>
    <div class="lrn-stat__lbl">Modules</div>
  </div>
  <div class="lrn-stat lrn-stat--done">
    <span class="lrn-stat__ico">✅</span>
    <div class="lrn-stat__num"><?php echo (int)$overallComplete; ?></div>
    <div class="lrn-stat__lbl">Completed</div>
  </div>
  <div class="lrn-stat lrn-stat--star">
    <span class="lrn-stat__ico">⭐</span>
    <div class="lrn-stat__num"><?php echo number_format($totalStars ?? 0); ?></div>
    <div class="lrn-stat__lbl">Stars Earned</div>
  </div>
</div>

<!-- Tabs -->
<div class="lrn-tabs" role="tablist">
  <button id="tab-lessons"  class="lrn-tab active" role="tab" aria-selected="true"  onclick="lTab('lessons')">My Lessons</button>
  <button id="tab-progress" class="lrn-tab"        role="tab" aria-selected="false" onclick="lTab('progress')">My Progress</button>
  <button id="tab-badges"   class="lrn-tab"        role="tab" aria-selected="false" onclick="lTab('badges')">My Badges</button>
</div>

<!-- TAB 1: MY LESSONS -->
<div id="panel-lessons" class="lrn-panel active" role="tabpanel">
<?php if (empty($lessonPlans)): ?>
  <div class="text-center py-5 text-muted">
    <span style="font-size:3rem;display:block;margin-bottom:12px;">📚</span>
    <p style="font-size:1rem;">Your teacher hasn't published any lessons yet.<br>Check back soon!</p>
  </div>
<?php else: ?>
<div class="lrn-grid">
<?php foreach ($lessonPlans as $lp):
  $at=(int)($lp['activity_count']??0); $ad=(int)($lp['completed_count']??0);
  $lp_pct=($at>0)?round(($ad/$at)*100):0;
  if($ad>=$at&&$at>0){$cm='done';$sm='done';$si='bi-check-circle-fill';$st='Done';       $bm='done';$bt='View lesson';}
  elseif($ad>0)       {$cm='prog';$sm='prog';$si='bi-clock-fill';      $st='In progress';$bm='cont';$bt='Continue';}
  else                {$cm='';   $sm='none';$si='bi-circle';           $st='Not started';$bm='start';$bt='Start';}
?>
<div class="lrn-lcard<?php echo $cm?' lrn-lcard--'.$cm:''; ?>">
  <span class="lrn-domain"><?php echo htmlspecialchars($lp['pdsp_domain']); ?></span>
  <div class="lrn-ltitle"><?php echo htmlspecialchars($lp['title']); ?></div>
  <div class="lrn-mini"><div class="lrn-mini__f" style="width:<?php echo $lp_pct; ?>%"></div></div>
  <div style="font-size:.78rem;color:#6c757d;margin-bottom:8px;"><?php echo $ad; ?> / <?php echo $at; ?> complete</div>
  <span class="lrn-status lrn-status--<?php echo $sm; ?>">
    <i class="bi <?php echo $si; ?>"></i><?php echo $st; ?>
  </span>
  <a id="btn-lesson-<?php echo (int)$lp['id']; ?>"
     href="<?php echo $basePath; ?>/learning/lesson/<?php echo (int)$lp['id']; ?>"
     class="lrn-btn lrn-btn--<?php echo $bm; ?>">
    <i class="bi bi-arrow-right-circle-fill"></i><?php echo htmlspecialchars($bt); ?>
  </a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- TAB 2: MY PROGRESS -->
<div id="panel-progress" class="lrn-panel" role="tabpanel">
  <div class="lrn-ring-card">
    <h3>📊 Overall Progress</h3>
    <svg width="130" height="130" viewBox="0 0 130 130" role="img" aria-label="<?php echo $pct; ?> percent complete">
      <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#e9e9e9" stroke-width="12"/>
      <circle cx="65" cy="65" r="<?php echo $r; ?>" fill="none" stroke="#ef9f27" stroke-width="12"
              stroke-dasharray="<?php echo $circ; ?>" stroke-dashoffset="<?php echo $dashOff; ?>"
              stroke-linecap="round" transform="rotate(-90 65 65)"/>
      <text x="65" y="68" text-anchor="middle" font-size="22" font-weight="700" fill="#ef9f27"><?php echo $pct; ?>%</text>
    </svg>
    <div class="lrn-ring-msg"><?php echo htmlspecialchars($progressMsg); ?></div>
  </div>
  <div class="lrn-prog-grid">
    <div class="lrn-prog-stat lrn-prog-stat--star"><span class="ico">⭐</span><div class="val"><?php echo number_format($totalStars??0); ?></div><div class="lbl">Total Stars</div></div>
    <div class="lrn-prog-stat lrn-prog-stat--done"><span class="ico">✅</span><div class="val"><?php echo (int)$overallComplete; ?></div><div class="lbl">Completed</div></div>
    <div class="lrn-prog-stat lrn-prog-stat--mat"><span class="ico">📚</span><div class="val"><?php echo count($lessonPlans??[]); ?></div><div class="lbl">Total Modules</div></div>
    <div class="lrn-prog-stat lrn-prog-stat--sub"><span class="ico">📝</span><div class="val"><?php echo (int)$overallComplete; ?></div><div class="lbl">Submissions</div></div>
  </div>
  <div class="lrn-prog-msg"><?php echo htmlspecialchars($progressMsg); ?></div>
</div>

<!-- TAB 3: MY BADGES -->
<div id="panel-badges" class="lrn-panel" role="tabpanel">
  <?php $ec = count(array_filter($badges, fn($b) => $b['earned'])); ?>
  <p style="font-size:.9rem;color:#6c757d;margin-bottom:14px;">
    🏅 <strong style="color:#1e4072;"><?php echo $ec; ?></strong> of
    <strong style="color:#1e4072;"><?php echo count($badges); ?></strong> badges earned!
  </p>
  <?php if (empty($badges)): ?>
    <div class="text-center py-4 text-muted">
      <span style="font-size:3rem;display:block;margin-bottom:10px;">🏅</span>
      <p>Complete activities to earn your first badge!</p>
    </div>
  <?php else: ?>
  <div class="lrn-shelf">
  <?php foreach ($badges as $b): ?>
    <div class="lrn-badge lrn-badge--<?php echo $b['earned'] ? 'earned' : 'locked'; ?>"
         title="<?php echo $b['earned'] ? htmlspecialchars($b['name']) : 'Keep going to unlock!'; ?>">
      <div class="lrn-badge__circle"><i class="ti <?php echo htmlspecialchars($b['icon']); ?>"></i></div>
      <div class="lrn-badge__name"><?php echo htmlspecialchars($b['name']); ?></div>
    </div>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

</div><!-- /.main-content -->

<?php if (isset($_SESSION['success'])): ?>
<script>Swal?.fire({icon:'success',title:'',text:'<?php echo addslashes($_SESSION['success']); ?>',confirmButtonColor:'#3b6d11',timer:3000,timerProgressBar:true});</script>
<?php unset($_SESSION['success']); endif; ?>

<script>
function lTab(name) {
  document.querySelectorAll('.lrn-tab').forEach(function(t) {
    var on = t.id === 'tab-' + name;
    t.classList.toggle('active', on);
    t.setAttribute('aria-selected', on);
  });
  document.querySelectorAll('.lrn-panel').forEach(function(p) {
    p.classList.toggle('active', p.id === 'panel-' + name);
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
