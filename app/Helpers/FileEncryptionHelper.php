<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 3
// Last modified: 2026-05-04
// Part of: SPED LMS — File Encryption Helper

require_once __DIR__ . '/EncryptionHelper.php';

class FileEncryptionHelper {
    private static $encryptedDir = 'uploads/encrypted/';

    /**
     * Encrypt an uploaded file
     * 
     * @param string $sourcePath Path to original file
     * @param string $originalName Original filename
     * @return array ['success' => bool, 'encrypted_path' => string, 'original_name' => string, 'error' => string]
     */
    public static function encryptFile($sourcePath, $originalName) {
        try {
            // Read file contents
            if (!file_exists($sourcePath)) {
                return ['success' => false, 'error' => 'Source file not found'];
            }

            $fileContents = file_get_contents($sourcePath);
            if ($fileContents === false) {
                return ['success' => false, 'error' => 'Failed to read file'];
            }

            // Encrypt file contents
            $encryptedContents = EncryptionHelper::encrypt($fileContents);

            // Generate unique encrypted filename
            $encryptedFilename = self::generateEncryptedFilename($originalName);
            $encryptedPath = self::$encryptedDir . $encryptedFilename;

            // Ensure encrypted directory exists
            $fullEncryptedDir = __DIR__ . '/../../public/' . self::$encryptedDir;
            if (!is_dir($fullEncryptedDir)) {
                mkdir($fullEncryptedDir, 0755, true);
            }

            // Write encrypted file
            $fullEncryptedPath = __DIR__ . '/../../public/' . $encryptedPath;
            $written = file_put_contents($fullEncryptedPath, $encryptedContents);

            if ($written === false) {
                return ['success' => false, 'error' => 'Failed to write encrypted file'];
            }

            // Delete original file for security
            @unlink($sourcePath);

            return [
                'success' => true,
                'encrypted_path' => $encryptedPath,
                'original_name' => $originalName,
                'error' => null
            ];

        } catch (Exception $e) {
            error_log('File encryption error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Decrypt a file and serve it
     * 
     * @param string $encryptedPath Path to encrypted file (relative to public/)
     * @param string $originalName Original filename for download
     * @param bool $inline Whether to display inline or force download
     */
    public static function serveDecryptedFile($encryptedPath, $originalName, $inline = false) {
        try {
            $fullPath = __DIR__ . '/../../public/' . $encryptedPath;

            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo "File not found";
                return;
            }

            // Read encrypted file
            $encryptedContents = file_get_contents($fullPath);
            if ($encryptedContents === false) {
                http_response_code(500);
                echo "Failed to read file";
                return;
            }

            // Decrypt file contents
            $decryptedContents = EncryptionHelper::decrypt($encryptedContents);

            // Determine MIME type
            $mimeType = self::getMimeType($originalName);

            // Set headers
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . strlen($decryptedContents));
            
            if ($inline && in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
                header('Content-Disposition: inline; filename="' . basename($originalName) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . basename($originalName) . '"');
            }

            // Output decrypted file
            echo $decryptedContents;
            exit;

        } catch (Exception $e) {
            error_log('File decryption error: ' . $e->getMessage());
            http_response_code(500);
            echo "Failed to decrypt file";
            exit;
        }
    }

    /**
     * Get decrypted file contents (for processing, not serving)
     * 
     * @param string $encryptedPath Path to encrypted file
     * @return string|false Decrypted contents or false on error
     */
    public static function getDecryptedContents($encryptedPath) {
        try {
            $fullPath = __DIR__ . '/../../public/' . $encryptedPath;

            if (!file_exists($fullPath)) {
                return false;
            }

            $encryptedContents = file_get_contents($fullPath);
            if ($encryptedContents === false) {
                return false;
            }

            return EncryptionHelper::decrypt($encryptedContents);

        } catch (Exception $e) {
            error_log('File decryption error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrypt file from full path (for testing and FileController)
     * 
     * @param string $fullPath Full path to encrypted file
     * @return string|false Decrypted contents or false on error
     */
    public static function decryptFile($fullPath) {
        try {
            if (!file_exists($fullPath)) {
                error_log('File not found: ' . $fullPath);
                return false;
            }

            $encryptedContents = file_get_contents($fullPath);
            if ($encryptedContents === false) {
                error_log('Failed to read file: ' . $fullPath);
                return false;
            }

            $decrypted = EncryptionHelper::decrypt($encryptedContents);
            
            if ($decrypted === false) {
                error_log('Failed to decrypt file: ' . $fullPath);
                return false;
            }

            return $decrypted;

        } catch (Exception $e) {
            error_log('File decryption error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate encrypted filename
     * 
     * @param string $originalName Original filename
     * @return string Encrypted filename
     */
    private static function generateEncryptedFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $hash = hash('sha256', $originalName . time() . random_bytes(16));
        return $hash . '.enc' . ($extension ? '.' . $extension : '');
    }

    /**
     * Get MIME type from filename
     * 
     * @param string $filename Filename
     * @return string MIME type
     */
    private static function getMimeType($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Check if file is encrypted
     * 
     * @param string $filePath File path
     * @return bool True if file appears to be encrypted
     */
    public static function isEncrypted($filePath) {
        return strpos($filePath, '/encrypted/') !== false || strpos($filePath, '.enc.') !== false;
    }

    /**
     * Migrate existing file to encrypted format
     * 
     * @param string $oldPath Old file path (relative to public/)
     * @param string $originalName Original filename
     * @return array Result of encryption
     */
    public static function migrateFile($oldPath, $originalName) {
        $fullOldPath = __DIR__ . '/../../public/' . $oldPath;
        
        if (!file_exists($fullOldPath)) {
            return ['success' => false, 'error' => 'File not found: ' . $oldPath];
        }

        return self::encryptFile($fullOldPath, $originalName);
    }

    /**
     * Get thumbnail for encrypted image
     * 
     * @param string $encryptedPath Path to encrypted image
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return string|false Base64 encoded thumbnail or false
     */
    public static function getThumbnail($encryptedPath, $maxWidth = 200, $maxHeight = 200) {
        try {
            $decrypted = self::getDecryptedContents($encryptedPath);
            if ($decrypted === false) {
                return false;
            }

            // Create image from string
            $image = @imagecreatefromstring($decrypted);
            if ($image === false) {
                return false;
            }

            // Get original dimensions
            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // Calculate thumbnail dimensions
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $thumbWidth = (int)($origWidth * $ratio);
            $thumbHeight = (int)($origHeight * $ratio);

            // Create thumbnail
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);

            // Output to buffer
            ob_start();
            imagejpeg($thumb, null, 85);
            $thumbData = ob_get_clean();

            // Clean up
            imagedestroy($image);
            imagedestroy($thumb);

            return 'data:image/jpeg;base64,' . base64_encode($thumbData);

        } catch (Exception $e) {
            error_log('Thumbnail generation error: ' . $e->getMessage());
            return false;
        }
    }
}
