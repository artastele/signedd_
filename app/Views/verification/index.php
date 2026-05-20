<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-05-04
// Part of: SPED LMS — Verification Dashboard

$basePath = isset($basePath) ? $basePath : '/';
$pageTitle = 'Enrollment Verification - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4" style="color: #1e4072;">
        <i class="bi bi-clipboard-check"></i> Enrollment Verification
    </h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <span class="badge bg-info"><?php echo count($enrollments); ?> Pending</span>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by student name or parent email...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="pending">Pending Documents</option>
                        <option value="partial">Partially Approved</option>
                        <option value="ready">Ready to Verify</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">Reset</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollments List -->
    <?php if (empty($enrollments)): ?>
        <div class="alert alert-info" role="alert">
            <strong>No pending enrollments</strong> — All enrollments have been verified or are in progress.
        </div>
    <?php else: ?>
        <div class="row" id="enrollmentsList">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <div class="col-md-6 mb-4 enrollment-item" data-student="<?php echo strtolower($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>" data-email="<?php echo strtolower($enrollment['parent_email']); ?>">
                                <div class="card enrollment-card h-100">
                                    <div class="card-body">
                                        <!-- Header -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    Grade: <?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?>
                                                </p>
                                            </div>
                                            <span class="badge pending-badge">Pending</span>
                                        </div>
                                        
                                        <!-- Parent Info -->
                                        <p class="small text-muted mb-2">
                                            <strong>Parent:</strong> <?php echo htmlspecialchars($enrollment['parent_name']); ?><br>
                                            <strong>Email:</strong> <?php echo htmlspecialchars($enrollment['parent_email']); ?>
                                        </p>
                                        
                                        <!-- Document Progress -->
                                        <div class="doc-progress">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="small"><strong>Documents</strong></span>
                                                <span class="small">
                                                    <span class="text-success"><?php echo $enrollment['approved_docs']; ?></span>/<?php echo $enrollment['total_docs']; ?>
                                                </span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" style="width: <?php echo ($enrollment['total_docs'] > 0) ? ($enrollment['approved_docs'] / $enrollment['total_docs'] * 100) : 0; ?>%; background-color: #3b6d11;"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Document Status -->
                                        <div class="mt-3 small">
                                            <?php if ($enrollment['pending_docs'] > 0): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $enrollment['pending_docs']; ?> Pending</span>
                                            <?php endif; ?>
                                            <?php if ($enrollment['rejected_docs'] > 0): ?>
                                                <span class="badge bg-danger"><?php echo $enrollment['rejected_docs']; ?> Rejected</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Submission Date -->
                                        <p class="small text-muted mt-3 mb-2">
                                            Submitted: <?php echo date('M d, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
                                        </p>
                                        
                                        <!-- Action Button -->
                                        <a href="<?php echo $basePath; ?>verification/<?php echo $enrollment['id']; ?>" class="btn btn-sm btn-primary w-100" style="background-color: #a01422; border-color: #a01422;">
                                            Review Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const items = document.querySelectorAll('.enrollment-item');
        
        items.forEach(item => {
            const student = item.dataset.student;
            const email = item.dataset.email;
            
            if (student.includes(searchTerm) || email.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Filter functionality
    document.getElementById('filterStatus').addEventListener('change', function() {
        const filterValue = this.value;
        const items = document.querySelectorAll('.enrollment-item');
        
        items.forEach(item => {
            const card = item.querySelector('.card');
            const progressBar = card.querySelector('.progress-bar');
            const width = parseFloat(progressBar.style.width);
            
            let show = true;
            if (filterValue === 'pending' && width === 100) show = false;
            if (filterValue === 'partial' && (width === 0 || width === 100)) show = false;
            if (filterValue === 'ready' && width !== 100) show = false;
            
            item.style.display = show ? '' : 'none';
        });
    });
    
    // Reset filters
    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterStatus').value = '';
        document.querySelectorAll('.enrollment-item').forEach(item => {
            item.style.display = '';
        });
    }
</script>
