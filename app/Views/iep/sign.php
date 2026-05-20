<?php
// DO NOT ALTER WITHOUT APPROVAL -- Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS -- IEP Digital Signature Page

$pageTitle = 'Sign IEP - SPED LMS';
$basePath  = BASE_PATH;
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="mb-4">
        <h1 style="color:#1e4072;"><i class="bi bi-pen me-2"></i>Sign IEP</h1>
        <p class="text-muted">
            <?php echo htmlspecialchars($iep['student_name']); ?> &bull;
            LRN: <?php echo htmlspecialchars($iep['lrn']); ?> &bull;
            <?php echo htmlspecialchars($iep['school_year']); ?>
        </p>
    </div>

    <!-- IEP Summary (read-only) -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0">IEP Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3"><strong>Student:</strong> <?php echo htmlspecialchars($iep['student_name']); ?></div>
                <div class="col-md-3"><strong>LRN:</strong> <?php echo htmlspecialchars($iep['lrn']); ?></div>
                <div class="col-md-3"><strong>School Year:</strong> <?php echo htmlspecialchars($iep['school_year']); ?></div>
                <div class="col-md-3"><strong>Grade Level:</strong> <?php echo htmlspecialchars($studentData['grade_level'] ?? ''); ?></div>
            </div>
            <?php if (!empty($domains)): ?>
            <div class="mt-3">
                <strong>Domains:</strong>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    <?php foreach ($domains as $d): ?>
                    <span class="badge px-3 py-2" style="background:#1e4072;"><?php echo htmlspecialchars($d['domain_name']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Signature Status Overview -->
    <div class="card mb-4">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Signatory Status</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
            <?php foreach ($signatories as $s): ?>
            <div class="col-md-4">
                <div class="p-3 rounded" style="border:1px solid #dee2e6;">
                    <div class="fw-semibold" style="color:#1e4072;"><?php echo htmlspecialchars($s['signatory_name']); ?></div>
                    <small class="text-muted"><?php echo ucwords(str_replace('_',' ',$s['signatory_role'])); ?></small>
                    <div class="mt-1">
                        <?php if (!empty($s['signature_image_path'])): ?>
                        <span class="badge" style="background:#3b6d11;"><i class="bi bi-check-circle-fill me-1"></i>Signed</span>
                        <?php else: ?>
                        <span class="badge" style="background:#ffc107;color:#000;"><i class="bi bi-clock me-1"></i>Pending</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Signature Pad for this signatory -->
    <?php if (empty($sig['signature_image_path'])): ?>
    <div class="card mb-4">
        <div class="card-header" style="background:#a01422;color:white;">
            <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Your Signature — <?php echo htmlspecialchars($sig['signatory_name']); ?></h5>
        </div>
        <div class="card-body text-center">
            <p class="text-muted mb-3">Draw your signature below using your mouse or finger.</p>
            <canvas id="sigCanvas" width="600" height="200"
                    style="border:2px solid #1e4072;border-radius:6px;max-width:100%;touch-action:none;"></canvas>
            <div class="mt-3 d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-outline-secondary" onclick="clearSig()">
                    <i class="bi bi-eraser me-1"></i>Clear
                </button>
                <button type="button" class="btn" style="background:#a01422;color:white;" onclick="submitSig()">
                    <i class="bi bi-check-circle me-1"></i>Submit Signature
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>
        You have already signed this IEP.
        <img src="<?php echo $basePath; ?>/<?php echo htmlspecialchars($sig['signature_image_path']); ?>"
             alt="Your signature" style="max-height:60px;display:block;margin-top:8px;border:1px solid #dee2e6;border-radius:4px;">
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const BASE = '<?php echo $basePath; ?>';
const canvas = document.getElementById('sigCanvas');
let sigPad;
if (canvas) {
    sigPad = new SignaturePad(canvas, { penColor: '#1e4072' });
    // Resize canvas properly
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPad.clear();
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
}
function clearSig() { sigPad?.clear(); }
function submitSig() {
    if (!sigPad || sigPad.isEmpty()) {
        alert('Please draw your signature first.'); return;
    }
    const fd = new FormData();
    fd.append('iep_id',        <?php echo $iep['id']; ?>);
    fd.append('signatory_id',  <?php echo $sig['id']; ?>);
    fd.append('signature_data', sigPad.toDataURL('image/png'));
    fetch(BASE + '/iep/save-signature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Signature saved successfully!');
                location.href = BASE + '/iep/form/' + <?php echo (int) $iep['id']; ?>;
            } else {
                alert(data.message || 'Failed to save signature');
            }
        });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
