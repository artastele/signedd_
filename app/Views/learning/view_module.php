<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — View Module

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
        <a href="<?php echo $basePath; ?>/learning/modules" class="btn-cartoon btn-back">
            <i class="bi bi-arrow-left"></i> Back to Modules
        </a>
        <div class="timer-display">
            <i class="bi bi-clock"></i>
            <span id="timer">00:00</span>
        </div>
    </div>

    <!-- Module Content Card -->
    <div class="card cartoon-card module-viewer-card">
        <div class="card-body">
            <div class="module-viewer-icon">📖</div>
            <h2 class="module-viewer-title"><?php echo htmlspecialchars($material['material_name']); ?></h2>
            
            <?php if ($material['description']): ?>
                <div class="module-description">
                    <?php echo nl2br(htmlspecialchars($material['description'])); ?>
                </div>
            <?php endif; ?>

            <!-- File Viewer -->
            <?php if ($material['file_path']): ?>
                <div class="file-viewer">
                    <?php
                    $fileExt = strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION));
                    // Use direct file path (no encryption)
                    $fileUrl = $basePath . '/' . $material['file_path'];
                    ?>
                    
                    <?php if (in_array($fileExt, ['pdf'])): ?>
                        <div class="pdf-viewer">
                            <iframe src="<?php echo $fileUrl; ?>" 
                                    width="100%" 
                                    height="600px" 
                                    style="border: none; border-radius: 15px;">
                            </iframe>
                        </div>
                    <?php elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <div class="image-viewer">
                            <img src="<?php echo $fileUrl; ?>" 
                                 alt="<?php echo htmlspecialchars($material['material_name']); ?>"
                                 class="img-fluid"
                                 style="border-radius: 15px; max-width: 100%;">
                        </div>
                    <?php elseif (in_array($fileExt, ['mp4', 'webm', 'ogg'])): ?>
                        <div class="video-viewer">
                            <video controls 
                                   width="100%" 
                                   style="border-radius: 15px;">
                                <source src="<?php echo $fileUrl; ?>" type="video/<?php echo $fileExt; ?>">
                                Your browser does not support video playback.
                            </video>
                        </div>
                    <?php else: ?>
                        <div class="file-download">
                            <div class="download-icon">📥</div>
                            <p>Download this file to view it</p>
                            <a href="<?php echo $fileUrl; ?>" 
                               download 
                               class="btn-cartoon btn-download">
                                Download File 📥
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Progress Info -->
            <div class="progress-info">
                <?php if ($progress['status'] === 'completed'): ?>
                    <div class="completion-badge">
                        <i class="bi bi-check-circle-fill"></i>
                        You completed this module!
                        <?php if ($progress['stars_earned'] > 0): ?>
                            <div class="mt-2">
                                <?php for ($i = 0; $i < $progress['stars_earned']; $i++): ?>
                                    <i class="bi bi-star-fill" style="color: var(--kid-yellow); font-size: 2rem;"></i>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Complete Button -->
            <?php if ($progress['status'] !== 'completed'): ?>
                <button id="completeBtn" class="btn-cartoon btn-complete">
                    Mark as Complete! ✓
                </button>
            <?php else: ?>
                <a href="<?php echo $basePath; ?>/learning/modules" class="btn-cartoon btn-next">
                    Next Module 🚀
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.module-viewer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.btn-back {
    background: var(--kid-blue);
    color: #fff;
}

.timer-display {
    background: #fff;
    border: 3px solid #333;
    border-radius: 20px;
    padding: 10px 20px;
    font-size: 1.2rem;
    font-weight: bold;
    box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
}

.timer-display i {
    color: var(--kid-orange);
    margin-right: 10px;
}

.module-viewer-card {
    max-width: 900px;
    margin: 0 auto;
}

.module-viewer-icon {
    font-size: 5rem;
    text-align: center;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

.module-viewer-title {
    text-align: center;
    color: #333;
    font-weight: bold;
    margin-bottom: 20px;
}

.module-description {
    background: #f9f9f9;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
    border: 3px solid var(--kid-blue);
}

.file-viewer {
    margin: 30px 0;
}

.file-download {
    text-align: center;
    padding: 40px;
    background: #f9f9f9;
    border-radius: 15px;
    border: 3px dashed #ccc;
}

.download-icon {
    font-size: 4rem;
    margin-bottom: 15px;
}

.btn-download {
    background: var(--kid-green);
    color: #fff;
}

.progress-info {
    margin: 30px 0;
}

.completion-badge {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, var(--kid-green) 0%, #4CAF50 100%);
    color: #fff;
    border-radius: 20px;
    font-size: 1.5rem;
    font-weight: bold;
    animation: pulse 1.5s infinite;
}

.completion-badge i {
    font-size: 3rem;
    display: block;
    margin-bottom: 10px;
}

.btn-complete {
    background: var(--kid-green);
    color: #fff;
    width: 100%;
    font-size: 1.3rem;
    padding: 15px;
}

.btn-next {
    background: var(--kid-blue);
    color: #fff;
    width: 100%;
    font-size: 1.3rem;
    padding: 15px;
}
</style>

<script>
// Timer
let seconds = 0;
const timerElement = document.getElementById('timer');

const timerInterval = setInterval(() => {
    seconds++;
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    timerElement.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}, 1000);

// Complete button
const completeBtn = document.getElementById('completeBtn');
if (completeBtn) {
    completeBtn.addEventListener('click', function() {
        if (confirm('Are you sure you finished reading this module?')) {
            // Send completion request
            fetch('<?php echo $basePath; ?>/learning/module/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `material_id=<?php echo $material['id']; ?>&time_spent=${seconds}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success animation
                    alert(`🎉 Great job! You earned ${data.stars_earned} star${data.stars_earned > 1 ? 's' : ''}!`);
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

// Log activity every 30 seconds
setInterval(() => {
    if (seconds > 0 && seconds % 30 === 0) {
        fetch('<?php echo $basePath; ?>/learning/log-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `material_id=<?php echo $material['id']; ?>&time_spent=30`
        });
    }
}, 30000);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    clearInterval(timerInterval);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
