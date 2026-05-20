<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-05-01
// Part of: SPED LMS — User Model

require_once __DIR__ . '/../../config/db.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT id, name, email, password_hash, role, status, created_at
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, status, created_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create new user
     */
    public function create($name, $firstName, $middleName, $lastName, $suffix, $email, $contactNumber, $password) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO users (name, first_name, middle_name, last_name, suffix, email, contact_number, password_hash, role, status)
            VALUES (:name, :first_name, :middle_name, :last_name, :suffix, :email, :contact_number, :password_hash, 'user', 'active')
        ");

        $stmt->execute([
            'name' => $name,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => $suffix,
            'email' => $email,
            'contact_number' => $contactNumber,
            'password_hash' => $passwordHash
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update user role
     */
    public function updateRole($userId, $role) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET role = :role, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            'role' => $role,
            'id' => $userId
        ]);
    }

    /**
     * Update user status
     */
    public function updateStatus($userId, $status) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            'status' => $status,
            'id' => $userId
        ]);
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt($email, $result, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        // Try to get user_id from email (if user exists)
        $userId = null;
        try {
            $userStmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $userStmt->execute(['email' => $email]);
            $user = $userStmt->fetch();
            if ($user) {
                $userId = $user['id'];
            }
        } catch (Exception $e) {
            // If user doesn't exist, userId stays null
        }

        $stmt = $this->db->prepare("
            INSERT INTO login_log (user_id, email, ip_address, result)
            VALUES (:user_id, :email, :ip_address, :result)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $ipAddress,
            'result' => $result
        ]);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM users
            WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get all users (admin only)
     */
    public function getAllUsers() {
        $stmt = $this->db->query("
            SELECT id, name, email, role, status, created_at
            FROM users
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Verify password
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }

    /**
     * Generate OTP for email verification
     */
    public function generateOTP($userId) {
        $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $this->db->prepare("
            UPDATE users
            SET email_verification_token = :token,
                email_verification_expires = :expires,
                verification_attempts = 0
            WHERE id = :id
        ");

        $stmt->execute([
            'token' => $otp,
            'expires' => $expires,
            'id' => $userId
        ]);

        return $otp;
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP($userId, $otp) {
        $stmt = $this->db->prepare("
            SELECT email_verification_token, email_verification_expires, verification_attempts
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Check if too many attempts
        if ($user['verification_attempts'] >= 3) {
            return ['success' => false, 'message' => 'Too many attempts. Please request a new code.'];
        }

        // Check if OTP expired
        if (strtotime($user['email_verification_expires']) < time()) {
            return ['success' => false, 'message' => 'Verification code expired. Please request a new one.'];
        }

        // Check if OTP matches
        if ($user['email_verification_token'] !== $otp) {
            // Increment attempts
            $this->incrementVerificationAttempts($userId);
            return ['success' => false, 'message' => 'Invalid verification code'];
        }

        // Success - mark email as verified
        $this->markEmailVerified($userId);
        return ['success' => true, 'message' => 'Email verified successfully'];
    }

    /**
     * Mark email as verified
     */
    public function markEmailVerified($userId) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET email_verified = TRUE,
                email_verification_token = NULL,
                email_verification_expires = NULL,
                verification_attempts = 0
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Increment verification attempts
     */
    public function incrementVerificationAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET verification_attempts = verification_attempts + 1
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified($userId) {
        $stmt = $this->db->prepare("
            SELECT email_verified FROM users WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch();
        return $result ? (bool)$result['email_verified'] : false;
    }

    /**
     * Find user by Google ID
     */
    public function findByGoogleId($googleId) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE google_id = :google_id LIMIT 1
        ");
        $stmt->execute(['google_id' => $googleId]);
        return $stmt->fetch();
    }

    /**
     * Create user from Google data
     */
    public function createFromGoogle($googleId, $email, $name, $firstName, $lastName, $profilePicture) {
        $stmt = $this->db->prepare("
            INSERT INTO users (
                name, first_name, last_name, email, google_id, profile_picture,
                auth_provider, email_verified, role, status, password_hash
            ) VALUES (
                :name, :first_name, :last_name, :email, :google_id, :profile_picture,
                'google', TRUE, 'user', 'active', ''
            )
        ");

        $stmt->execute([
            'name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'google_id' => $googleId,
            'profile_picture' => $profilePicture
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Link Google account to existing user
     */
    public function linkGoogleAccount($userId, $googleId, $profilePicture) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET google_id = :google_id,
                profile_picture = :profile_picture,
                email_verified = TRUE
            WHERE id = :id
        ");

        return $stmt->execute([
            'google_id' => $googleId,
            'profile_picture' => $profilePicture,
            'id' => $userId
        ]);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE role = :role AND status = 'active'
        ");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    /**
     * Get all users with statistics
     */
    public function getAllUsersWithStats($filters = []) {
        $sql = "
            SELECT id, name, email, role, status, created_at, deleted_at, locked_until
            FROM users
            WHERE deleted_at IS NULL
        ";
        $params = [];

        // Apply filters
        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get user statistics
     */
    public function getUserStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN role = 'parent' THEN 1 ELSE 0 END) as parents,
                SUM(CASE WHEN role = 'sped_teacher' THEN 1 ELSE 0 END) as teachers,
                SUM(CASE WHEN role = 'guidance' THEN 1 ELSE 0 END) as guidance,
                SUM(CASE WHEN role = 'principal' THEN 1 ELSE 0 END) as principals,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
            FROM users
            WHERE deleted_at IS NULL
        ");
        return $stmt->fetch();
    }

    /**
     * Soft delete user
     */
    public function softDelete($userId) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET deleted_at = CURRENT_TIMESTAMP,
                status = 'inactive'
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Lock user account until specified time
     */
    public function lockAccount($userId, $minutes) {
        $lockUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
        
        $stmt = $this->db->prepare("
            UPDATE users
            SET locked_until = :locked_until
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'locked_until' => $lockUntil,
            'id' => $userId
        ]);
    }

    /**
     * Check if account is locked
     */
    public function isAccountLocked($userId) {
        $stmt = $this->db->prepare("
            SELECT locked_until
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch();
        
        if (!$result || !$result['locked_until']) {
            return false;
        }
        
        // Check if lock has expired
        if (strtotime($result['locked_until']) < time()) {
            // Lock expired, clear it
            $this->unlockAccount($userId);
            return false;
        }
        
        return true;
    }

    /**
     * Unlock user account
     */
    public function unlockAccount($userId) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET locked_until = NULL
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Get user's full details
     */
    public function getUserDetails($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                id, name, first_name, middle_name, last_name, suffix,
                email, contact_number, role, status, email_verified,
                auth_provider, profile_picture, created_at, updated_at,
                locked_until, deleted_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch();
    }
}
