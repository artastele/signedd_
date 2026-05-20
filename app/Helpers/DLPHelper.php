<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 6
// Last modified: 2026-05-04
// Part of: SPED LMS — Data Loss Prevention (DLP) Helper

require_once __DIR__ . '/../../config/db.php';

class DLPHelper {
    private static $settings = null;

    /**
     * Load DLP settings from database
     */
    private static function loadSettings() {
        if (self::$settings === null) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT setting_key, setting_value FROM dlp_settings");
                $results = $stmt->fetchAll();
                
                self::$settings = [];
                foreach ($results as $row) {
                    self::$settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Exception $e) {
                error_log('Failed to load DLP settings: ' . $e->getMessage());
                self::$settings = [];
            }
        }
    }

    /**
     * Get DLP setting value
     * 
     * @param string $key Setting key
     * @param string $default Default value if not found
     * @return string Setting value
     */
    public static function getSetting($key, $default = '') {
        self::loadSettings();
        return self::$settings[$key] ?? $default;
    }

    /**
     * Check if DLP feature is enabled
     * 
     * @param string $feature Feature name (watermark, screenshot_block, copy_block, print_block, export_block)
     * @return bool True if enabled
     */
    public static function isEnabled($feature) {
        $setting = self::getSetting('dlp_enable_' . $feature, 'false');
        return strtolower($setting) === 'true';
    }

    /**
     * Generate watermark text
     * 
     * @param string $userName User name
     * @param string $userEmail User email
     * @return string Watermark text
     */
    public static function generateWatermark($userName = null, $userEmail = null) {
        if ($userName === null) {
            $userName = $_SESSION['user_name'] ?? 'User';
        }
        if ($userEmail === null) {
            $userEmail = $_SESSION['user_email'] ?? '';
        }

        $format = self::getSetting('dlp_watermark_format', '{user} | {timestamp} | {ip}');
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $watermark = str_replace(
            ['{user}', '{email}', '{timestamp}', '{ip}'],
            [$userName, $userEmail, $timestamp, $ip],
            $format
        );

        return $watermark;
    }

    /**
     * Get HTML/CSS for watermark overlay
     * 
     * @param string $text Watermark text
     * @return string HTML for watermark
     */
    public static function getWatermarkHTML($text = null) {
        if (!self::isEnabled('watermark')) {
            return '';
        }

        if ($text === null) {
            $text = self::generateWatermark();
        }

        $html = <<<HTML
<div class="dlp-watermark" style="
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 48px;
    color: rgba(160, 20, 34, 0.1);
    font-weight: bold;
    white-space: nowrap;
    pointer-events: none;
    z-index: 9999;
    font-family: Arial, sans-serif;
    letter-spacing: 2px;
">
    $text
</div>
HTML;

        return $html;
    }

    /**
     * Get JavaScript for DLP protections
     * 
     * @return string JavaScript code
     */
    public static function getProtectionScript() {
        $protections = [];

        if (self::isEnabled('screenshot_block')) {
            $protections[] = self::getScreenshotBlockScript();
        }

        if (self::isEnabled('copy_block')) {
            $protections[] = self::getCopyBlockScript();
        }

        if (self::isEnabled('print_block')) {
            $protections[] = self::getPrintBlockScript();
        }

        if (self::isEnabled('export_block')) {
            $protections[] = self::getExportBlockScript();
        }

        if (empty($protections)) {
            return '';
        }

        return '<script>' . implode("\n", $protections) . '</script>';
    }

    /**
     * Get screenshot blocking script
     */
    private static function getScreenshotBlockScript() {
        return <<<JS
// DLP: Screenshot blocking
(function() {
    // Detect screenshot attempts via keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Print Screen
        if (e.key === 'PrintScreen') {
            e.preventDefault();
            alert('Screenshots are not allowed on this page.');
            return false;
        }
        
        // Ctrl+Print Screen
        if (e.ctrlKey && e.key === 'PrintScreen') {
            e.preventDefault();
            alert('Screenshots are not allowed on this page.');
            return false;
        }
    });

    // Detect screenshot via Shift+Ctrl+S (Chrome)
    document.addEventListener('keydown', function(e) {
        if (e.shiftKey && e.ctrlKey && e.key === 'S') {
            e.preventDefault();
            alert('Screenshots are not allowed on this page.');
            return false;
        }
    });

    // Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        alert('Right-click is disabled on this page.');
        return false;
    });
})();
JS;
    }

    /**
     * Get copy blocking script
     */
    private static function getCopyBlockScript() {
        return <<<JS
// DLP: Copy/Paste blocking
(function() {
    // Disable copy
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        alert('Copying content is not allowed on this page.');
        return false;
    });

    // Disable cut
    document.addEventListener('cut', function(e) {
        e.preventDefault();
        alert('Cutting content is not allowed on this page.');
        return false;
    });

    // Disable paste
    document.addEventListener('paste', function(e) {
        e.preventDefault();
        alert('Pasting content is not allowed on this page.');
        return false;
    });

    // Disable text selection
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });

    // Disable drag
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
})();
JS;
    }

    /**
     * Get print blocking script
     */
    private static function getPrintBlockScript() {
        return <<<JS
// DLP: Print blocking
(function() {
    // Disable Ctrl+P
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            alert('Printing is not allowed on this page.');
            return false;
        }
    });

    // Disable Cmd+P (Mac)
    document.addEventListener('keydown', function(e) {
        if (e.metaKey && e.key === 'p') {
            e.preventDefault();
            alert('Printing is not allowed on this page.');
            return false;
        }
    });

    // Override print styles
    var style = document.createElement('style');
    style.innerHTML = '@media print { body { display: none; } }';
    document.head.appendChild(style);
})();
JS;
    }

    /**
     * Get export blocking script
     */
    private static function getExportBlockScript() {
        return <<<JS
// DLP: Export blocking
(function() {
    // Disable download links
    var links = document.querySelectorAll('a[download]');
    links.forEach(function(link) {
        link.removeAttribute('download');
        link.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Downloading is not allowed on this page.');
            return false;
        });
    });

    // Disable save functionality
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            alert('Saving is not allowed on this page.');
            return false;
        }
    });
})();
JS;
    }

    /**
     * Get CSS for DLP protections
     * 
     * @return string CSS code
     */
    public static function getProtectionCSS() {
        $css = '';

        if (self::isEnabled('copy_block')) {
            $css .= <<<CSS
/* DLP: Prevent text selection */
.dlp-protected {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

/* Prevent drag */
.dlp-protected {
    -webkit-user-drag: none;
}
CSS;
        }

        if (self::isEnabled('print_block')) {
            $css .= <<<CSS
/* DLP: Hide from print */
@media print {
    .dlp-protected {
        display: none !important;
    }
}
CSS;
        }

        return $css;
    }

    /**
     * Check if page is sensitive (requires DLP)
     * 
     * @param string $pageType Page type (iep, assessment, student_records, etc.)
     * @return bool True if page is sensitive
     */
    public static function isSensitivePage($pageType) {
        $sensitivePages = self::getSetting('dlp_sensitive_pages', 'iep,assessment,student_records');
        $pages = array_map('trim', explode(',', $sensitivePages));
        return in_array($pageType, $pages);
    }

    /**
     * Log DLP event (for audit trail)
     * 
     * @param string $event Event type (screenshot_attempt, copy_attempt, print_attempt, etc.)
     * @param string $pageType Page type
     */
    public static function logEvent($event, $pageType = '') {
        try {
            $db = Database::getInstance()->getConnection();
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $stmt = $db->prepare("
                INSERT INTO activity_log (user_id, action_type, details, ip_address)
                VALUES (:user_id, 'dlp_event', :details, :ip_address)
            ");

            $stmt->execute([
                'user_id' => $userId,
                'details' => 'DLP event: ' . $event . ' on page: ' . $pageType,
                'ip_address' => $ipAddress
            ]);
        } catch (Exception $e) {
            error_log('Failed to log DLP event: ' . $e->getMessage());
        }
    }

    /**
     * Update DLP setting
     * 
     * @param string $key Setting key
     * @param string $value Setting value
     */
    public static function updateSetting($key, $value) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO dlp_settings (setting_key, setting_value)
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE setting_value = :value
            ");

            $stmt->execute([
                'key' => $key,
                'value' => $value
            ]);

            // Clear cache
            self::$settings = null;
        } catch (Exception $e) {
            error_log('Failed to update DLP setting: ' . $e->getMessage());
        }
    }
}
