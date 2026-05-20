<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-07
// Part of: SPED LMS — IEP Meeting Details

$pageTitle = 'IEP Meeting Details - SPED LMS';
require __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0" style="color: #a01422;">
                        <i class="bi bi-calendar-event"></i> IEP Meeting Details
                    </h1>
                    <p class="text-muted mt-2">
                        <?php echo $isReadOnly ? 'View meeting information' : 'Manage meeting and PDSP form'; ?>
                    </p>
                </div>
                <a href="<?php echo $basePath; ?>/iep/meetings" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Meetings
                </a>
            </div>
        </div>

        <!-- Meeting Information Card -->
        <div class="card mb-4" style="border-top: 4px solid #1e4072;">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Meeting Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Student Details</h6>
                        <p class="mb-2">
                            <strong>Student Name:</strong> 
                            <?php echo htmlspecialchars($meeting['student_name'] ?? 'N/A'); ?>
                        </p>
                        <p class="mb-2">
                            <strong>LRN:</strong> 
                            <code><?php echo htmlspecialchars($meeting['lrn'] ?? 'N/A'); ?></code>
                        </p>
                        <p class="mb-2">
                            <strong>Date of Birth:</strong> 
                            <?php echo isset($meeting['date_of_birth']) ? date('M d, Y', strtotime($meeting['date_of_birth'])) : 'N/A'; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Meeting Details</h6>
                        <p class="mb-2">
                            <strong>Date:</strong> 
                            <?php echo date('F d, Y', strtotime($meeting['meeting_date'])); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Time:</strong> 
                            <?php echo date('g:i A', strtotime($meeting['meeting_date'])); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Venue:</strong> 
                            <?php echo htmlspecialchars($meeting['meeting_location'] ?? 'N/A'); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Status:</strong> 
                            <span class="badge" style="background-color: <?php echo $meeting['status'] === 'completed' ? '#3b6d11' : '#1e4072'; ?>;">
                                <?php echo ucfirst($meeting['status']); ?>
                            </span>
                        </p>
                        <p class="mb-2">
                            <strong>Scheduled By:</strong> 
                            <?php echo htmlspecialchars($meeting['scheduled_by_name'] ?? 'N/A'); ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($meeting['agenda'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Agenda</h6>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($meeting['agenda'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PDSP Status Card -->
        <div class="card mb-4" style="border-top: 4px solid #a01422;">
            <div class="card-header" style="background-color: #a01422; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-medical"></i> PDSP (Part II) Status
                </h5>
            </div>
            <div class="card-body">
                <?php if ($pdsp): ?>
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">Current Status:</h6>
                            <?php
                            $statusBadge = match($pdsp['status']) {
                                'signed' => '<span class="badge" style="background-color: #3b6d11; font-size: 1.1rem;"><i class="bi bi-check-circle"></i> Signed</span>',
                                'draft' => '<span class="badge" style="background-color: #ffc107; font-size: 1.1rem;"><i class="bi bi-pencil"></i> Draft</span>',
                                default => '<span class="badge" style="background-color: #6c757d; font-size: 1.1rem;"><i class="bi bi-clock"></i> Not Started</span>'
                            };
                            echo $statusBadge;
                            ?>
                            
                            <?php if ($pdsp['status'] === 'signed' && !empty($pdsp['completed_at'])): ?>
                                <p class="text-muted mt-2 mb-0">
                                    <small>Completed on <?php echo date('F d, Y g:i A', strtotime($pdsp['completed_at'])); ?></small>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if (!empty($pdsp['signed_document_path'])): ?>
                                <?php if ($_SESSION['role'] === 'parent'): ?>
                                    <!-- Parent: View only (no download) -->
                                    <button type="button" class="btn btn-outline-success"
                                            onclick="viewDocument('<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>')">
                                        <i class="bi bi-eye"></i> View PDSP Document
                                    </button>
                                <?php elseif ($canAccessPDSP): ?>
                                    <!-- SPED Teacher: Open form + download -->
                                    <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>/pdsp"
                                       class="btn btn-primary me-2"
                                       style="background-color: #a01422; border-color: #a01422;">
                                        <i class="bi bi-file-earmark-text"></i> Open PDSP Form
                                    </a>
                                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>"
                                       target="_blank" class="btn btn-outline-success" download>
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                <?php else: ?>
                                    <!-- Guidance / Principal / Master Teacher: View + Download -->
                                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>"
                                       target="_blank" class="btn btn-success" download>
                                        <i class="bi bi-download"></i> Download PDSP Document
                                    </a>
                                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>"
                                       target="_blank" class="btn btn-outline-secondary ms-1">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                <?php endif; ?>
                            <?php elseif ($canAccessPDSP): ?>
                                <!-- SPED Teacher: No document yet — open form -->
                                <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>/pdsp"
                                   class="btn btn-primary"
                                   style="background-color: #a01422; border-color: #a01422;">
                                    <i class="bi bi-file-earmark-text"></i> Open PDSP Form
                                </a>
                            <?php else: ?>
                                <!-- Others: Document not yet uploaded -->
                                <p class="text-muted mb-0">
                                    <i class="bi bi-info-circle"></i> PDSP document not yet uploaded by SPED Teacher
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($pdsp['status'] === 'signed' && !empty($pdsp['signatories'])): ?>
                        <!-- Show Signatories -->
                        <div class="mt-4">
                            <h6 class="text-muted mb-3">Signatories:</h6>
                            <div class="row">
                                <?php
                                $signatoryRoles = [
                                    'sped_teacher' => 'SPED Teacher',
                                    'gen_ed_teacher' => 'General Ed Teacher',
                                    'school_head' => 'School Head',
                                    'ilrc_supervisor' => 'ILRC Supervisor',
                                    'parent_guardian' => 'Parents/Guardian',
                                    'medical_allied_1' => 'Medical/Allied Health Professional 1',
                                    'medical_allied_2' => 'Medical/Allied Health Professional 2',
                                    'medical_allied_3' => 'Medical/Allied Health Professional 3'
                                ];
                                
                                $signatories = json_decode($pdsp['signatories'], true);
                                foreach ($signatories as $sig):
                                    $roleLabel = $signatoryRoles[$sig['role']] ?? $sig['role'];
                                ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center p-2" style="background-color: #e8f5e9; border-radius: 4px;">
                                        <i class="bi bi-check-circle-fill text-success me-2" style="font-size: 1.2rem;"></i>
                                        <div>
                                            <strong><?php echo htmlspecialchars($roleLabel); ?>:</strong><br>
                                            <small><?php echo htmlspecialchars($sig['name']); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> 
                        <strong>PDSP form not yet created.</strong> 
                        <?php if (!$isReadOnly): ?>
                            Click "Open PDSP Form" to begin.
                        <?php else: ?>
                            The SPED Teacher will create the PDSP form after the meeting.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <a href="<?php echo $basePath; ?>/iep/meetings" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Meetings
            </a>
            <div class="d-flex gap-2">
                <?php if (!$isReadOnly && in_array($meeting['status'], ['scheduled','rescheduled'])): ?>
                    <button type="button" class="btn btn-outline-primary"
                            data-bs-toggle="collapse" data-bs-target="#editMeetingForm">
                        <i class="bi bi-pencil-square"></i> Edit Meeting
                    </button>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['sped_teacher','guidance','principal','admin']) && in_array($meeting['status'], ['scheduled','rescheduled'])): ?>
                    <button type="button" class="btn btn-outline-danger"
                            data-bs-toggle="collapse" data-bs-target="#cancelMeetingForm">
                        <i class="bi bi-x-circle"></i> Cancel Meeting
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cancel Meeting Form -->
        <?php if (in_array($_SESSION['role'], ['sped_teacher','guidance','principal','admin']) && in_array($meeting['status'], ['scheduled','rescheduled'])): ?>
        <div class="collapse mt-3" id="cancelMeetingForm">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-x-circle me-2"></i>Cancel This Meeting</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Note: Parent is obliged to attend. Only staff (SPED Teacher, Guidance, Principal) may cancel due to urgent matters.
                        All participants will be notified.
                    </p>
                    <form method="POST" action="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>/cancel">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Cancellation <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="cancellation_reason" rows="2"
                                      placeholder="e.g. Urgent school matter, Medical emergency..." required></textarea>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-toggle="collapse" data-bs-target="#cancelMeetingForm">
                                Never mind
                            </button>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Confirm Cancellation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Meeting Form (SPED Teacher only, collapsible) -->
        <?php if (!$isReadOnly && in_array($meeting['status'], ['scheduled', 'rescheduled'])): ?>
        <div class="collapse mt-3" id="editMeetingForm">
            <div class="card" style="border-top:3px solid #a01422;">
                <div class="card-header" style="background:#a01422;color:white;">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Meeting Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>/update">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="meeting_date"
                                       value="<?php echo date('Y-m-d', strtotime($meeting['meeting_date'])); ?>"
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="meeting_time"
                                       value="<?php echo date('H:i', strtotime($meeting['meeting_date'])); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Venue <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="meeting_location"
                                       value="<?php echo htmlspecialchars($meeting['meeting_location'] ?? ''); ?>"
                                       placeholder="e.g. Conference Room A" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Agenda / Notes</label>
                                <textarea class="form-control" name="agenda" rows="3"
                                          placeholder="Meeting agenda..."><?php echo htmlspecialchars($meeting['agenda'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-toggle="collapse" data-bs-target="#editMeetingForm">
                                Cancel
                            </button>
                            <button type="submit" class="btn text-white" style="background:#a01422;">
                                <i class="bi bi-save me-1"></i>Save Changes & Notify Participants
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Document Modal (for Parent view-only) -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title"><i class="bi bi-file-earmark-pdf"></i> Signed PDSP Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe id="documentFrame" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewDocument(url) {
    const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
    document.getElementById('documentFrame').src = url;
    modal.show();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
