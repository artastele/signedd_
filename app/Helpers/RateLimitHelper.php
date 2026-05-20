<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 5
// Last modified: 2026-05-04
// Part of: SPED LMS — Login Rate Limiting Helper

require_once __DIR__ . '/../../config/db.php';

class RateLimitHelper {
    // Rate limiting time windows (in seconds)
    const ATTEMPT_WINDOW = 900; // 15 minutes
    
    /**
     * Get system setting value
     */
    private static function getSetting($key, $default) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1");
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetch();
            return $result ? (int)$result['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * Get max login attempts from settings
     */
    private static function getMaxAttempts() {
        return self::getSetting('max_login_attempts', 5);
    }

    /**
     * Get lockout duration in seconds from settings
     */
    private static function getLockoutDuration() {
        return self::getSetting('lockout_duration', 15) * 60; // Convert minutes to seconds
    }

    /**
     * Check if login attempt is rate limited
     * 
     * @param string $email Email address attempting login
     * @param string $ipAddress IP address of attempt
     * @return array ['allowed' => bool, 'message' => string, 'remaining' => int]
     */
    public static function checkLoginAttempts($email, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        try {
            $db = Database::getInstance()->getConnection();
            $maxAttempts = self::getMaxAttempts();
            $lockoutDuration = self::getLockoutDuration();
            $windowStart = date('Y-m-d H:i:s', time() - $lockoutDuration);

            // Check attempts by email
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM rate_limit_log
                WHERE email = :email
                AND attempt_type = 'login'
                AND success = FALSE
                AND attempted_at > :window_start
            ");
            
            $stmt->execute([
                'email' => $email,
                'window_start' => $windowStart
            ]);
            
            $emailAttempts = $stmt->fetch()['count'];

            // Check if rate limited by email
            if ($emailAttempts >= $maxAttempts) {
                $lockoutMinutes = (int)($lockoutDuration / 60);
                return [
                    'allowed' => false,
                    'message' => "Too many login attempts. Please try again in {$lockoutMinutes} minutes.",
                    'remaining' => 0,
                    'type' => 'email'
                ];
            }

            // Check attempts by IP (10x the email limit)
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM rate_limit_log
                WHERE ip_address = :ip_address
                AND attempt_type = 'login'
                AND success = FALSE
                AND attempted_at > :window_start
            ");
            
            $stmt->execute([
                'ip_address' => $ipAddress,
                'window_start' => $windowStart
            ]);
            
            $ipAttempts = $stmt->fetch()['count'];

            if ($ipAttempts >= ($maxAttempts * 10)) {
                $lockoutMinutes = (int)($lockoutDuration / 60);
                return [
                    'allowed' => false,
                    'message' => "Too many login attempts from this IP. Please try again in {$lockoutMinutes} minutes.",
                    'remaining' => 0,
                    'type' => 'ip'
                ];
            }

            return [
                'allowed' => true,
                'message' => 'OK',
                'remaining' => $maxAttempts - $emailAttempts,
                'type' => 'none'
            ];
        } catch (Exception $e) {
            error_log('Rate limit check failed: ' . $e->getMessage());
            // Allow attempt if check fails (fail open)
            return [
                'allowed' => true,
                'message' => 'OK',
                'remaining' => 5,
                'type' => 'none'
            ];
        }
    }

    /**
     * Record a login attempt
     * 
     * @param string $email Email address
     * @param bool $success Whether attempt was successful
     * @param string $ipAddress IP address
     */
    public static function recordLoginAttempt($email, $success = false, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO rate_limit_log (email, ip_address, attempt_type, success)
                VALUES (:email, :ip_address, 'login', :success)
            ");
            
            $stmt->execute([
                'email' => $email,
                'ip_address' => $ipAddress,
                'success' => $success ? 1 : 0
            ]);
        } catch (Exception $e) {
            error_log('Failed to record login attempt: ' . $e->getMessage());
        }
    }

