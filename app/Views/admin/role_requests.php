<?php
$pageTitle = 'Role Requests - Admin - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-person-check"></i> Role Verification Requests
    </h1>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No role requests found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Requested Role</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($request['user_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($request['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucwords(str_replace('_', ' ', $request['requested_role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y', strtotime($request['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reviewModal<?php echo $request['id']; ?>">
                                                <i class="bi bi-eye"></i> Review
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal<?php echo $request['id']; ?>">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Review/View Modal -->
                                <div class="modal fade" id="<?php echo $request['status'] === 'pending' ? 'reviewModal' : 'viewModal'; ?><?php echo $request['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <?php echo $request['status'] === 'pending' ? 'Review' : 'View'; ?> Role Request
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6>Applicant Information</h6>
                                                <p>
                                                    <strong>Name:</strong> <?php echo htmlspecialchars($request['user_name']); ?><br>
                                                    <strong>Email:</strong> <?php echo htmlspecialchars($request['user_email']); ?><br>
                                                    <strong>Requested Role:</strong> 
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucwords(str_replace('_', ' ', $request['requested_role'])); ?>
                                                    </span>
                                                </p>

                                                <?php 
                                                $docs = json_decode($request['submitted_docs'], true);
                                                if ($docs && isset($docs['employee_number']) && !empty($docs['employee_number'])): 
                                                ?>
                                                    <p>
                                                        <strong>Employee/DepEd Number:</strong> <?php echo htmlspecialchars($docs['employee_number']); ?>
                                                    </p>
                                                <?php endif; ?>

                                                <h6 class="mt-4">Submitted Documents</h6>
                                                <?php if ($docs && isset($docs['files'])): ?>
                                                    <ul>
                                                        <?php foreach ($docs['files'] as $type => $path): ?>
                                                            <li>
                                                                <strong><?php echo ucwords(str_replace('_', ' ', $type)); ?>:</strong>
                                                                <a href="<?php echo $basePath . '/' . $path; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-download"></i> View/Download
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted">No documents uploaded.</p>
                                                <?php endif; ?>

                                                <?php if ($request['status'] !== 'pending'): ?>
                                                    <h6 class="mt-4">Review Details</h6>
                                                    <p>
                                                        <strong>Status:</strong> 
                                                        <?php if ($request['status'] === 'approved'): ?>
                                                            <span class="badge bg-success">Approved</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Rejected</span>
                                                        <?php endif; ?>
                                                        <br>
                                                        <strong>Reviewed by:</strong> <?php echo htmlspecialchars($request['reviewer_name'] ?? 'N/A'); ?><br>
                                                        <strong>Review Note:</strong> <?php echo htmlspecialchars($request['review_note'] ?? 'None'); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <div class="modal-footer">
                                                    <form method="POST" action="<?php echo $basePath; ?>/admin/role-requests/<?php echo $request['id']; ?>/approve" class="d-inline">
                                                        <div class="mb-2">
                                                            <label class="form-label">Review Note (Optional)</label>
                                                            <textarea name="review_note" class="form-control" rows="2" placeholder="Add a note..."></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle"></i> Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="<?php echo $basePath; ?>/admin/role-requests/<?php echo $request['id']; ?>/reject" class="d-inline">
                                                        <div class="mb-2">
                                                            <label class="form-label">Rejection Reason *</label>
                                                            <textarea name="review_note" class="form-control" rows="2" placeholder="Reason for rejection..." required></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-x-circle"></i> Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
