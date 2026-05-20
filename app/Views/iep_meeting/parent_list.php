<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP Meetings List (Parent View)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-calendar-check"></i> IEP Meetings
                </h1>
                <p class="text-muted mt-2">Scheduled IEP meetings for your child</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Upcoming</h6>
                        <h3 style="color: #ffc107;"><?php echo count(array_filter($meetings ?? [], fn($m) => strtotime($m['meeting_date']) > time() && $m['status'] === 'scheduled')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Completed</h6>
                        <h3 style="color: #3b6d11;"><?php echo count(array_filter($meetings ?? [], fn($m) => $m['status'] === 'completed')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total</h6>
                        <h3 style="color: #1e4072;"><?php echo count($meetings ?? []); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meetings Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>Child Name</th>
                            <th>Meeting Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($meetings)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No meetings scheduled yet
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($meetings as $meeting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($meeting['student_name']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($meeting['meeting_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($meeting['meeting_location'] ?? 'TBD'); ?></td>
                                    <td>
                                        <?php 
                                        $statusColor = match($meeting['status']) {
                                            'scheduled' => '#ffc107',
                                            'completed' => '#3b6d11',
                                            'cancelled' => '#dc3545',
                                            default => '#6c757d'
                                        };
                                        ?>
                                        <span class="badge" style="background-color: <?php echo $statusColor; ?>;">
                                            <?php echo ucfirst($meeting['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/iep/meetings/<?php echo $meeting['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card mt-4" style="border-left: 4px solid #a01422;">
            <div class="card-body">
                <h6 style="color: #1e4072; font-weight: bold;">
                    <i class="fas fa-info-circle"></i> About IEP Meetings
                </h6>
                <p class="mb-0 text-muted">
                    IEP (Individualized Education Plan) meetings are scheduled after your child's assessment is approved. 
                    During these meetings, the school team will discuss your child's educational needs and create a personalized 
                    learning plan. You will receive an email invitation with the meeting details.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
