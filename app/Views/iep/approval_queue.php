<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP Approval Queue (Principal)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$documents = $documents ?? [];
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-check-circle"></i> IEP Approval Queue
                </h1>
                <p class="text-muted mt-2">Final approval of IEP P3 documents</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending Approval</h6>
                        <h3 style="color: #ffc107;"><?php echo count($documents); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Approved</h6>
                        <h3 style="color: #3b6d11;">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total</h6>
                        <h3 style="color: #1e4072;"><?php echo count($documents); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Queue Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>Created By</th>
                            <th>Created Date</th>
                            <th>Signatures</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No documents pending approval
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($doc['student_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['lrn']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['created_by_name']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($doc['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo count($doc['signatures']); ?>/5 Signed
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button type="button" class="btn btn-sm" 
                                                style="background-color: #a01422; color: white;"
                                                onclick="approveDocument(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card mt-4" style="border-left: 4px solid #a01422;">
            <div class="card-body">
                <h6 style="color: #1e4072; font-weight: bold;">
                    <i class="fas fa-info-circle"></i> About IEP Approval
                </h6>
                <p class="mb-0 text-muted">
                    As Principal, you are responsible for the final approval of IEP P3 documents. 
                    Review all signatures and ensure all required signers have signed before approving. 
                    Once approved, the IEP document is locked and cannot be modified.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- View Document Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title">IEP P3 Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="documentContent">
                <!-- Document content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn" style="background-color: #a01422; color: white;" 
                        onclick="approveFromModal()">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentDocumentId = null;

function viewDocument(docId) {
    currentDocumentId = docId;
    
    // Load document content via AJAX
    fetch('<?php echo $basePath; ?>/iep/p3/' + docId + '/sign')
        .then(response => response.text())
        .then(html => {
            document.getElementById('documentContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error loading document');
        });
}

function approveDocument(docId) {
    if (!confirm('Are you sure you want to approve this IEP document? This action cannot be undone.')) {
        return;
    }
    
    fetch('<?php echo $basePath; ?>/iep/documents/' + docId + '/approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Document approved successfully!');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error approving document');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error approving document');
    });
}

function approveFromModal() {
    if (currentDocumentId) {
        approveDocument(currentDocumentId);
    }
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
