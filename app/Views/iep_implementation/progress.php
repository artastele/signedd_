<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Student Progress Tracking

$pageTitle = 'Student Progress - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="bi bi-graph-up text-primary"></i> Student Progress</h1>
            <p class="text-muted mb-0">
                Student: <strong><?php echo htmlspecialchars($iep['student_name']); ?></strong> 
                (LRN: <?php echo htmlspecialchars($iep['lrn']); ?>)
            </p>
        </div>
        <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Materials</h6>
                    <h3 class="mb-0"><?php echo count($materials); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Completed</h6>
                    <h3 class="mb-0 text-success">
                        <?php 
                        $completed = array_filter($progress, fn($p) => $p['status'] === 'completed');
                        echo count($completed);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">In Progress</h6>
                    <h3 class="mb-0 text-warning">
                        <?php 
                        $inProgress = array_filter($progress, fn($p) => $p['status'] === 'in_progress');
                        echo count($inProgress);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Overall Progress</h6>
                    <h3 class="mb-0 text-primary">
                        <?php 
                        $percentage = count($materials) > 0 
                            ? round((count($completed) / count($materials)) * 100) 
                            : 0;
                        echo $percentage;
                        ?>%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Timeline -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Activity Timeline</h5>
        </div>
        <div class="card-body">
            <?php if (empty($progress)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No activity yet.</p>
                </div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($progress as $item): ?>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker">
                                    <?php if ($item['status'] === 'completed'): ?>
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    <?php elseif ($item['status'] === 'in_progress'): ?>
                                        <i class="bi bi-hourglass-split text-warning"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle text-secondary"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content flex-grow-1 ms-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['material_name']); ?></h6>
                                            <p class="text-muted mb-2">
                                                <small>
                                                    <i class="bi bi-tag"></i> <?php echo ucfirst($item['material_type']); ?>
                                                </small>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-<?php 
                                                    echo $item['status'] === 'completed' ? 'success' : 
                                                        ($item['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                                                ?>">
                                                    <?php echo ucwords(str_replace('_', ' ', $item['status'])); ?>
                                                </span>
                                                <?php if ($item['completed_at']): ?>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('M j, Y g:i A', strtotime($item['completed_at'])); ?>
                                                    </small>
                                                <?php elseif ($item['started_at']): ?>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> 
                                                        Started: <?php echo date('M j, Y', strtotime($item['started_at'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($item['time_spent_minutes'] > 0): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i> 
                                                        Time spent: <?php echo $item['time_spent_minutes']; ?> minutes
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($item['stars_earned'] > 0): ?>
                                                <div class="mt-2">
                                                    <small class="text-warning">
                                                        <?php for ($i = 0; $i < $item['stars_earned']; $i++): ?>
                                                            <i class="bi bi-star-fill"></i>
                                                        <?php endfor; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Materials Overview -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Materials Overview</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Material</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Time Spent</th>
                            <th>Stars</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $material): ?>
                            <?php
                            $materialProgress = array_filter($progress, fn($p) => $p['material_id'] == $material['id']);
                            $materialProgress = !empty($materialProgress) ? reset($materialProgress) : null;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($material['material_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($materialProgress): ?>
                                        <span class="badge bg-<?php 
                                            echo $materialProgress['status'] === 'completed' ? 'success' : 
                                                ($materialProgress['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $materialProgress['status'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Started</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $materialProgress && $materialProgress['time_spent_minutes'] > 0 
                                        ? $materialProgress['time_spent_minutes'] . ' min' 
                                        : '-'; 
                                    ?>
                                </td>
                                <td>
                                    <?php if ($materialProgress && $materialProgress['stars_earned'] > 0): ?>
                                        <span class="text-warning">
                                            <?php for ($i = 0; $i < $materialProgress['stars_earned']; $i++): ?>
                                                <i class="bi bi-star-fill"></i>
                                            <?php endfor; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $materialProgress && $materialProgress['completed_at'] 
                                        ? date('M j, Y', strtotime($materialProgress['completed_at'])) 
                                        : '-'; 
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}
.timeline-item {
    position: relative;
}
.timeline-marker {
    font-size: 1.5rem;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
