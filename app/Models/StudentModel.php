<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-05-04
// Part of: SPED LMS — Student Record Model

require_once __DIR__ . '/../../config/db.php';

class StudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Generate unique 12-digit LRN
     * Format: YYYYMMDDNNNN (Year + Month + Day + 4-digit sequence)
     * Example: 202605040001
     */
    public function generateLRN() {
        $datePrefix = date('Ymd'); // YYYYMMDD
        
        // Find the next sequence number for today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM student_records
            WHERE lrn LIKE :date_prefix
        ");
        $stmt->execute(['date_prefix' => $datePrefix . '%']);
        $result = $stmt->fetch();
        
        $sequence = ($result['count'] + 1);
        $sequenceStr = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        $lrn = $datePrefix . $sequenceStr;
        
        error_log("Generated LRN: $lrn");
        return $lrn;
    }

    /**
     * Create student record from enrollment
     * Uses existing LRN if provided (transfer/returning students)
     * Generates new LRN if not provided (new students)
     */
    public function createStudentRecord($enrollmentId, $verifiedBy) {
        try {
            // Get enrollment data
            $stmt = $this->db->prepare("
                SELECT * FROM enrollment_submissions
                WHERE id = :id
            ");
            $stmt->execute(['id' => $enrollmentId]);
            $enrollment = $stmt->fetch();
            
            if (!$enrollment) {
                throw new Exception("Enrollment not found: $enrollmentId");
            }
            
            // Check if LRN exists in enrollment (transfer/returning students)
            $lrn = null;
            if (!empty($enrollment['lrn']) && preg_match('/^\d{12}$/', $enrollment['lrn'])) {
                // Use existing LRN
                $lrn = $enrollment['lrn'];
                error_log("Using existing LRN from enrollment: $lrn");
            } else {
                // Generate new LRN for new students
                $lrn = $this->generateLRN();
                error_log("Generated new LRN: $lrn");
            }
            
            // Create student record
            $stmt = $this->db->prepare("
                INSERT INTO student_records (
                    enrollment_id, lrn, student_name, date_of_birth,
                    disability_type, psa_number, pwd_id_number, verified_by
                ) VALUES (
                    :enrollment_id, :lrn, :student_name, :date_of_birth,
                    :disability_type, :psa_number, :pwd_id_number, :verified_by
                )
            ");
            
            // Determine disability type from enrollment
            $disabilities = [];
            if ($enrollment['disability_visual']) $disabilities[] = 'Visual';
            if ($enrollment['disability_hearing']) $disabilities[] = 'Hearing';
            if ($enrollment['disability_learning']) $disabilities[] = 'Learning';
            if ($enrollment['disability_speech']) $disabilities[] = 'Speech';
            if ($enrollment['disability_intellectual']) $disabilities[] = 'Intellectual';
            if ($enrollment['disability_physical']) $disabilities[] = 'Physical';
            if ($enrollment['disability_emotional']) $disabilities[] = 'Emotional';
            if ($enrollment['disability_chronic_illness']) $disabilities[] = 'Chronic Illness';
            if ($enrollment['disability_others']) $disabilities[] = $enrollment['disability_others_specify'];
            
            $disabilityType = !empty($disabilities) ? implode(', ', $disabilities) : 'Not specified';
            
            $result = $stmt->execute([
                'enrollment_id' => $enrollmentId,
                'lrn' => $lrn,
                'student_name' => $enrollment['first_name'] . ' ' . $enrollment['last_name'],
                'date_of_birth' => $enrollment['birth_date'],
                'disability_type' => $disabilityType,
                'psa_number' => $enrollment['lrn'] ?? null,
                'pwd_id_number' => null,
                'verified_by' => $verifiedBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create student record");
            }
            
            $studentId = $this->db->lastInsertId();
            error_log("Created student record ID: $studentId with LRN: $lrn");
            
            return [
                'id' => $studentId,
                'lrn' => $lrn,
                'name' => $enrollment['first_name'] . ' ' . $enrollment['last_name']
            ];
            
        } catch (Exception $e) {
            error_log("StudentModel->createStudentRecord() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create learner account with LRN as credentials
     * Returns: ['user_id' => id, 'lrn' => lrn, 'temp_password' => password]
     */
    /**
     * Create learner account with LRN as credentials
     * Handles both new accounts and existing accounts (transfer/returning students)
     * Returns: ['user_id' => id, 'lrn' => lrn, 'temp_password' => password, 'is_existing' => bool]
     */
    public function createLearnerAccount($studentId, $lrn, $enrollmentData) {
        try {
            // Generate temporary password (8 characters)
            $tempPassword = bin2hex(random_bytes(4)); // 8 hex chars
            $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);
            
            // Get parent email from enrollment
            // Support both 'enrollment_id' and 'id' keys from the enrollment record
            $enrollmentId = $enrollmentData['enrollment_id'] ?? $enrollmentData['id'] ?? null;
            $stmt = $this->db->prepare("
                SELECT u.id, u.email, u.first_name, u.last_name
                FROM enrollment_submissions es
                JOIN users u ON es.parent_id = u.id
                WHERE es.id = :enrollment_id
            ");
            $stmt->execute(['enrollment_id' => $enrollmentId]);
            $parent = $stmt->fetch();
            
            if (!$parent) {
                throw new Exception("Parent not found for enrollment");
            }
            
            $learnerName = $enrollmentData['first_name'] . ' ' . $enrollmentData['last_name'];
            $learnerEmail = 'learner_' . $lrn . '@spedlms.local'; // System email
            
            // Check if account already exists with this LRN
            $stmt = $this->db->prepare("
                SELECT id, status FROM users WHERE email = :email
            ");
            $stmt->execute(['email' => $learnerEmail]);
            $existingUser = $stmt->fetch();
            
            $userId = null;
            $isExisting = false;
            
            if ($existingUser) {
                // Account exists - reset password and reactivate
                $userId = $existingUser['id'];
                $isExisting = true;
                
                error_log("Existing learner account found ID: $userId - Resetting password");
                
                $stmt = $this->db->prepare("
                    UPDATE users
                    SET password_hash = :password_hash,
                        status = 'active',
                        name = :name,
                        first_name = :first_name,
                        last_name = :last_name
                    WHERE id = :id
                ");
                
                $result = $stmt->execute([
                    'password_hash' => $passwordHash,
                    'name' => $learnerName,
                    'first_name' => $enrollmentData['first_name'],
                    'last_name' => $enrollmentData['last_name'],
                    'id' => $userId
                ]);
                
                if (!$result) {
                    throw new Exception("Failed to reset learner account password");
                }
                
                error_log("Reset password for existing learner account ID: $userId with LRN: $lrn");
                
            } else {
                // Create new learner user account
                $stmt = $this->db->prepare("
                    INSERT INTO users (
                        name, first_name, last_name, email, password_hash,
                        role, status, email_verified, auth_provider
                    ) VALUES (
                        :name, :first_name, :last_name, :email, :password_hash,
                        'learner', 'active', TRUE, 'local'
                    )
                ");
                
                $result = $stmt->execute([
                    'name' => $learnerName,
                    'first_name' => $enrollmentData['first_name'],
                    'last_name' => $enrollmentData['last_name'],
                    'email' => $learnerEmail,
                    'password_hash' => $passwordHash
                ]);
                
                if (!$result) {
                    throw new Exception("Failed to create learner account");
                }
                
                $userId = $this->db->lastInsertId();
                error_log("Created new learner account ID: $userId with LRN: $lrn");
            }
            
            // Send credentials email to parent
            $parentName = $parent['first_name'] . ' ' . $parent['last_name'];
            $this->sendLRNCredentialsEmail($parent['email'], $learnerName, $lrn, $tempPassword, $parentName, $isExisting);
            
            // Create in-app notification for parent
            $this->createLRNNotification($parent['id'], $learnerName, $lrn, $tempPassword, $isExisting);
            
            return [
                'user_id' => $userId,
                'lrn' => $lrn,
                'temp_password' => $tempPassword,
                'parent_email' => $parent['email'],
                'is_existing' => $isExisting
            ];
            
        } catch (Exception $e) {
            error_log("StudentModel->createLearnerAccount() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send LRN credentials email to parent
     * Handles both new accounts and password resets for existing accounts
     */
    private function sendLRNCredentialsEmail($parentEmail, $learnerName, $lrn, $tempPassword, $parentName = 'Parent/Guardian', $isExisting = false) {
        try {
            // Check if MailHelper is available
            if (!class_exists('MailHelper')) {
                require_once __DIR__ . '/../Helpers/MailHelper.php';
            }
            
            if ($isExisting) {
                // Password reset for existing account
                $subject = "Learner Account Password Reset - LRN: $lrn";
                
                $body = "
                <h2>Learner Account Password Reset</h2>
                <p>Dear $parentName,</p>
                <p>Your child's enrollment has been re-verified and the learner account password has been reset.</p>
                
                <h3>Learner Information</h3>
                <p><strong>Name:</strong> $learnerName</p>
                <p><strong>LRN (Learner Reference Number):</strong> <strong>$lrn</strong></p>
                
                <h3>New Login Credentials</h3>
                <p><strong>Username (LRN):</strong> $lrn</p>
                <p><strong>New Temporary Password:</strong> $tempPassword</p>
                
                <p style='color: #a01422; font-weight: bold;'>
                    This is a returning/transfer student account. The password has been reset for security.
                    Please change your password on first login.
                </p>
                
                <p>If you have any questions, please contact the school.</p>
                <p>Best regards,<br>SPED LMS System</p>
                ";
            } else {
                // New account creation
                $subject = "Learner Account Created - LRN: $lrn";
                
                $body = "
                <h2>Learner Account Created</h2>
                <p>Dear $parentName,</p>
                <p>Your child's enrollment has been verified and a learner account has been created.</p>
                
                <h3>Learner Information</h3>
                <p><strong>Name:</strong> $learnerName</p>
                <p><strong>LRN (Learner Reference Number):</strong> <strong>$lrn</strong></p>
                
                <h3>Login Credentials</h3>
                <p><strong>Username (LRN):</strong> $lrn</p>
                <p><strong>Temporary Password:</strong> $tempPassword</p>
                
                <p style='color: #a01422; font-weight: bold;'>
                    Please change your password on first login for security.
                </p>
                
                <p>If you have any questions, please contact the school.</p>
                <p>Best regards,<br>SPED LMS System</p>
                ";
            }
            
            @MailHelper::sendNotification($parentEmail, $parentName, $subject, $body);
            error_log("LRN credentials email sent to: $parentEmail (isExisting: " . ($isExisting ? 'true' : 'false') . ")");
            
        } catch (Throwable $e) {
            error_log("Failed to send LRN credentials email: " . $e->getMessage());
            // Don't throw - email failure shouldn't block account creation
        }
    }

    /**
     * Create in-app notification for LRN credentials
     * Handles both new accounts and password resets for existing accounts
     */
    private function createLRNNotification($parentId, $learnerName, $lrn, $tempPassword, $isExisting = false) {
        try {
            require_once __DIR__ . '/../Models/NotificationModel.php';
            
            $notificationModel = new NotificationModel();
            
            if ($isExisting) {
                // Password reset notification
                $notificationModel->create(
                    $parentId,
                    'learner_password_reset',
                    'Learner Account Password Reset',
                    "Your child $learnerName's learner account password has been reset. LRN: $lrn. Check your email for new login credentials.",
                    [
                        'learner_name' => $learnerName,
                        'lrn' => $lrn,
                        'temp_password' => $tempPassword,
                        'is_existing' => true
                    ]
                );
            } else {
                // New account notification
                $notificationModel->create(
                    $parentId,
                    'learner_account_created',
                    'Learner Account Created - LRN Credentials',
                    "Your child $learnerName's learner account has been created. LRN: $lrn. Check your email for login credentials.",
                    [
                        'learner_name' => $learnerName,
                        'lrn' => $lrn,
                        'temp_password' => $tempPassword,
                        'is_existing' => false
                    ]
                );
            }
            
            error_log("LRN notification created for parent ID: $parentId (isExisting: " . ($isExisting ? 'true' : 'false') . ")");
            
        } catch (Exception $e) {
            error_log("Failed to create LRN notification: " . $e->getMessage());
            // Don't throw - notification failure shouldn't block account creation
        }
    }

    /**
     * Find student by LRN
     */
    public function findByLRN($lrn) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE lrn = :lrn
            LIMIT 1
        ");
        $stmt->execute(['lrn' => $lrn]);
        return $stmt->fetch();
    }

    /**
     * Get student by user ID (for learner accounts)
     */
    public function getByUserId($userId) {
        try {
            // Get user's email (learner_LRN@spedlms.local format)
            $stmt = $this->db->prepare("SELECT email FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return null;
            }
            
            // Extract LRN from email
            if (preg_match('/learner_(\d+)@/', $user['email'], $matches)) {
                $lrn = $matches[1];
                return $this->findByLRN($lrn);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log('Failed to get student by user ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find student by enrollment ID
     */
    public function findByEnrollmentId($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM student_records
            WHERE enrollment_id = :enrollment_id
            LIMIT 1
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        return $stmt->fetch();
    }

    /**
     * Get student with enrollment and parent info
     */
    public function getWithDetails($studentId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.*, u.name as parent_name, u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON es.parent_id = u.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Get all students (for admin/teacher)
     */
    public function getAll($limit = 100) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.*, u.name as parent_name, u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON es.parent_id = u.id
            ORDER BY sr.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update student record
     */
    public function update($studentId, $data) {
        $fields = [];
        $params = ['id' => $studentId];
        
        $validColumns = ['student_name', 'date_of_birth', 'disability_type', 'psa_number', 'pwd_id_number'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $validColumns)) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE student_records SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("StudentModel->update() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete student record
     */
    public function delete($studentId) {
        $stmt = $this->db->prepare("
            DELETE FROM student_records
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $studentId]);
    }

    /**
     * Get all students with their latest enrollment info
     */
    public function getAllStudents() {
        $stmt = $this->db->query("
            SELECT 
                sr.*,
                es.school_year as latest_school_year,
                es.grade_level_to_enroll as current_grade_level,
                es.status as enrollment_status,
                es.parent_id,
                u.name as parent_name,
                u.email as parent_email
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN users u ON es.parent_id = u.id
            ORDER BY sr.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all enrollments for a student by enrollment_id (primary) or LRN (fallback)
     */
    public function getEnrollmentsByLRN($lrn) {
        $stmt = $this->db->prepare("
            SELECT 
                es.*,
                u.name as parent_name,
                u.email as parent_email,
                verifier.name as verifier_name
            FROM enrollment_submissions es
            JOIN users u ON es.parent_id = u.id
            LEFT JOIN users verifier ON es.verified_by = verifier.id
            JOIN student_records sr ON sr.enrollment_id = es.id
            WHERE sr.lrn = :lrn AND es.is_draft = FALSE
            ORDER BY es.created_at DESC
        ");
        $stmt->execute(['lrn' => $lrn]);
        return $stmt->fetchAll();
    }

    /**
     * Find student by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                sr.*,
                es.parent_id,
                es.grade_level_to_enroll as current_grade_level,
                es.school_year as latest_school_year,
                es.status as enrollment_status,
                u.name as parent_name,
                u.email as parent_email,
                u.contact_number
            FROM student_records sr
            LEFT JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN users u ON es.parent_id = u.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get verified students ready for assessment (Process 3)
     * Returns students with verified enrollment status
     */
    public function getVerifiedStudents() {
        $stmt = $this->db->query("
            SELECT DISTINCT
                sr.id,
                sr.lrn,
                sr.student_name,
                sr.date_of_birth,
                sr.disability_type,
                es.school_year,
                es.grade_level_to_enroll
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE es.status = 'verified'
            ORDER BY sr.student_name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get full student details for assessment auto-fill (Process 3)
     * Returns all data needed for Part I Section A
     */
    public function getFullDetails($studentId) {
        $stmt = $this->db->prepare("
            SELECT 
                sr.*,
                es.last_name,
                es.first_name,
                es.middle_name,
                es.extension_name,
                es.birth_date,
                es.sex,
                es.birth_place,
                es.mother_tongue,
                es.is_indigenous_people,
                es.indigenous_group,
                es.is_4ps_beneficiary,
                es.fourps_household_id,
                es.current_house_no,
                es.current_barangay,
                es.current_city,
                es.current_province,
                es.current_zip_code,
                es.father_last_name,
                es.father_first_name,
                es.father_middle_name,
                es.father_contact_number,
                es.mother_maiden_last_name,
                es.mother_first_name,
                es.mother_middle_name,
                es.mother_contact_number,
                es.guardian_last_name,
                es.guardian_first_name,
                es.guardian_middle_name,
                es.guardian_contact_number,
                es.previous_school_name,
                es.previous_grade_level,
                es.previous_school_year,
                es.grade_level_to_enroll,
                es.school_year,
                es.enrollment_type,
                u.name as parent_name,
                u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            LEFT JOIN users u ON es.parent_id = u.id
            WHERE sr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $studentId]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Calculate age from birth_date
            $birthDate = new DateTime($result['birth_date']);
            $today = new DateTime();
            $result['age'] = $today->diff($birthDate)->y;
            
            // Format full address
            $result['full_address'] = trim(
                ($result['current_house_no'] ? $result['current_house_no'] . ', ' : '') .
                ($result['current_barangay'] ? $result['current_barangay'] . ', ' : '') .
                ($result['current_city'] ? $result['current_city'] . ', ' : '') .
                ($result['current_province'] ? $result['current_province'] . ' ' : '') .
                ($result['current_zip_code'] ? $result['current_zip_code'] : '')
            );
            
            // Format parent names
            $result['father_full_name'] = trim(
                ($result['father_first_name'] ?? '') . ' ' .
                ($result['father_middle_name'] ?? '') . ' ' .
                ($result['father_last_name'] ?? '')
            );
            
            $result['mother_full_name'] = trim(
                ($result['mother_first_name'] ?? '') . ' ' .
                ($result['mother_middle_name'] ?? '') . ' ' .
                ($result['mother_maiden_last_name'] ?? '')
            );
            
            $result['guardian_full_name'] = trim(
                ($result['guardian_first_name'] ?? '') . ' ' .
                ($result['guardian_middle_name'] ?? '') . ' ' .
                ($result['guardian_last_name'] ?? '')
            );
        }
        
        return $result;
    }

}
