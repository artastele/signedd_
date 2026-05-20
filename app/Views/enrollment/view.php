<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — View Enrollment Details (Parent/Teacher)

$pageTitle = 'View Enrollment - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Document type labels
$docLabels = [
    'psa_birth_cert' => 'PSA Birth Certificate',
    'pwd_id' => 'PWD ID',
    'medical_record' => 'Medical Record',
    'beef_form' => 'BEEF Form'
];

// Status colors
$statusColors = [
    'draft' => 'secondary',
    'pending' => 'warning',
    'verified' => 'success',
    'rejected' => 'danger'
];

$isParent = $_SESSION['role'] === 'parent';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-file-text text-primary"></i> Enrollment Details
        </h1>
        <a href="<?php echo $basePath; ?><?php echo $isParent ? '/enrollment/status' : '/enrollment/review'; ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Status Banner -->
    <div class="alert alert-<?php echo $statusColors[$enrollment['status']]; ?> mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-info-circle"></i> 
                    Status: <strong><?php echo strtoupper($enrollment['status']); ?></strong>
                </h5>
                <p class="mb-0">
                    Enrollment ID: #<?php echo $enrollment['id']; ?> | 
                    Submitted: <?php echo date('F j, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
                </p>
            </div>
            <?php if ($isParent && $enrollment['status'] === 'rejected'): ?>
                <a href="<?php echo $basePath; ?>/enrollment/create?type=<?php echo $enrollment['enrollment_type']; ?>&resubmit=<?php echo $enrollment['id']; ?>" 
                   class="btn btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Resubmit
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Student Information Summary -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-primary mb-3">
                        <?php 
                        echo htmlspecialchars($enrollment['first_name'] . ' ' . 
                             ($enrollment['middle_name'] ? $enrollment['middle_name'] . ' ' : '') . 
                             $enrollment['last_name'] . 
                             ($enrollment['extension_name'] ? ' ' . $enrollment['extension_name'] : ''));
                        ?>
                    </h4>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>LRN:</strong> <?php echo htmlspecialchars($enrollment['lrn'] ?? 'Not assigned'); ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Birth Date:</strong> <?php echo date('F j, Y', strtotime($enrollment['birth_date'])); ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Sex:</strong> <?php echo htmlspecialchars($enrollment['sex']); ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Age:</strong> <?php echo htmlspecialchars($enrollment['age'] ?? 'N/A'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="text-primary">Enrollment Details</h6>
                            <p class="mb-1"><strong>Type:</strong> <?php echo ucfirst($enrollment['enrollment_type']); ?></p>
                            <p class="mb-1"><strong>Grade Level:</strong> <?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></p>
                            <p class="mb-0"><strong>School Year:</strong> <?php echo htmlspecialchars($enrollment['school_year']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parent/Guardian Signature -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-pen"></i> Parent/Guardian Digital Signature</h5>
        </div>
        <div class="card-body">
            <?php if ($enrollment['signature_data']): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light text-center">
                            <img src="<?php echo htmlspecialchars($enrollment['signature_data']); ?>" 
                                 alt="Parent Signature" 
                                 class="img-fluid" 
                                 style="max-height: 150px; border: 2px solid #1e4072; background: white; padding: 10px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-check-circle"></i> Digitally Signed</h6>
                            <p class="mb-1"><strong>Signed by:</strong> <?php echo htmlspecialchars($enrollment['parent_name']); ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($enrollment['date_signed'])); ?></p>
                            <p class="mb-0"><strong>IP Address:</strong> <?php echo htmlspecialchars($enrollment['signature_ip'] ?? 'Not recorded'); ?></p>
                        </div>
                        <div class="alert alert-info mb-0">
                            <small><i class="bi bi-shield-check"></i> This digital signature is legally binding and verifies the authenticity of this enrollment submission.</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No signature provided.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Documents with Preview -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-file-earmark-check"></i> Uploaded Documents</h5>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No documents uploaded.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-<?php echo $statusColors[$doc['status']]; ?>">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong><i class="bi bi-file-earmark-text"></i> <?php echo $docLabels[$doc['document_type']]; ?></strong>
                                    <span class="badge bg-<?php echo $statusColors[$doc['status']]; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Document Preview -->
                                <div class="mb-3 text-center">
                                    <?php 
                                    $fileExt = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                    // Use direct file path (no encryption)
                                    $fileUrl = $basePath . '/' . $doc['file_path'];
                                    ?>
                                    
                                    <?php if ($isImage): ?>
                                        <div class="border rounded p-2 bg-light" style="max-height: 250px; overflow: hidden;">
                                            <img src="<?php echo $fileUrl; ?>" 
                                                 alt="<?php echo $docLabels[$doc['document_type']]; ?>" 
                                                 class="img-fluid" 
                                                 style="max-height: 230px; cursor: pointer;"
                                                 onclick="window.open('<?php echo $fileUrl; ?>', '_blank')">
                                        </div>
                                        <small class="text-muted">Click image to view full size</small>
                                    <?php else: ?>
                                        <div class="border rounded p-4 bg-light">
                                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                                            <p class="mb-0 mt-2"><strong>PDF Document</strong></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Document Info -->
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> Uploaded: <?php echo date('M j, Y g:i A', strtotime($doc['uploaded_at'])); ?>
                                    </small>
                                </div>

                                <!-- Review Status -->
                                <?php if ($doc['status'] !== 'pending'): ?>
                                    <div class="alert alert-<?php echo $statusColors[$doc['status']]; ?> mb-3">
                                        <small>
                                            <strong><i class="bi bi-person-check"></i> Reviewed by:</strong> 
                                            <?php echo htmlspecialchars($doc['reviewer_name'] ?? 'Unknown'); ?><br>
                                            <strong><i class="bi bi-calendar-check"></i> Date:</strong> 
                                            <?php echo date('M j, Y g:i A', strtotime($doc['reviewed_at'])); ?>
                                            <?php if ($doc['review_note']): ?>
                                                <br><strong><i class="bi bi-chat-left-text"></i> Note:</strong> 
                                                <?php echo htmlspecialchars($doc['review_note']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-3">
                                        <small><i class="bi bi-clock-history"></i> Awaiting review by SPED Teacher</small>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <a href="<?php echo $fileUrl; ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View Full Document
                                    </a>
                                    <a href="<?php echo $fileUrl; ?>" 
                                       download
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contact Information -->
    <?php if (!$isParent): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-telephone"></i> Contact Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary">Parent/Guardian</h6>
                    <p>
                        <strong>Name:</strong> <?php echo htmlspecialchars($enrollment['parent_name']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($enrollment['parent_email']); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Father</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['father_first_name'] ?? '') . ' ' . ($enrollment['father_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['father_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Mother</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['mother_first_name'] ?? '') . ' ' . ($enrollment['mother_maiden_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['mother_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Address Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Address Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Current Address</h6>
                    <p>
                        <?php echo htmlspecialchars($enrollment['current_house_no'] ?? ''); ?><br>
                        Barangay <?php echo htmlspecialchars($enrollment['current_barangay'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_city'] ?? ''); ?>, 
                        <?php echo htmlspecialchars($enrollment['current_province'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_region'] ?? ''); ?> 
                        <?php echo htmlspecialchars($enrollment['current_zip_code'] ?? ''); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Permanent Address</h6>
                    <?php if ($enrollment['same_as_current_address']): ?>
                        <p class="text-muted"><em>Same as current address</em></p>
                    <?php else: ?>
                        <p>
                            <?php echo htmlspecialchars($enrollment['permanent_house_no'] ?? ''); ?><br>
                            Barangay <?php echo htmlspecialchars($enrollment['permanent_barangay'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($enrollment['permanent_province'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_region'] ?? ''); ?> 
                            <?php echo htmlspecialchars($enrollment['permanent_zip_code'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mb-4">
        <?php if (!$isParent): ?>
            <a href="<?php echo $basePath; ?>/enrollment/review/<?php echo $enrollment['id']; ?>" 
               class="btn btn-primary btn-lg">
                <i class="bi bi-clipboard-check"></i> Review Enrollment
            </a>
        <?php endif; ?>
        
        <a href="<?php echo $basePath; ?><?php echo $isParent ? '/enrollment/status' : '/enrollment/review'; ?>" 
           class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
