<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Modules List

$pageTitle = 'My Modules - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>/css/learner.css">

<body data-logged-in="true" class="learner-page">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <div class="section-header">
        <h1>📚 My Modules</h1>
        <a href="<?php echo $basePath; ?>/learning/dashboard" class="btn-cartoon btn-primary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <?php if (empty($modules)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No modules available yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($modules as $module): ?>
                <div class="col-md-4 mb-4">
                    <div class="module-card">
                        <div class="module-icon">
                            <?php 
                            $icons = ['📖', '📕', '📗', '📘', '📙', '📓'];
                            echo $icons[array_rand($icons)];
                            ?>
                        </div>
                        <h5 class="module-title"><?php echo htmlspecialchars($module['material_name']); ?></h5>
                        
                        <?php if ($module['progress_status'] === 'completed'): ?>
                            <div class="module-badge badge-completed">✓ Completed</div>
                            <?php if ($module['stars_earned'] > 0): ?>
                                <div class="mb-2">
                                    <?php for ($i = 0; $i < $module['stars_earned']; $i++): ?>
                                        <i class="bi bi-star-fill" style="color: var(--kid-yellow); font-size: 1.5rem;"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($module['progress_status'] === 'in_progress'): ?>
                            <div class="module-badge badge-progress">⏳ In Progress</div>
                        <?php else: ?>
                            <div class="module-badge badge-new">✨ New!</div>
                        <?php endif; ?>
                        
                        <a href="<?php echo $basePath; ?>/learning/module/<?php echo $module['id']; ?>" 
                           class="btn-cartoon btn-start">
                            <?php echo $module['progress_status'] === 'completed' ? 'Review Again 🔄' : 'Start Learning! 🚀'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
