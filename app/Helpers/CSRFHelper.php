<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 4
// Last modified: 2026-05-04
// Part of: SPED LMS — CSRF Protection Helper

require_once __DIR__ . '/EncryptionHelper.php';
require_once __DIR__ . '/../../config/db.php';

class CSRFHelper {
    private const TOKEN_LENGTH = 32;
    private const TOKEN_EXPIRY = 3600; // 1 hour

    /**
     * Generate a new CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateToken() {
        // Generate random token
        $token = EncryptionHelper::generateToken(self::TOKEN_LENGTH);
        
        // Store in database
        $sessionId = session_id();
        $userId = $_SESSION['user_id'] ?? null;
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO csrf_tokens (session_id, token, user_id, expires_at)
                VALUES (:session_id, :token, :user_id, :expires_at)
            ");
            
            $stmt->execute([
                'session_id' => $sessionId,
                'token' => $token,
                'user_id' => $userId,
                'expires_at' => $expiresAt
            ]);
        } catch (Exception $e) {
            error_log('CSRF token generation failed: ' . $e->getMessage());
            // Still return the token even if DB insert fails
            // This allows the form to load and be submitted
        }

        return $token;
    }

    /**
     * Get or create CSRF token for current session
     * 
     * @return string CSRF token
     */
    public static function getToken() {
        $sessionId = session_id();
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check for existing valid token
            $stmt = $db->prepare("
                SELECT token FROM csrf_tokens
                WHERE session_id = :session_id
                AND expires_at > NOW()
                AND used = FALSE
                ORDER BY created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute(['session_id' => $sessionId]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $result['token'];
            }
        } catch (Exception $e) {
            error_log('CSRF token retrieval failed: ' . $e->getMessage());
            // If database fails, generate a temporary token
            // This allows the form to load even if DB is temporarily unavailable
            return self::generateToken();
        }

        // Generate new token if none exists
        return self::generateToken();
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validateToken($token) {
        if (empty($token)) {
            return false;
        }

        $sessionId = session_id();
        $userId = $_SESSION['user_id'] ?? null;

        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if token exists and is valid
            $stmt = $db->prepare("
                SELECT id, used FROM csrf_tokens
                WHERE token = :token
                AND session_id = :session_id
                AND expires_at > NOW()
                LIMIT 1
            ");
            
            $stmt->execute([
                'token' => $token,
                'session_id' => $sessionId
            ]);
            
            $result = $stmt->fetch();
            
            if (!$result) {
                self::logCSRFFailure('Token not found or expired', $token);
                return false;
            }

            if ($result['used']) {
                self::logCSRFFailure('Token already used', $token);
                return false;
            }

            // Mark token as used
            $stmt = $db->prepare("
                UPDATE csrf_tokens
                SET used = TRUE, used_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $result['id']]);

            return true;
        } catch (Exception $e) {
            error_log('CSRF token validation failed: ' . $e->getMessage());
            // In development, allow if DB is down
            if (getenv('APP_ENV') === 'development') {
                error_log('CSRF validation skipped in development mode');
                return true;
            }
            return false;
        }
    }

    /**
     * Log CSRF validation failure
     * 
     * @param string $reason Reason for failure
     * @param string $token Token that failed
     */
    private static function logCSRFFailure($reason, $token) {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Only log if user is logged in (activity_log requires user_id)
        if ($userId === null) {
            error_log("CSRF validation failed for anonymous user: $reason (IP: $ipAddress)");
            return;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO activity_log (user_id, action_type, details, ip_address)
                VALUES (:user_id, 'csrf_failure', :details, :ip_address)
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'details' => 'CSRF validation failed: ' . $reason,
                'ip_address' => $ipAddress
            ]);
        } catch (Exception $e) {
            error_log('Failed to log CSRF failure: ' . $e->getMessage());
        }
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanupExpiredTokens() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                DELETE FROM csrf_tokens
                WHERE expires_at < NOW()
                OR (used = TRUE AND used_at < DATE_SUB(NOW(), INTERVAL 1 DAY))
            ");
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log('CSRF token cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify CSRF token from POST/PUT/DELETE request
     * Call this at the start of any state-changing operation
     * 
     * @throws Exception If token is invalid
     */
    public static function verify() {
        // Skip verification for GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }

        // Get token from POST data or headers
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        // Check environment
        $appEnv = getenv('APP_ENV');
        error_log("CSRF: APP_ENV = " . ($appEnv ?: 'not set'));

        // In development mode, be more lenient but still try to validate
        if ($appEnv === 'development') {
            error_log('CSRF: Development mode detected - lenient validation');
            
            if (!$token) {
                error_log('CSRF: No token provided (development mode - allowing)');
                return true;
            }
            
            // Try to validate, but don't fail if it doesn't work
            try {
                $valid = self::validateToken($token);
                if (!$valid) {
                    error_log('CSRF: Token validation failed (development mode - allowing anyway)');
                }
                return true;
            } catch (Exception $e) {
                error_log('CSRF: Validation error (development mode - allowing): ' . $e->getMessage());
                return true;
            }
        }

        // Production mode - strict validation
        error_log('CSRF: Production mode - strict validation');
        if (!$token || !self::validateToken($token)) {
            http_response_code(403);
            throw new Exception('CSRF token validation failed');
        }

        return true;
    }
}