    /**
     * Clear rate limit for email (on successful login)
     * 
     * @param string $email Email address
     */
    public static function clearLoginAttempts($email) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                DELETE FROM rate_limit_log
                WHERE email = :email
                AND attempt_type = 'login'
                AND success = FALSE
            ");
            
            $stmt->execute(['email' => $email]);
        } catch (Exception $e) {
            error_log('Failed to clear login attempts: ' . $e->getMessage());
        }
    }

    /**
     * Check registration rate limit
     * 
     * @param string $email Email address
     * @param string $ipAddress IP address
     * @return array ['allowed' => bool, 'message' => string]
     */
    public static function checkRegistrationAttempts($email, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        try {
            $db = Database::getInstance()->getConnection();
            $windowStart = date('Y-m-d H:i:s', time() - self::ATTEMPT_WINDOW);

            // Check registrations by email (max 3 per 15 min)
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM rate_limit_log
                WHERE email = :email
                AND attempt_type = 'registration'
                AND attempted_at > :window_start
            ");
            
            $stmt->execute([
                'email' => $email,
                'window_start' => $windowStart
            ]);
            
            $emailAttempts = $stmt->fetch()['count'];

            if ($emailAttempts >= 3) {
                return [
                    'allowed' => false,
                    'message' => 'Too many registration attempts. Please try again in 15 minutes.'
                ];
            }

            // Check registrations by IP (max 10 per 15 min)
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM rate_limit_log
                WHERE ip_address = :ip_address
                AND attempt_type = 'registration'
                AND attempted_at > :window_start
            ");
            
            $stmt->execute([
                'ip_address' => $ipAddress,
                'window_start' => $windowStart
            ]);
            
            $ipAttempts = $stmt->fetch()['count'];

            if ($ipAttempts >= 10) {
                return [
                    'allowed' => false,
                    'message' => 'Too many registration attempts from this IP. Please try again in 15 minutes.'
                ];
            }

            return ['allowed' => true, 'message' => 'OK'];
        } catch (Exception $e) {
            error_log('Registration rate limit check failed: ' . $e->getMessage());
            return ['allowed' => true, 'message' => 'OK'];
        }
    }

    /**
     * Record a registration attempt
     * 
     * @param string $email Email address
     * @param string $ipAddress IP address
     */
    public static function recordRegistrationAttempt($email, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO rate_limit_log (email, ip_address, attempt_type, success)
                VALUES (:email, :ip_address, 'registration', 1)
            ");
            
            $stmt->execute([
                'email' => $email,
                'ip_address' => $ipAddress
            ]);
        } catch (Exception $e) {
            error_log('Failed to record registration attempt: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old rate limit records
     */
    public static function cleanupOldRecords() {
        try {
            $db = Database::getInstance()->getConnection();
            $cutoffTime = date('Y-m-d H:i:s', time() - (24 * 3600)); // 24 hours
            
            $stmt = $db->prepare("
                DELETE FROM rate_limit_log
                WHERE attempted_at < :cutoff_time
            ");
            
            $stmt->execute(['cutoff_time' => $cutoffTime]);
        } catch (Exception $e) {
            error_log('Failed to cleanup rate limit records: ' . $e->getMessage());
        }
    }

    /**
     * Get remaining attempts for email
     * 
     * @param string $email Email address
     * @return int Number of remaining attempts
     */
    public static function getRemainingAttempts($email) {
        try {
            $db = Database::getInstance()->getConnection();
            $maxAttempts = self::getMaxAttempts();
            $lockoutDuration = self::getLockoutDuration();
            $windowStart = date('Y-m-d H:i:s', time() - $lockoutDuration);

            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM rate_limit_log
                WHERE email = :email
                AND attempt_type = 'login'
                AND success = FALSE
                AND attempted_at > :window_start
            ");
            
            $stmt->execute([
                'email' => $email,
                'window_start' => $windowStart
            ]);
            
            $attempts = $stmt->fetch()['count'];
            return max(0, $maxAttempts - $attempts);
        } catch (Exception $e) {
            error_log('Failed to get remaining attempts: ' . $e->getMessage());
            return 5;
        }
    }
}
