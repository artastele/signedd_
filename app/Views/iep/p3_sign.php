<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS — IEP Signature Page (Individualized Education Plan)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$p3Data   = $p3Data ?? [];
$iepData  = $p3Data['iep_data'] ?? [];

// Build signature status map: role => row or null
$sigMap = [];
foreach (($signatureStatus ?? []) as $row) {
    $sigMap[$row['signer_role']] = $row;
}

// Required signatories for IEP
$requiredSigners = [
    'sped_teacher' => 'SPED Teacher',
    'parent'       => 'Parent / Guardian',
    'guidance'     => 'Guidance Counselor',
    'principal'    => 'Principal',
];

$currentRole = $_SESSION['role'] ?? '';
$mySignerRole = in_array($currentRole, array_keys($requiredSigners)) ? $currentRole : null;
$alreadySigned = $mySignerRole && !empty($sigMap[$mySignerRole]['signature_data']);
$isComplete = ($p3Data['status'] ?? '') === 'signed_approved';
?>

<div class="main-content">
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0" style="color:#a01422;">
                <i class="bi bi-file-earmark-text"></i> Individualized Education Plan
            </h1>
            <p class="text-muted mt-1">Review and sign the IEP document</p>
        </div>
        <a href="<?php echo $basePath; ?>/iep/documents" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div id="alertContainer"></div>

    <!-- Status + Meta -->
    <div class="card mb-4" style="border-top:3px solid #1e4072;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted d-block">Student</small>
                    <strong><?php echo htmlspecialchars($p3Data['student_name'] ?? 'N/A'); ?></strong>
                    <small class="text-muted d-block">LRN: <?php echo htmlspecialchars($p3Data['lrn'] ?? 'N/A'); ?></small>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Created</small>
                    <strong><?php echo date('M d, Y g:i A', strtotime($p3Data['created_at'] ?? 'now')); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Status</small>
                    <?php
                    $statusColors = ['draft'=>'secondary','pending_signatures'=>'warning','signed_approved'=>'success'];
                    $sc = $statusColors[$p3Data['status'] ?? 'draft'] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $sc; ?> fs-6">
                        <?php echo ucwords(str_replace('_',' ',$p3Data['status'] ?? 'draft')); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- IEP Document Content (read-only) -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>IEP Document Content</h5>
        </div>
        <div class="card-body">
            <?php
            $sections = [
                'Section 1: Student Information' => [
                    'Student Name'   => $iepData['student_name'] ?? null,
                    'LRN'            => $iepData['lrn'] ?? null,
                    'Grade Level'    => $iepData['grade_level'] ?? null,
                    'School Year'    => $iepData['school_year'] ?? null,
                ],
                'Section 2: Present Level of Performance' => [
                    'Academic Performance'    => $iepData['academic_performance'] ?? null,
                    'Behavioral Performance'  => $iepData['behavioral_performance'] ?? null,
                    'Social Performance'      => $iepData['social_performance'] ?? null,
                ],
                'Section 3: Annual Goals & Objectives' => [
                    'Goal 1' => $iepData['goal_1'] ?? null,
                    'Goal 2' => $iepData['goal_2'] ?? null,
                    'Goal 3' => $iepData['goal_3'] ?? null,
                ],
                'Section 4: Special Education Services' => [
                    'Services to be Provided' => $iepData['services_provided'] ?? null,
                    'Frequency & Duration'    => $iepData['frequency_duration'] ?? null,
                ],
                'Section 5: Accommodations & Modifications' => [
                    'Accommodations' => $iepData['accommodations'] ?? null,
                    'Modifications'  => $iepData['modifications'] ?? null,
                ],
            ];
            foreach ($sections as $title => $fields):
            ?>
            <h6 class="fw-bold mb-3 mt-4" style="color:#1e4072;"><?php echo $title; ?></h6>
            <div class="row">
                <?php foreach ($fields as $label => $value): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small"><?php echo $label; ?></label>
                    <div class="p-2 rounded" style="background:#f9f9f9;border:1px solid #e0e0e0;min-height:38px;">
                        <?php echo nl2br(htmlspecialchars($value ?? 'N/A')); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Signature Status Overview -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Signature Status</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($requiredSigners as $role => $label):
                    $signed = !empty($sigMap[$role]['signature_data']);
                    $signedAt = $signed ? date('M d, Y g:i A', strtotime($sigMap[$role]['signed_at'])) : null;
                ?>
                <div class="col-md-3">
                    <div class="p-3 rounded text-center" style="border:2px solid <?php echo $signed ? '#3b6d11' : '#dee2e6'; ?>; background:<?php echo $signed ? '#f0f7eb' : '#f9f9f9'; ?>;">
                        <i class="bi <?php echo $signed ? 'bi-check-circle-fill text-success' : 'bi-clock text-warning'; ?>" style="font-size:1.8rem;"></i>
                        <div class="fw-bold mt-2" style="font-size:0.85rem;"><?php echo $label; ?></div>
                        <div class="small <?php echo $signed ? 'text-success' : 'text-muted'; ?>">
                            <?php echo $signed ? 'Signed ' . $signedAt : 'Pending'; ?>
                        </div>
                        <?php if ($signed && !empty($sigMap[$role]['remarks'])): ?>
                            <div class="small text-muted mt-1 fst-italic">
                                "<?php echo htmlspecialchars($sigMap[$role]['remarks']); ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Signature Pad — only show to eligible roles who haven't signed yet -->
    <?php if ($mySignerRole && !$alreadySigned && !$isComplete): ?>
    <div class="card mb-4" style="border-top:3px solid #a01422;">
        <div class="card-header" style="background:#a01422;color:white;">
            <h5 class="mb-0">
                <i class="bi bi-pen me-2"></i>
                Your Signature — <?php echo $requiredSigners[$mySignerRole]; ?>
            </h5>
        </div>
        <div class="card-body">
            <form id="signForm">
                <input type="hidden" name="p3_id" value="<?php echo htmlspecialchars($p3Data['id'] ?? ''); ?>">
                <input type="hidden" name="signer_role" value="<?php echo htmlspecialchars($mySignerRole); ?>">

                <div class="mb-3">
                    <label class="form-label">Remarks <small class="text-muted">(optional)</small></label>
                    <textarea class="form-control" name="remarks" rows="2"
                              placeholder="Add any remarks or comments..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Draw Your Signature Below</label>
                    <div style="border:2px solid #1e4072;border-radius:6px;background:#fff;position:relative;">
                        <canvas id="signaturePad"
                                style="width:100%;height:200px;display:block;cursor:crosshair;touch-action:none;"></canvas>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">Use mouse or finger to draw your signature</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBtn">
                            <i class="bi bi-eraser"></i> Clear
                        </button>
                    </div>
                    <input type="hidden" name="signature_data" id="signatureData">
                </div>

                <button type="submit" class="btn w-100" style="background:#a01422;color:white;font-size:1rem;">
                    <i class="bi bi-check-circle me-2"></i>Submit My Signature
                </button>
            </form>
        </div>
    </div>
    <?php elseif ($alreadySigned): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>
        You have already signed this IEP document as <strong><?php echo $requiredSigners[$mySignerRole]; ?></strong>.
    </div>
    <?php elseif ($isComplete): ?>
    <div class="alert" style="background:#f0f7eb;border:1px solid #3b6d11;color:#3b6d11;">
        <i class="bi bi-patch-check-fill me-2"></i>
        <strong>IEP document is fully signed and approved.</strong> All required signatures have been collected.
    </div>
    <?php endif; ?>

    <!-- Print -->
    <div class="text-end">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
<?php if ($mySignerRole && !$alreadySigned && !$isComplete): ?>
const canvas = document.getElementById('signaturePad');
const pad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(255,255,255)',
    penColor: '#1e4072'
});

function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const data = pad.toData();
    canvas.width  = canvas.offsetWidth  * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    pad.fromData(data);
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

document.getElementById('clearBtn').addEventListener('click', () => pad.clear());

document.getElementById('signForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (pad.isEmpty()) {
        showAlert('danger', 'Please draw your signature before submitting.');
        return;
    }
    document.getElementById('signatureData').value = pad.toDataURL();

    const formData = new FormData(this);
    fetch('<?php echo $basePath; ?>/iep/p3/sign-submit', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Signature submitted successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Error submitting signature.');
        }
    })
    .catch(() => showAlert('danger', 'Network error. Please try again.'));
});
<?php endif; ?>

function showAlert(type, msg) {
    document.getElementById('alertContainer').innerHTML =
        `<div class="alert alert-${type} alert-dismissible fade show">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>`;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
