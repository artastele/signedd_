<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-08
// Part of: SPED LMS — Availability Calendar View

$pageTitle = 'My Availability - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

$monthName = date('F', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
$prevMonth = $currentMonth - 1; $prevYear = $currentYear;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $currentMonth + 1; $nextYear = $currentYear;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>

<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-2"><i class="bi bi-calendar-check"></i> My Availability Calendar</h1>
    <p class="text-muted mb-4">Set your availability and add task notes to dates.</p>

    <!-- Weekly Schedule -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Set Weekly Schedule</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Select the days you are regularly available for IEP meetings.</p>
            <form id="weeklyScheduleForm">
                <div class="row">
                    <?php
                    $daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                    foreach ($daysOfWeek as $i => $day):
                        $checked = isset($recurringAvailability[$i]) && $recurringAvailability[$i];
                    ?>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="days[]" value="<?php echo $i; ?>"
                                   id="day<?php echo $i; ?>"
                                   <?php echo $checked ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="day<?php echo $i; ?>">
                                <?php echo $day; ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bi bi-save"></i> Save Weekly Schedule
                </button>
            </form>
        </div>
    </div>

    <!-- Monthly Calendar -->
    <div class="card">
        <div class="card-header" style="background:#1e4072;color:white;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i><?php echo $monthName . ' ' . $currentYear; ?></h5>
                <div class="d-flex gap-1">
                    <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <a href="?year=<?php echo date('Y'); ?>&month=<?php echo date('m'); ?>" class="btn btn-sm btn-light">Today</a>
                    <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Legend -->
            <div class="p-3 border-bottom d-flex gap-3 flex-wrap small">
                <span><span class="badge" style="background:#1e4072;">■</span> Available</span>
                <span><span class="badge bg-secondary">■</span> Unavailable</span>
                <span><span style="color:#a01422;">●</span> Exception override</span>
                <span><span style="color:#3b6d11;">📅</span> IEP Meeting scheduled</span>
                <span><span style="color:#ffc107;">■</span> Today</span>
            </div>

            <table class="table table-bordered mb-0" style="table-layout:fixed;">
                <thead>
                    <tr>
                        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
                        <th class="text-center py-2" style="background:#f5f5f5;"><?php echo $d; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calendarData as $week): ?>
                    <tr>
                        <?php foreach ($week as $day): ?>
                        <?php if ($day === null): ?>
                            <td style="background:#fafafa;height:90px;"></td>
                        <?php else: ?>
                            <td class="calendar-day p-1"
                                style="height:90px;vertical-align:top;cursor:pointer;position:relative;
                                       background:<?php echo $day['is_available'] ? '#e7f1ff' : '#f8f9fa'; ?>;
                                       border-left:3px solid <?php echo $day['is_available'] ? '#1e4072' : '#dee2e6'; ?>;
                                       <?php echo $day['is_today'] ? 'outline:2px solid #ffc107;' : ''; ?>"
                                onclick="openDayModal('<?php echo $day['date']; ?>', <?php echo $day['is_available'] ? 'true' : 'false'; ?>, '<?php echo htmlspecialchars(addslashes($day['note'] ?? ''), ENT_QUOTES); ?>')">
                                <div class="fw-bold" style="font-size:15px;"><?php echo $day['day']; ?></div>
                                <?php if ($day['is_exception']): ?>
                                    <div style="position:absolute;top:4px;right:4px;color:#a01422;font-size:10px;">●</div>
                                <?php endif; ?>
                                <?php if (!empty($day['iep_meeting'])): ?>
                                    <div class="mt-1 p-1 rounded small" style="background:#e8f5e9;color:#3b6d11;font-size:0.7rem;line-height:1.2;">
                                        <i class="bi bi-calendar-event"></i>
                                        <?php echo htmlspecialchars($day['iep_meeting']); ?>
                                    </div>
                                <?php elseif (!empty($day['note'])): ?>
                                    <div class="mt-1 p-1 rounded small" style="background:#fff3cd;color:#856404;font-size:0.7rem;line-height:1.2;">
                                        <?php echo htmlspecialchars($day['note']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Tip:</strong> Click any date to toggle availability or add a task note.
        IEP meeting dates are automatically marked in green.
    </div>
</div>

<!-- Day Modal -->
<div class="modal fade" id="dayModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:white;">
                <h6 class="modal-title mb-0" id="dayModalTitle">Date</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalDate">
                <input type="hidden" id="modalCurrentAvail">

                <div class="mb-3">
                    <label class="form-label fw-bold small">Availability</label>
                    <div class="d-flex gap-2">
                        <button type="button" id="btnAvail"
                                class="btn btn-sm flex-fill"
                                style="background:#1e4072;color:white;"
                                onclick="setAvail(true)">
                            <i class="bi bi-check-circle"></i> Available
                        </button>
                        <button type="button" id="btnUnavail"
                                class="btn btn-sm flex-fill btn-outline-secondary"
                                onclick="setAvail(false)">
                            <i class="bi bi-x-circle"></i> Unavailable
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Task / Note <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" class="form-control form-control-sm" id="modalNote"
                           placeholder="e.g. Faculty meeting, Holiday...">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background:#a01422;" onclick="saveDay()">
                    <i class="bi bi-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Save weekly schedule
document.getElementById('weeklyScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('<?php echo $basePath; ?>/iep/availability/save-recurring', {
        method: 'POST', body: new FormData(this)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else { alert('Error: ' + d.message); }
    });
});

let pendingAvail = null;

function openDayModal(date, isAvailable, note) {
    pendingAvail = isAvailable;
    document.getElementById('modalDate').value = date;
    document.getElementById('modalCurrentAvail').value = isAvailable ? '1' : '0';
    document.getElementById('modalNote').value = note || '';
    document.getElementById('dayModalTitle').textContent = formatDate(date);
    updateAvailButtons(isAvailable);
    new bootstrap.Modal(document.getElementById('dayModal')).show();
}

function setAvail(val) {
    pendingAvail = val;
    updateAvailButtons(val);
}

function updateAvailButtons(isAvail) {
    const btnA = document.getElementById('btnAvail');
    const btnU = document.getElementById('btnUnavail');
    if (isAvail) {
        btnA.style.background = '#1e4072'; btnA.style.color = 'white'; btnA.className = 'btn btn-sm flex-fill';
        btnU.style.background = ''; btnU.style.color = ''; btnU.className = 'btn btn-sm flex-fill btn-outline-secondary';
    } else {
        btnU.style.background = '#a01422'; btnU.style.color = 'white'; btnU.className = 'btn btn-sm flex-fill';
        btnA.style.background = ''; btnA.style.color = ''; btnA.className = 'btn btn-sm flex-fill btn-outline-secondary';
    }
}

function saveDay() {
    const date = document.getElementById('modalDate').value;
    const note = document.getElementById('modalNote').value;
    const fd = new FormData();
    fd.append('date', date);
    fd.append('is_available', pendingAvail ? '1' : '0');
    fd.append('note', note);

    fetch('<?php echo $basePath; ?>/iep/availability/toggle-exception', {
        method: 'POST', body: fd
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else { alert('Error: ' + d.message); }
    });
}

function formatDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
