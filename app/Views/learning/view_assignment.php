<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — View Assignment

$pageTitle = htmlspecialchars($material['material_name']) . ' - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>/css/learner.css">

<body data-logged-in="true" class="learner-page">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <!-- Header -->
    <div class="module-viewer-header">
        <a href="<?php echo $basePath; ?>/learning/assignments" class="btn-cartoon btn-back">
            <i class="bi bi-arrow-left"></i> Back to Assignments
        </a>
        <?php if ($material['due_date']): ?>
            <?php
            $daysLeft = ceil((strtotime($material['due_date']) - time()) / 86400);
            ?>
            <div class="due-date-badge <?php echo $daysLeft <= 3 ? 'urgent' : ''; ?>">
                <?php if ($daysLeft < 0): ?>
                    ⚠️ Overdue
                <?php elseif ($daysLeft == 0): ?>
                    🔥 Due Today
                <?php else: ?>
                    📅 <?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?> left
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Assignment Content Card -->
    <div class="card cartoon-card assignment-viewer-card">
        <div class="card-body">
            <div class="assignment-viewer-icon">📝</div>
            <h2 class="assignment-viewer-title"><?php echo htmlspecialchars($material['material_name']); ?></h2>
            
            <!-- Points Badge -->
            <?php if ($material['points']): ?>
                <div class="points-badge">
                    🏆 Worth <?php echo $material['points']; ?> points
                </div>
            <?php endif; ?>

            <!-- Description -->
            <?php if ($material['description']): ?>
                <div class="assignment-description">
                    <h5>📋 Instructions:</h5>
                    <?php echo nl2br(htmlspecialchars($material['description'])); ?>
                </div>
            <?php endif; ?>

            <!-- Attached File -->
            <?php if ($material['file_path']): ?>
                <div class="attached-file">
                    <h5>📎 Attached File:</h5>
                    <a href="<?php echo $basePath; ?>/<?php echo $material['file_path']; ?>" 
                       target="_blank"
                       class="btn-cartoon btn-view-file">
                        View File 👀
                    </a>
                </div>
            <?php endif; ?>

            <!-- Submission Status -->
            <?php if ($submission): ?>
                <div class="submission-status">
                    <div class="status-header">
                        <i class="bi bi-check-circle-fill"></i>
                        <h4>Your Submission</h4>
                    </div>
                    
                    <div class="submission-details">
                        <p><strong>Submitted on:</strong> <?php echo date('F j, Y g:i A', strtotime($submission['submitted_at'])); ?></p>
                        
                        <?php if ($submission['submission_type'] === 'text' || $submission['submission_type'] === 'both'): ?>
                            <div class="text-submission">
                                <strong>Your Answer:</strong>
                                <div class="answer-box">
                                    <?php echo nl2br(htmlspecialchars($submission['text_answer'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($submission['file_path']): ?>
                            <div class="file-submission">
                                <strong>Uploaded File:</strong>
                                <a href="<?php echo $basePath; ?>/<?php echo $submission['file_path']; ?>" 
                                   target="_blank"
                                   class="btn-cartoon btn-view-submission">
                                    View Your File 📄
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($submission['grade'] !== null): ?>
                            <div class="grade-display">
                                <div class="grade-icon">⭐</div>
                                <div class="grade-text">
                                    <h3><?php echo $submission['grade']; ?> / <?php echo $material['points']; ?></h3>
                                    <p>Great work!</p>
                                </div>
                            </div>
                            
                            <?php if ($submission['feedback']): ?>
                                <div class="teacher-feedback">
                                    <strong>Teacher's Feedback:</strong>
                                    <div class="feedback-box">
                                        <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="pending-grade">
                                <i class="bi bi-hourglass-split"></i>
                                Waiting for teacher to grade...
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Submission Form -->
                <div class="submission-form">
                    <h5>✍️ Submit Your Work:</h5>
                    
                    <form id="submissionForm" enctype="multipart/form-data">
                        <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                        
                        <!-- Text Answer -->
                        <div class="form-group mb-3">
                            <label for="textAnswer">Type your answer here:</label>
                            <textarea id="textAnswer" 
                                      name="text_answer" 
                                      class="form-control cartoon-input" 
                                      rows="6"
                                      placeholder="Write your answer here..."></textarea>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="form-group mb-3">
                            <label for="fileUpload">Or upload a file:</label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <input type="file" 
                                       id="fileUpload" 
                                       name="file" 
                                       class="file-input"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="upload-placeholder">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--kid-blue);"></i>
                                    <p>Click or drag file here</p>
                                    <small>PDF, Word, or Image files</small>
                                </div>
                                <div class="file-preview" style="display: none;">
                                    <i class="bi bi-file-earmark"></i>
                                    <span class="file-name"></span>
                                    <button type="button" class="btn-remove-file">✕</button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-cartoon btn-submit">
                            Submit Assignment! 🚀
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.due-date-badge {
    background: var(--kid-blue);
    color: #fff;
    padding: 10px 20px;
    border-radius: 20px;
    border: 3px solid #333;
    font-weight: bold;
    box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
}

.due-date-badge.urgent {
    background: var(--kid-red);
    animation: shake 0.5s infinite;
}

.assignment-viewer-card {
    max-width: 900px;
    margin: 0 auto;
}

.assignment-viewer-icon {
    font-size: 5rem;
    text-align: center;
    margin-bottom: 20px;
}

.assignment-viewer-title {
    text-align: center;
    color: #333;
    font-weight: bold;
    margin-bottom: 20px;
}

.points-badge {
    text-align: center;
    background: var(--kid-yellow);
    color: #333;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 20px;
    display: inline-block;
    width: 100%;
}

.assignment-description {
    background: #f9f9f9;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    border: 3px solid var(--kid-orange);
}

.attached-file {
    margin-bottom: 20px;
}

.btn-view-file {
    background: var(--kid-purple);
    color: #fff;
}

.submission-status {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-radius: 20px;
    padding: 30px;
    border: 3px solid var(--kid-green);
}

.status-header {
    text-align: center;
    margin-bottom: 20px;
}

.status-header i {
    font-size: 3rem;
    color: var(--kid-green);
}

.submission-details {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
}

.answer-box, .feedback-box {
    background: #f9f9f9;
    border-radius: 10px;
    padding: 15px;
    margin-top: 10px;
    border: 2px solid #ddd;
}

.grade-display {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--kid-yellow) 0%, var(--kid-orange) 100%);
    border-radius: 20px;
    padding: 30px;
    margin: 20px 0;
    color: #fff;
}

