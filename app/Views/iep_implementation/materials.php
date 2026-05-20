<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Materials Management

$pageTitle = 'Manage Materials - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="bi bi-folder text-primary"></i> Learning Materials</h1>
            <p class="text-muted mb-0">
                Student: <strong><?php echo htmlspecialchars($iep['student_name']); ?></strong> 
                (LRN: <?php echo htmlspecialchars($iep['lrn']); ?>)
            </p>
        </div>
        <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cloud-upload text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Upload File</h5>
                    <p class="text-muted">Upload PDF, videos, images, or documents</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i> Upload File
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-puzzle text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Create Activity</h5>
                    <p class="text-muted">Build interactive quizzes and activities</p>
                    <a href="<?php echo $basePath; ?>/iep/implementation/create-activity/<?php echo $iep['id']; ?>" 
                       class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Activity
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Materials (<?php echo count($materials); ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($materials)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No materials uploaded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Material Name</th>
                                <th>Type</th>
                                <th>Assignment</th>
                                <th>Due Date</th>
                                <th>Points</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-<?php echo $material['material_type'] === 'activity' ? 'puzzle' : 'file-earmark'; ?>"></i>
                                        <?php echo htmlspecialchars($material['material_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($material['material_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($material['is_assignment']): ?>
                                            <span class="badge bg-warning">Assignment</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Module</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($material['due_date']): ?>
                                            <?php echo date('M j, Y', strtotime($material['due_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $material['points'] ?: '-'; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($material['uploaded_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteMaterial(<?php echo $material['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Upload Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="learner_iep_ids[]" value="<?php echo $iep['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Material Name *</label>
                        <input type="text" name="material_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Material Type *</label>
                        <select name="material_type" class="form-select" required>
                            <option value="module">Module</option>
                            <option value="video">Video</option>
                            <option value="document">Document</option>
                            <option value="worksheet">Worksheet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File *</label>
                        <?php 
                        $fieldName = 'file';
                        $acceptedTypes = '.pdf,.docx,.mp4,.mp3,.jpg,.jpeg,.png';
                        $maxSize = 50;
                        $showCamera = true;
                        include __DIR__ . '/../components/upload-zone.php';
                        ?>
                        <small class="text-muted">Max 50MB. Supported: PDF, DOCX, MP4, MP3, JPG, PNG</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_assignment" class="form-check-input" id="isAssignment">
                            <label class="form-check-label" for="isAssignment">
                                This is an assignment
                            </label>
                        </div>
                    </div>

                    <div id="assignmentFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" name="points" class="form-control" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show/hide assignment fields
document.getElementById('isAssignment').addEventListener('change', function() {
    document.getElementById('assignmentFields').style.display = this.checked ? 'block' : 'none';
});

// Upload form submission
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
    
    try {
        const response = await fetch('<?php echo $basePath; ?>/iep/implementation/upload-file', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-upload"></i> Upload';
        }
    } catch (error) {
        alert('Upload failed: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-upload"></i> Upload';
    }
});

// Delete material
async function deleteMaterial(materialId) {
    if (!confirm('Are you sure you want to delete this material?')) {
        return;
    }
    
    try {
        const response = await fetch('<?php echo $basePath; ?>/iep/implementation/delete-material/' + materialId, {
            method: 'POST'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Delete failed: ' + error.message);
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
