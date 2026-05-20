<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-05-04
// Part of: SPED LMS — Enrollment Verification Controller

require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class VerificationController {
    private $enrollmentModel;
    private $studentModel;
    private $notificationModel;
    private $db;
    private $userId;
    private $userRole;

    public function __construct() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $this->userId = $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? 'user';
        
        // Check permission
        if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
            http_response_code(403);
            echo "Access Denied";
            exit;
        }
        
        $this->db = Database::getInstance()->getConnection();
        $this->enrollmentModel = new EnrollmentModel();
        $this->studentModel = new StudentModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * List all pending enrollments for verification
     */
    public function index() {
        try {
            // Get all pending enrollments
            $enrollments = $this->enrollmentModel->getPending();
            
            // Add document counts
            foreach ($enrollments as &$enrollment) {
                $documents = $this->enrollmentModel->getDocuments($enrollment['id']);
                $enrollment['documents'] = $documents;
                $enrollment['total_docs'] = count($documents);
                $enrollment['approved_docs'] = count(array_filter($documents, fn($d) => $d['status'] === 'approved'));
                $enrollment['pending_docs'] = count(array_filter($documents, fn($d) => $d['status'] === 'pending'));
                $enrollment['rejected_docs'] = count(array_filter($documents, fn($d) => $d['status'] === 'rejected'));
            }
            
            // Log activity
            $this->logActivity('verification.list', 'enrollment_submissions', null, 'Viewed verification dashboard');
            
            // Load view
            require __DIR__ . '/../Views/verification/index.php';
            
        } catch (Exception $e) {
            error_log("VerificationController->index() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading enrollments";
        }
    }

    /**
     * Show enrollment detail with all 76 BEEF fields
     */
    public function show($id) {
        try {
            // Get enrollment with all details
            $enrollment = $this->enrollmentModel->findById($id);
            
            if (!$enrollment) {
                http_response_code(404);
                echo "Enrollment not found";
                return;
            }
            
            // Get documents
            $documents = $this->enrollmentModel->getDocuments($id);
            
            // Check if all documents approved
            $allApproved = $this->enrollmentModel->areAllDocumentsApproved($id);
            
            // Log activity
            $this->logActivity('verification.view', 'enrollment_submissions', $id, 'Viewed enrollment detail');
            
            // Load view
            require __DIR__ . '/../Views/verification/show.php';
            
        } catch (Exception $e) {
            error_log("VerificationController->show() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading enrollment";
        }
    }

    /**
     * Verify enrollment (mark as verified when all docs approved)
     * This is called after all documents are individually approved
     */
    public function verify($id) {
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            // Get enrollment
            $enrollment = $this->enrollmentModel->findById($id);
            
            if (!$enrollment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
                return;
            }
            
            // Check if enrollment is already verified with learner account
            if ($enrollment['learner_account_created']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Learner account already created for this enrollment']);
                return;
            }
            
            // Check if enrollment status is verified
            if ($enrollment['status'] !== 'verified') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Enrollment must be verified first before creating learner account']);
                return;
            }
            
            // Create student record with LRN
            $studentData = $this->studentModel->createStudentRecord($id, $this->userId);
            
            // Prepare enrollment data with correct field name
            $enrollmentDataForAccount = $enrollment;
            $enrollmentDataForAccount['enrollment_id'] = $enrollment['id'];
            
            // Create learner account
            $accountData = $this->studentModel->createLearnerAccount(
                $studentData['id'],
                $studentData['lrn'],
                $enrollmentDataForAccount
            );
            
            // Mark learner account as created
            $this->enrollmentModel->update($id, [
                'learner_account_created' => true,
                'lrn' => $studentData['lrn']
            ]);
            
            // Send notification to parent (email + in-app) - don't let this fail the whole process
            try {
                $this->notifyParentVerified($enrollment, $studentData['lrn'], $accountData);
            } catch (Throwable $e) {
                error_log("Failed to send parent notification (non-fatal): " . $e->getMessage());
            }
            
            // Log activity
            $this->logActivity(
                'enrollment.verified',
                'enrollment_submissions',
                $id,
                "Created learner account with LRN: {$studentData['lrn']}"
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Learner account created successfully',
                'lrn' => $studentData['lrn'],
                'learner_id' => $accountData['user_id']
            ]);
            
        } catch (Exception $e) {
            error_log("VerificationController->verify() ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating learner account: ' . $e->getMessage()]);
        }
    }

    /**
     * Send verification notification to parent (email + in-app)
     */
    private function notifyParentVerified($enrollment, $lrn, $accountData) {
        try {
            // Get parent user ID
            $stmt = $this->db->prepare("
                SELECT id FROM users WHERE id = :parent_id
            ");
            $stmt->execute(['parent_id' => $enrollment['parent_id']]);
            $parent = $stmt->fetch();
            
            if (!$parent) {
                error_log("Parent not found for enrollment: " . $enrollment['id']);
                return;
            }
            
            // Create in-app notification
            $this->notificationModel->create(
                $parent['id'],
                'enrollment_verified',
                'Enrollment Verified - Learner Account Created',
                "Your child's enrollment has been verified. Learner Reference Number (LRN): $lrn. Login credentials have been sent to your email.",
                [
                    'enrollment_id' => $enrollment['id'],
                    'lrn' => $lrn,
                    'learner_id' => $accountData['user_id'],
                    'temp_password' => $accountData['temp_password']
                ]
            );
            
            // Send email notification
            $subject = "Enrollment Verified - Learner Account Created";
            
            $body = "
            <h2>Enrollment Verified</h2>
            <p>Dear {$enrollment['parent_name']},</p>
            <p>Your child's enrollment has been verified and approved by the SPED Teacher.</p>
            
            <h3>Learner Information</h3>
            <p><strong>Learner Reference Number (LRN):</strong> <strong>$lrn</strong></p>
            <p><strong>Status:</strong> Active</p>
            
            <h3>Next Steps</h3>
            <p>A learner account has been created. You will receive a separate email with login credentials for your child's account.</p>
            
            <p>If you have any questions, please contact the school.</p>
            <p>Best regards,<br>SPED LMS System</p>
            ";
            
            @MailHelper::sendNotification($enrollment['parent_email'], $enrollment['parent_name'], $subject, $body);
            
            error_log("Verification notification sent to parent ID: {$parent['id']} for enrollment: {$enrollment['id']}");
            
        } catch (Exception $e) {
            error_log("Failed to send verification notification: " . $e->getMessage());
        }
    }

    /**
     * Log activity
     */
    private function logActivity($actionType, $table, $recordId, $details) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address)
                VALUES (:user_id, :action_type, :affected_table, :affected_record_id, :details, :ip_address)
            ");
            
            $stmt->execute([
                'user_id' => $this->userId,
                'action_type' => $actionType,
                'affected_table' => $table,
                'affected_record_id' => $recordId,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}
