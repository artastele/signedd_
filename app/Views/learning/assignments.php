<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Assignments List

$pageTitle = 'My Assignments - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>/css/learner.css">

<body data-logged-in="true" class="learner-page">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <div class="section-header">
        <h1>📝 My Assignments</h1>
        <a href="<?php echo $basePath; ?>/learning/dashboard" class="btn-cartoon btn-primary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No assignments yet. Enjoy your free time!</p>
        </div>
    <?php else: ?>
        <!-- Filter Tabs -->
        <div class="filter-tabs mb-4">
            <button class="filter-tab active" data-filter="all">
                All (<?php echo count($assignments); ?>)
            </button>
            <button class="filter-tab" data-filter="pending">
                Pending
            </button>
            <button class="filter-tab" data-filter="submitted">
                Submitted
            </button>
            <button class="filter-tab" data-filter="graded">
                Graded
            </button>
        </div>

        <div class="row">
            <?php foreach ($assignments as $assignment): ?>
                <?php
                $status = 'pending';
                if ($assignment['submission_id']) {
                    $status = $assignment['grade'] !== null ? 'graded' : 'submitted';
                }
                ?>
                <div class="col-md-4 mb-4 assignment-item" data-status="<?php echo $status; ?>">
                    <div class="assignment-card">
                        <div class="assignment-icon">
                            <?php 
                            $icons = ['✏️', '📄', '📋', '📝', '📑'];
                            echo $icons[array_rand($icons)];
                            ?>
                        </div>
                        <h5 class="assignment-title"><?php echo htmlspecialchars($assignment['material_name']); ?></h5>
                        
                        <!-- Due Date -->
                        <?php if ($assignment['due_date']): ?>
                            <?php
                            $daysLeft = ceil((strtotime($assignment['due_date']) - time()) / 86400);
                            $urgentClass = $daysLeft <= 3 && $daysLeft > 0 ? 'urgent' : '';
                            $overdueClass = $daysLeft < 0 ? 'overdue' : '';
                            ?>
                            <div class="assignment-due <?php echo $urgentClass . ' ' . $overdueClass; ?>">
                                <?php if ($daysLeft < 0): ?>
                                    ⚠️ Overdue by <?php echo abs($daysLeft); ?> day<?php echo abs($daysLeft) != 1 ? 's' : ''; ?>
                                <?php elseif ($daysLeft == 0): ?>
                                    🔥 Due Today!
                                <?php else: ?>
                                    📅 Due in <?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Points -->
                        <?php if ($assignment['points']): ?>
                            <div class="assignment-points">
                                🏆 <?php echo $assignment['points']; ?> points
                            </div>
                        <?php endif; ?>

                        <!-- Status Badge -->
                        <?php if ($assignment['submission_id']): ?>
                            <?php if ($assignment['grade'] !== null): ?>
                                <div class="assignment-badge badge-graded">
                                    ✓ Graded: <?php echo $assignment['grade']; ?>/<?php echo $assignment['points']; ?>
                                </div>
                            <?php else: ?>
                                <div class="assignment-badge badge-submitted">✓ Submitted</div>
                            <?php endif; ?>
                            <a href="<?php echo $basePath; ?>/learning/assignment/<?php echo $assignment['id']; ?>" 
                               class="btn-cartoon btn-view">
                                View Details 👀
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>/learning/assignment/<?php echo $assignment['id']; ?>" 
                               class="btn-cartoon btn-do">
                                Do Assignment! ✍️
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    border-radius: 20px;
    border: 3px solid #333;
    background: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    transform: translateY(-2px);
}

.filter-tab.active {
    background: var(--kid-orange);
    color: #fff;
}

.assignment-points {
    padding: 8px 15px;
    border-radius: 15px;
    background: var(--kid-yellow);
    color: #333;
    font-size: 0.9rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.assignment-due.overdue {
    background: var(--kid-red);
    animation: shake 0.5s infinite;
}

.badge-graded {
    background: var(--kid-blue);
    color: #fff;
}

.btn-cartoon.btn-view {
    background: var(--kid-purple);
    color: #fff;
    width: 100%;
    margin-top: 15px;
}
</style>

<script>
// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        
        // Filter assignments
        document.querySelectorAll('.assignment-item').forEach(item => {
            if (filter === 'all' || item.dataset.status === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
