<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-07
// Part of: SPED LMS — IEP Meeting Scheduler

$pageTitle = 'Schedule IEP Meeting - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-calendar-plus text-primary"></i> 
            Schedule IEP Meeting
        </h1>
        <a href="<?php echo $basePath; ?>/iep/meetings" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Meetings
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Scheduler Form -->
            <div class="card">
                <div class="card-header" style="background-color: #1e4072; color: white;">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event"></i> Meeting Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $basePath; ?>/iep/meetings/create" id="schedulerForm">
                        <!-- Student Selection -->
                        <div class="mb-3">
                            <label for="student_id" class="form-label">
                                <strong>Student</strong> <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="student_id" id="student_id" required>
                                <option value="">-- Select student with finalized assessment --</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['student_name']); ?> 
                                        (LRN: <?php echo htmlspecialchars($student['lrn']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Only students with finalized assessments are shown</div>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-3">
                            <label for="meeting_date" class="form-label">
                                <strong>Meeting Date</strong> <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="meeting_date" id="meeting_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <div class="form-text">
                                <span class="badge bg-success me-2">Suggested dates</span> are when all participants are available
                            </div>
                        </div>

                        <!-- Suggested Dates -->
                        <?php if (!empty($suggestedDates)): ?>
                            <div class="mb-3">
                                <label class="form-label"><strong>Suggested Available Dates:</strong></label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach (array_slice($suggestedDates, 0, 10) as $date): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success suggested-date" 
                                                data-date="<?php echo $date; ?>"
                                                onclick="selectSuggestedDate('<?php echo $date; ?>')">
                                            <?php echo date('M d, Y', strtotime($date)); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($suggestedDates) > 10): ?>
                                    <small class="text-muted">
                                        Showing first 10 of <?php echo count($suggestedDates); ?> available dates
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>No suggested dates available.</strong> 
                                Not all participants have set their availability. You can still schedule manually.
                            </div>
                        <?php endif; ?>

                        <!-- Manual Override -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="manual_override" 
                                       name="manual_override" value="1" onchange="toggleOverrideReason()">
                                <label class="form-check-label" for="manual_override">
                                    <strong>Manual Override</strong> (schedule on a date when not all participants are available)
                                </label>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="override_reason_container">
                            <label for="override_reason" class="form-label">
                                <strong>Reason for Override</strong> <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="override_reason" id="override_reason" 
                                      rows="2" placeholder="Explain why this date is necessary"></textarea>
                        </div>

                        <!-- Time -->
                        <div class="mb-3">
                            <label for="meeting_time" class="form-label">
                                <strong>Meeting Time</strong> <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" name="meeting_time" id="meeting_time" required>
                        </div>

                        <!-- Location (Venue Only - Face-to-Face Meetings) -->
                        <div class="mb-3">
                            <label for="meeting_location" class="form-label">
                                <strong>Meeting Venue</strong> <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="meeting_location" id="meeting_location" 
                                   placeholder="e.g., Conference Room A, Principal's Office" required>
                            <div class="form-text">
                                <i class="bi bi-geo-alt"></i> All IEP meetings are conducted face-to-face
                            </div>
                        </div>

                        <!-- Agenda -->
                        <div class="mb-3">
                            <label for="agenda_notes" class="form-label">
                                <strong>Agenda / Notes</strong>
                            </label>
                            <textarea class="form-control" name="agenda_notes" id="agenda_notes" 
                                      rows="4" placeholder="Meeting agenda and any additional notes"></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo $basePath; ?>/iep/meetings" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calendar-check"></i> Schedule Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Info Card -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Meeting Information</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2"><strong>Participants:</strong></p>
                    <ul class="small mb-3">
                        <li>SPED Teacher (You)</li>
                        <li>Guidance Counselor</li>
                        <li>Principal</li>
                        <li>Parent/Guardian</li>
                    </ul>
                    <p class="small mb-2"><strong>Notifications:</strong></p>
                    <p class="small">All participants will receive email and in-system notifications.</p>
                </div>
            </div>

            <!-- Availability Link -->
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-week" style="font-size: 48px; color: #1e4072;"></i>
                    <h6 class="mt-3">Manage Your Availability</h6>
                    <p class="small text-muted">Set your weekly schedule to help suggest meeting dates</p>
                    <a href="<?php echo $basePath; ?>/iep/availability" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-calendar-check"></i> Set Availability
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Select suggested date
function selectSuggestedDate(date) {
    document.getElementById('meeting_date').value = date;
    
    // Highlight selected button
    document.querySelectorAll('.suggested-date').forEach(btn => {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-success');
    });
    event.target.classList.remove('btn-outline-success');
    event.target.classList.add('btn-success');
}

// Toggle override reason
function toggleOverrideReason() {
    const checkbox = document.getElementById('manual_override');
    const container = document.getElementById('override_reason_container');
    const textarea = document.getElementById('override_reason');
    
    if (checkbox.checked) {
        container.classList.remove('d-none');
        textarea.required = true;
    } else {
        container.classList.add('d-none');
        textarea.required = false;
        textarea.value = '';
    }
}

// Form validation
document.getElementById('schedulerForm').addEventListener('submit', function(e) {
    const meetingLocation = document.getElementById('meeting_location').value.trim();
    
    if (!meetingLocation) {
        e.preventDefault();
        alert('Please provide a meeting venue');
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