.grade-icon {
    font-size: 4rem;
    margin-right: 20px;
}

.grade-text h3 {
    font-size: 3rem;
    font-weight: bold;
    margin: 0;
}

.pending-grade {
    text-align: center;
    padding: 20px;
    background: #fff3cd;
    border-radius: 15px;
    font-size: 1.2rem;
    color: #856404;
}

.pending-grade i {
    font-size: 2rem;
    display: block;
    margin-bottom: 10px;
}

.submission-form {
    margin-top: 30px;
}

.cartoon-input {
    border: 3px solid #333;
    border-radius: 15px;
    padding: 15px;
    font-size: 1.1rem;
    font-family: 'Comic Sans MS', cursive;
}

.cartoon-input:focus {
    border-color: var(--kid-blue);
    box-shadow: 0 0 0 0.2rem rgba(77, 150, 255, 0.25);
}

.file-upload-area {
    border: 3px dashed #333;
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.file-upload-area:hover {
    border-color: var(--kid-blue);
    background: #f0f8ff;
}

.file-input {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}

.file-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.file-preview i {
    font-size: 2rem;
    color: var(--kid-green);
}

.btn-remove-file {
    background: var(--kid-red);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-weight: bold;
}

.btn-submit {
    background: var(--kid-green);
    color: #fff;
    width: 100%;
    font-size: 1.3rem;
    padding: 15px;
}

.btn-view-submission {
    background: var(--kid-blue);
    color: #fff;
}
</style>

<script>
// File upload handling
const fileInput = document.getElementById('fileUpload');
const fileUploadArea = document.getElementById('fileUploadArea');
const uploadPlaceholder = fileUploadArea?.querySelector('.upload-placeholder');
const filePreview = fileUploadArea?.querySelector('.file-preview');
const fileNameSpan = filePreview?.querySelector('.file-name');
const removeFileBtn = filePreview?.querySelector('.btn-remove-file');

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileNameSpan.textContent = file.name;
            uploadPlaceholder.style.display = 'none';
            filePreview.style.display = 'flex';
        }
    });
}

if (removeFileBtn) {
    removeFileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        uploadPlaceholder.style.display = 'block';
        filePreview.style.display = 'none';
    });
}

// Form submission
const submissionForm = document.getElementById('submissionForm');
if (submissionForm) {
    submissionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const textAnswer = document.getElementById('textAnswer').value.trim();
        const file = fileInput.files[0];
        
        if (!textAnswer && !file) {
            alert('Please write an answer or upload a file!');
            return;
        }
        
        if (confirm('Are you sure you want to submit this assignment?')) {
            const formData = new FormData(this);
            
            fetch('<?php echo $basePath; ?>/learning/assignment/submit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('🎉 ' + data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
