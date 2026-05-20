<?php
$pageTitle = 'Select Role - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

$type = $_GET['type'] ?? null;
$oldRole = $_SESSION['old_role'] ?? '';
$oldEmployeeNumber = $_SESSION['old_employee_number'] ?? '';
unset($_SESSION['old_role'], $_SESSION['old_employee_number']);
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <?php if ($type === 'parent'): ?>
            <i class="bi bi-heart-fill text-primary"></i> Enroll Your Child
        <?php else: ?>
            <i class="bi bi-person-badge text-secondary"></i> Apply as Staff
        <?php endif; ?>
    </h1>

    <?php if (isset($pendingRequest) && $pendingRequest): ?>
        <!-- Pending Request Alert -->
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-clock-history"></i> Application Pending
            </h5>
            <p>
                You have a pending application for <strong><?php echo ucwords(str_replace('_', ' ', $pendingRequest['requested_role'])); ?></strong>.
            </p>
            <p class="mb-0">
                <small>Submitted: <?php echo date('F j, Y g:i A', strtotime($pendingRequest['created_at'])); ?></small>
            </p>
            <hr>
            <p class="mb-0">
                <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </p>
        </div>

    <?php elseif ($type === 'parent'): ?>
        <!-- Parent Role Selection -->
        <div class="card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-heart-fill text-primary" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Welcome, Parent!</h3>
                    <p class="text-muted">You're about to start the enrollment process for your child.</p>
                </div>

                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> What happens next?</h6>
                    <ul class="mb-0">
                        <li>Your role will be set to <strong>Parent</strong></li>
                        <li>You'll get instant access to enrollment features</li>
                        <li>You can submit enrollment documents immediately</li>
                        <li>Track your child's progress and receive IEP notifications</li>
                    </ul>
                </div>

                <form method="POST" action="<?php echo $basePath; ?>/role/select-parent">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Continue as Parent
                        </button>
                        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Staff Role Application -->
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title text-secondary mb-4">
                    <i class="bi bi-briefcase"></i> Staff Role Application
                </h5>

                <!-- Error Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <div class="alert alert-warning mb-4">
                    <h6><i class="bi bi-shield-check"></i> Verification Required</h6>
                    <p class="mb-0">
                        Staff roles require document verification by an administrator. 
                        Please upload the required documents below. You will be notified via email once your application is reviewed.
                    </p>
                </div>

                <form method="POST" action="<?php echo $basePath; ?>/role/submit-staff" enctype="multipart/form-data">
                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label for="requested_role" class="form-label">
                            <i class="bi bi-person-badge"></i> Select Role *
                        </label>
                        <select class="form-select form-select-lg" id="requested_role" name="requested_role" required>
                            <option value="">-- Choose a role --</option>
                            <option value="sped_teacher" <?php echo $oldRole === 'sped_teacher' ? 'selected' : ''; ?>>SPED Teacher</option>
                            <option value="guidance" <?php echo $oldRole === 'guidance' ? 'selected' : ''; ?>>Guidance Counselor</option>
                            <option value="principal" <?php echo $oldRole === 'principal' ? 'selected' : ''; ?>>Principal</option>
                            <option value="master_teacher" <?php echo $oldRole === 'master_teacher' ? 'selected' : ''; ?>>Master Teacher</option>
                        </select>
                    </div>

                    <!-- Employee Number (Optional) -->
                    <div class="mb-4">
                        <label for="employee_number" class="form-label">
                            <i class="bi bi-hash"></i> Employee / DepEd Number (Optional)
                        </label>
                        <input type="text" class="form-control" id="employee_number" name="employee_number" 
                               placeholder="e.g., 123456" value="<?php echo htmlspecialchars($oldEmployeeNumber); ?>">
                        <div class="form-text">If applicable, provide your employee or DepEd identification number.</div>
                    </div>

                    <!-- Government ID Upload -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-card-image"></i> Government-Issued ID *
                        </label>
                        <?php 
                        $fieldName = 'government_id';
                        $acceptedTypes = '.jpg,.jpeg,.png,.pdf';
                        $maxSize = 5;
                        $showCamera = true;
                        include __DIR__ . '/../components/upload-zone.php';
                        ?>
                        <div class="form-text">
                            Upload a clear photo or scan of your government ID (e.g., Driver's License, Passport, National ID).
                            <br><strong>Accepted formats:</strong> JPG, PNG, PDF | <strong>Max size:</strong> 5MB
                        </div>
                    </div>

                    <!-- Proof of Designation Upload -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-file-earmark-text"></i> Proof of Designation *
                        </label>
                        <?php 
                        $fieldName = 'proof_designation';
                        $acceptedTypes = '.jpg,.jpeg,.png,.pdf';
                        $maxSize = 5;
                        $showCamera = true;
                        include __DIR__ . '/../components/upload-zone.php';
                        ?>
                        <div class="form-text">
                            Upload proof of your position (e.g., Appointment Letter, DepEd Order, School ID, Certificate of Employment).
                            <br><strong>Accepted formats:</strong> JPG, PNG, PDF | <strong>Max size:</strong> 5MB
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-secondary btn-lg">
                            <i class="bi bi-send"></i> Submit Application
                        </button>
                        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
