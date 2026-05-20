<?php
$title = 'Learner Progress Tracker';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid pt-3 px-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1" style="color: #1e4072; font-weight: 700;">
                    <i class="ti ti-bar-chart me-2"></i>Learner Progress Tracker
                </h4>
                <p class="text-muted small mb-0">Monitor the progress of your assigned learners in real-time.</p>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                    <i class="ti ti-refresh me-1"></i>Refresh Data
                </button>
            </div>
        </div>

        <?php if (empty($learners)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="ti ti-users text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No Learners Found</h5>
                    <p class="small text-muted mb-0">You currently have no learners with a signed IEP.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-2">
                <?php foreach ($learners as $learner): ?>
                    <?php 
                        $totalXp = (int)($learner['total_xp'] ?? 0);
                        $totalStars = (int)($learner['total_stars'] ?? 0);
                        $publishedPlans = (int)($learner['published_plans'] ?? 0);
                        $completedActivities = (int)($learner['completed_activities'] ?? 0);
                        $totalActivities = (int)($learner['total_activities'] ?? 0);
                        
                        // Calculate completion percentage
                        $progressPct = 0;
                        if ($totalActivities > 0) {
                            $progressPct = min(100, round(($completedActivities / $totalActivities) * 100));
                        }
                    ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm h-100 learner-progress-card">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-circle me-3" style="background:#1e4072;color:#fff;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:bold;">
                                        <?php echo strtoupper(substr($learner['student_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="color:#1e4072;"><?php echo htmlspecialchars($learner['student_name']); ?></h6>
                                        <div class="small text-muted">LRN: <?php echo htmlspecialchars($learner['lrn']); ?></div>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold text-muted">Overall Completion</span>
                                        <span class="small fw-bold" style="color:#1e4072;"><?php echo $progressPct; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="background:#3b6d11; width: <?php echo $progressPct; ?>%;" aria-valuenow="<?php echo $progressPct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="row g-2 text-center">
                                    <div class="col-6">
                                        <div class="p-2 rounded bg-light border">
                                            <div class="small text-muted mb-1"><i class="ti ti-star text-warning"></i> Stars</div>
                                            <div class="fw-bold fs-5" style="color:#1e4072;"><?php echo $totalStars; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded bg-light border">
                                            <div class="small text-muted mb-1"><i class="ti ti-bolt text-primary"></i> XP</div>
                                            <div class="fw-bold fs-5" style="color:#1e4072;"><?php echo $totalXp; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded bg-light border">
                                            <div class="small text-muted mb-1"><i class="ti ti-book text-success"></i> Activities</div>
                                            <div class="fw-bold fs-5" style="color:#1e4072;"><?php echo $completedActivities; ?> / <?php echo $totalActivities; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded bg-light border">
                                            <div class="small text-muted mb-1"><i class="ti ti-file-text text-info"></i> Lesson Plans</div>
                                            <div class="fw-bold fs-5" style="color:#1e4072;"><?php echo $publishedPlans; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0 pb-3 text-center">
                                <a href="<?php echo $basePath; ?>/iep/implementation/workspace/<?php echo $learner['iep_id']; ?>" class="btn btn-sm w-100" style="background:#1e4072;color:#fff;">
                                    <i class="ti ti-arrow-right me-1"></i>Go to Workspace
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.learner-progress-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.learner-progress-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
