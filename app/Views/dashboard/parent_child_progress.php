<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Step 16 — Parent: list all children's progress (no lesson content shown)

$pageTitle = "My Child's Progress — SPED LMS";
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
<div class="main-content">
<style>
.pc-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px}
.pc-head h1{font-size:1.2rem;font-weight:700;color:#1e4072;margin:0;display:flex;align-items:center;gap:8px}
.pc-card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:20px;margin-bottom:14px;border-left:4px solid #1e4072;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.pc-name{font-weight:700;font-size:1rem;color:#1e4072;margin-bottom:2px}
.pc-grade{font-size:.8rem;color:#6c757d}
.pc-stats{display:flex;gap:16px;flex-wrap:wrap}
.pc-stat{text-align:center}
.pc-stat .val{font-size:1.2rem;font-weight:700;color:#a01422;line-height:1}
.pc-stat .lbl{font-size:.72rem;color:#6c757d}
.pc-pct-bar{height:8px;border-radius:4px;background:#e9e9e9;overflow:hidden;min-width:100px}
.pc-pct-fill{height:100%;background:#ef9f27;border-radius:4px}
.pc-btn{display:inline-flex;align-items:center;gap:6px;background:#1e4072;color:#fff;border-radius:10px;padding:9px 16px;font-size:.84rem;font-weight:600;text-decoration:none;white-space:nowrap}
.pc-btn:hover{opacity:.85;color:#fff}
.pc-empty{text-align:center;padding:48px 20px;color:#6c757d}
</style>

<div class="pc-head">
  <h1>👨‍👩‍👧 My Child's Progress</h1>
  <a href="<?php echo $basePath; ?>/dashboard" style="display:inline-flex;align-items:center;gap:6px;color:#1e4072;text-decoration:none;font-size:.88rem;font-weight:600;">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
  </a>
</div>

<?php if (empty($children)): ?>
  <div class="pc-empty">
    <span style="font-size:3rem;display:block;margin-bottom:12px;">📋</span>
    <p>No enrolled children yet. Once enrollment is approved, their progress will appear here.</p>
  </div>
<?php else: ?>
  <?php foreach ($children as $ch): ?>
  <div class="pc-card">
    <div>
      <div class="pc-name"><?php echo htmlspecialchars($ch['student_name']); ?></div>
      <div class="pc-grade">Grade <?php echo htmlspecialchars($ch['grade_level'] ?? ''); ?></div>
      <div style="margin-top:8px;">
        <div class="pc-pct-bar"><div class="pc-pct-fill" style="width:<?php echo $ch['pct']; ?>%"></div></div>
        <span style="font-size:.75rem;color:#6c757d;"><?php echo $ch['complete']; ?> / <?php echo $ch['total']; ?> activities · <?php echo $ch['pct']; ?>%</span>
      </div>
    </div>
    <div class="pc-stats">
      <div class="pc-stat"><div class="val">⭐ <?php echo $ch['total_stars']; ?></div><div class="lbl">Stars</div></div>
      <div class="pc-stat"><div class="val">🏆 <?php echo $ch['total_xp']; ?></div><div class="lbl">XP</div></div>
    </div>
    <a href="<?php echo $basePath; ?>/parent/child-progress/<?php echo (int)$ch['student_id']; ?>" class="pc-btn">
      <i class="bi bi-bar-chart-line"></i> View Details
    </a>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
