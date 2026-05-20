<?php
// Simple file viewer for decrypted test files
$dir = __DIR__;
$files = glob($dir . '/*');

// Remove index.php from list
$files = array_filter($files, function($file) {
    return basename($file) !== 'index.php';
});

?>
<!DOCTYPE html>
<html>
<head>
    <title>Decrypted Files Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #1e4072;
        }
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .file-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
        }
        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .file-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .file-name {
            font-size: 12px;
            color: #666;
            word-break: break-all;
            margin-bottom: 10px;
        }
        .file-size {
            font-size: 11px;
            color: #999;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #1e4072;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            margin: 2px;
        }
        .btn:hover {
            background: #a01422;
        }
        .btn-download {
            background: #3b6d11;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>🔓 Decrypted Files Viewer</h1>
    
    <?php if (empty($files)): ?>
        <div class="alert">
            <strong>⚠️ No files found</strong><br>
            Run <code>test-decrypt-files.php</code> first to decrypt files from the database.
        </div>
    <?php else: ?>
        <p>Found <strong><?php echo count($files); ?></strong> decrypted file(s)</p>
        
        <div class="file-grid">
            <?php foreach ($files as $file): ?>
                <?php
                $filename = basename($file);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $fileSize = filesize($file);
                $fileSizeKB = round($fileSize / 1024, 2);
                
                // Determine icon
                $icon = '📄';
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $icon = '🖼️';
                } elseif ($ext === 'pdf') {
                    $icon = '📕';
                } elseif (in_array($ext, ['mp4', 'webm'])) {
                    $icon = '🎬';
                } elseif (in_array($ext, ['mp3', 'wav'])) {
                    $icon = '🎵';
                }
                ?>
                
                <div class="file-card">
                    <div class="file-icon"><?php echo $icon; ?></div>
                    <div class="file-name"><?php echo htmlspecialchars($filename); ?></div>
                    <div class="file-size"><?php echo $fileSizeKB; ?> KB</div>
                    <a href="<?php echo $filename; ?>" target="_blank" class="btn">👁️ View</a>
                    <a href="<?php echo $filename; ?>" download class="btn btn-download">⬇️ Download</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <hr style="margin-top: 40px;">
    <p style="color: #666; font-size: 12px;">
        <strong>Note:</strong> These are decrypted test files. In production, files are served through FileController with permission checks.
    </p>
</body>
</html>
