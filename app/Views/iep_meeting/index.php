<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-07
// Part of: SPED LMS — IEP Meeting List

$pageTitle = 'IEP Meetings - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-calendar-event text-primary"></i> 
            IEP Meetings
        </h1>
        <?php if (in_array($_SESSION['role'], ['sped_teacher', 'admin'])): ?>
            <a href="<?php echo $basePath; ?>/iep/meetings/schedule" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Schedule New Meeting
            </a>
        <?php endif; ?>
    </div>

    <!-- Upcoming Meetings -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: #1e4072; color: white;">
            <h5 class="mb-0">
                <i class="bi bi-calendar-check"></i> Upcoming Meetings
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($upcomingMeetings)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No upcoming meetings scheduled.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th>Student</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Scheduled By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingMeetings as $meeting): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($meeting['student_name']); ?></strong><br>
                                        <small class="text-muted">LRN: <?php echo htmlspecialchars($meeting['lrn']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('F d, Y', strtotime($meeting['meeting_date'])); ?><br>
                                        <small><?php echo date('g:i A', strtotime($meeting['meeting_date'])); ?></small>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt"></i> 
                                        <?php echo htmlspecialchars($meeting['meeting_location'] ?? 'TBA'); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'scheduled' => 'primary',
                                            'rescheduled' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$meeting['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $statusColor; ?>">
                                            <?php echo ucfirst($meeting['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($meeting['scheduled_by_name']); ?></td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <?php if (in_array($_SESSION['role'], ['sped_teacher', 'admin'])): ?>
                                            <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>/pdsp" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-clipboard-data"></i> PDSP
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Past Meetings -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Past Meetings
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($pastMeetings)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No past meetings found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th>Student</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastMeetings as $meeting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($meeting['student_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?></td>
                                    <td>
                                        <?php
                                        $statusColor = $statusColors[$meeting['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $statusColor; ?>">
                                            <?php echo ucfirst($meeting['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
