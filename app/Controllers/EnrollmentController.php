<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Controller

require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
if (file_exists(__DIR__ . '/../Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../Helpers/MailHelper.php';
}

class EnrollmentController {
    private $enrollmentModel;
    private $notificationModel;
    private $basePath;

    public function __construct() {
        $this->enrollmentModel = new EnrollmentModel();
        $this->notificationModel = new NotificationModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Show enrollment type selection
     */
    public function index() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            $_SESSION['error'] = 'You must be logged in as a parent to enroll.';
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Clean up old drafts (7+ days)
        $this->enrollmentModel->cleanupOldDrafts();
        
        // Check for existing draft
        $draft = $this->enrollmentModel->getDraftByParentId($userId);
        
        // Check for previous enrollment (for returning students)
        $previousEnrollment = $this->enrollmentModel->getLatestByParentId($userId);

        // Pass basePath to view
        $basePath = $this->basePath;

        require_once __DIR__ . '/../Views/enrollment/index.php';
    }

    /**
     * Discard draft
     */
    public function discardDraft() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $this->enrollmentModel->deleteDraftByParentId($userId);
        
        $_SESSION['success'] = 'Draft discarded successfully.';
        header('Location: ' . $this->basePath . '/enrollment');
        exit;
    }

    /**
     * Keep session alive (AJAX endpoint)
     */
    public function keepalive() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        // Touch session to extend timeout
        $_SESSION['last_activity'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'Session extended',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Show enrollment form
     */
    public function create() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $enrollmentType = $_GET['type'] ?? 'new';
        $userId = $_SESSION['user_id'];
        
        // Load draft if exists
        $draft = $this->enrollmentModel->getDraftByParentId($userId);
        
        // Load previous enrollment for returning students
        $previousEnrollment = null;
        if ($enrollmentType === 'returning') {
            // Check if previous_id is provided (from search)
            $previousId = $_GET['previous_id'] ?? null;
            
            if ($previousId) {
                // Load specific previous enrollment
                $previousEnrollment = $this->enrollmentModel->findById($previousId);
                
                if (!$previousEnrollment) {
                    $_SESSION['error'] = 'Previous enrollment not found';
                    header('Location: ' . $this->basePath . '/enrollment/returning-lookup');
                    exit;
                }
                
                error_log("=== RETURNING ENROLLMENT AUTO-FILL ===");
                error_log("Previous ID: $previousId");
                error_log("Student: " . $previousEnrollment['first_name'] . ' ' . $previousEnrollment['last_name']);
                error_log("LRN: " . ($previousEnrollment['lrn'] ?? 'none'));
            } else {
                // Load latest enrollment by same parent (old behavior)
                $previousEnrollment = $this->enrollmentModel->getLatestByParentId($userId);
                
                if ($previousEnrollment) {
                    error_log("=== RETURNING ENROLLMENT (SAME PARENT) ===");
                    error_log("Student: " . $previousEnrollment['first_name'] . ' ' . $previousEnrollment['last_name']);
                }
            }
        }

        // Pass basePath to view
        $basePath = $this->basePath;

        require_once __DIR__ . '/../Views/enrollment/form.php';
    }

    /**
     * Save draft (AJAX)
     */
    public function saveDraft() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $data = $this->prepareEnrollmentData($_POST);
        
        // Ensure parent_id is set
        $data['parent_id'] = $userId;
        
        try {
            $enrollmentId = $this->enrollmentModel->saveDraft($userId, $data);
            echo json_encode([
                'success' => true,
                'message' => 'Draft saved successfully',
                'enrollment_id' => $enrollmentId
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save draft: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Submit enrollment
     */
    public function submit() {
        // CRITICAL DEBUG
        error_log("=== SUBMIT METHOD CALLED ===");
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
        error_log("Session Role: " . ($_SESSION['role'] ?? 'NOT SET'));
        error_log("POST data keys: " . implode(', ', array_keys($_POST)));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("ERROR: Not a POST request, redirecting...");
            header('Location: ' . $this->basePath . '/enrollment');
            exit;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            error_log("ERROR: Not logged in as parent, redirecting to login...");
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $errors = [];

        // Validate required fields
        $requiredFields = ['last_name', 'first_name', 'birth_date', 'sex', 'grade_level_to_enroll'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        // Validate signature
        if (empty($_POST['signature_data'])) {
            $errors[] = 'Parent/Guardian signature is required';
        }

        if (!empty($errors)) {
            error_log("VALIDATION ERRORS: " . implode(', ', $errors));
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $_POST;
            header('Location: ' . $this->basePath . '/enrollment/create?type=' . ($_POST['enrollment_type'] ?? 'new'));
            exit;
        }

        error_log("Validation passed, preparing data...");
        
        // Prepare data
        $data = $this->prepareEnrollmentData($_POST);
        
        // Ensure required metadata fields are set
        $data['parent_id'] = $userId;
        $data['is_draft'] = false;
        $data['status'] = 'pending';
        $data['submitted_at'] = date('Y-m-d H:i:s');
        
        // Ensure all required fields have defaults if missing
        $data['enrollment_type'] = $data['enrollment_type'] ?? 'new';
        $data['school_year'] = $data['school_year'] ?? (date('Y') . '-' . (date('Y') + 1));
        $data['previous_enrollment_id'] = $data['previous_enrollment_id'] ?? null;

        // DEBUG: Log the data being prepared
        error_log("=== ENROLLMENT SUBMISSION DEBUG ===");
        error_log("User ID: " . $userId);
        error_log("Data prepared: " . json_encode($data));

        try {
            // Clean up old drafts first (7+ days old)
            $this->enrollmentModel->cleanupOldDrafts();
            
            // Check if draft exists
            $draft = $this->enrollmentModel->getDraftByParentId($userId);
            error_log("Draft found: " . ($draft ? "YES (ID: {$draft['id']})" : "NO"));
            
            if ($draft) {
                // STRATEGY: Update existing draft with complete submission data
                $enrollmentId = $draft['id'];
                error_log("Converting draft ID $enrollmentId to final submission");
                
                // Merge draft data with new submission data (new data takes priority)
                $completeData = array_merge(
                    $draft, // Existing draft data
                    $data   // New submission data (overwrites draft)
                );
                
                // Ensure submission metadata is set
                $completeData['is_draft'] = false;
                $completeData['status'] = 'pending';
                $completeData['submitted_at'] = date('Y-m-d H:i:s');
                $completeData['parent_id'] = $userId;
                
                // Update the draft with complete data
                $updateResult = $this->enrollmentModel->update($enrollmentId, $completeData);
                error_log("Update result: " . ($updateResult ? "SUCCESS" : "FAILED"));
                
                if (!$updateResult) {
                    throw new Exception("Failed to update draft to final submission");
                }
                
            } else {
                // STRATEGY: Create new enrollment (no draft exists)
                error_log("Creating new enrollment (no draft found)");
                $enrollmentId = $this->enrollmentModel->create($data);
                error_log("Created enrollment ID: " . ($enrollmentId ? $enrollmentId : "FAILED - NO ID RETURNED"));
                
                if (!$enrollmentId) {
                    throw new Exception("Failed to create enrollment - no ID returned");
                }
            }

            error_log("Enrollment ID for document upload: $enrollmentId");

            // Handle document uploads OR copy from previous enrollment
            if ($data['enrollment_type'] === 'returning' && !empty($data['previous_enrollment_id'])) {
                // RETURNING STUDENT: Copy documents from previous enrollment
                error_log("=== RETURNING STUDENT: Copying documents from previous enrollment ===");
                $this->copyDocumentsFromPreviousEnrollment($data['previous_enrollment_id'], $enrollmentId);
            } else {
                // NEW/TRANSFER STUDENT: Handle new document uploads
                $this->handleDocumentUploads($enrollmentId);
            }

            // Create notification for parent
            $this->notificationModel->create(
                $userId,
                'enrollment_submitted',
                'Enrollment Submitted',
                'Your enrollment application has been submitted successfully. A SPED teacher will review it soon.'
            );

            // Notify SPED teachers
            $this->notifySPEDTeachers($enrollmentId);

            $_SESSION['success'] = 'Enrollment submitted successfully! You will be notified once it is reviewed.';
            header('Location: ' . $this->basePath . '/enrollment/status');
            exit;

        } catch (Exception $e) {
            // Log the error for debugging
            $errorMsg = $e->getMessage();
            error_log("=== ENROLLMENT SUBMISSION ERROR ===");
            error_log("Error: " . $errorMsg);
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("User ID: " . $userId);
            error_log("Data keys: " . implode(', ', array_keys($data)));
            error_log("Required fields present: " . json_encode([
                'parent_id' => isset($data['parent_id']),
                'is_draft' => isset($data['is_draft']),
                'status' => isset($data['status']),
                'last_name' => isset($data['last_name']),
                'first_name' => isset($data['first_name']),
                'birth_date' => isset($data['birth_date']),
                'sex' => isset($data['sex']),
                'grade_level_to_enroll' => isset($data['grade_level_to_enroll'])
            ]));
            
            // Show detailed error to user
            $_SESSION['error'] = "Enrollment submission failed: " . $errorMsg . 
                                 "<br><br><strong>Debug Info:</strong><br>" .
                                 "Please take a screenshot of this error and contact support.<br>" .
                                 "Error occurred at: " . date('Y-m-d H:i:s');
            $_SESSION['old_data'] = $_POST;
            
            header('Location: ' . $this->basePath . '/enrollment/create?type=' . ($_POST['enrollment_type'] ?? 'new'));
            exit;
        }
    }

    /**
     * View enrollment status
     */
    public function status() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $enrollments = $this->enrollmentModel->getByParentId($userId);

        // Pass basePath to view
        $basePath = $this->basePath;

        require_once __DIR__ . '/../Views/enrollment/status.php';
    }

    /**
     * View single enrollment
     */
    public function view($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $enrollment = $this->enrollmentModel->findById($id);
        
        if (!$enrollment) {
            $_SESSION['error'] = 'Enrollment not found';
            header('Location: ' . $this->basePath . '/enrollment/status');
            exit;
        }

        // Check permission
        if ($_SESSION['role'] === 'parent' && $enrollment['parent_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: ' . $this->basePath . '/enrollment/status');
            exit;
        }

        $documents = $this->enrollmentModel->getDocuments($id);

        require_once __DIR__ . '/../Views/enrollment/view.php';
    }

    /**
     * SPED Teacher: List all enrollments for review
     */
    public function review() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sped_teacher') {
            $_SESSION['error'] = 'Access denied. SPED teachers only.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $enrollments = $this->enrollmentModel->getAll();

        require_once __DIR__ . '/../Views/enrollment/review_list.php';
    }

    /**
     * SPED Teacher: View enrollment for review
     */
    public function reviewDetail($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sped_teacher') {
            $_SESSION['error'] = 'Access denied. SPED teachers only.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $enrollment = $this->enrollmentModel->findById($id);
        
        if (!$enrollment) {
            $_SESSION['error'] = 'Enrollment not found';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        $documents = $this->enrollmentModel->getDocuments($id);

        // Pass basePath to view
        $basePath = $this->basePath;

        require_once __DIR__ . '/../Views/enrollment/review_detail_v2.php';
    }

    /**
     * SPED Teacher: Approve entire enrollment (Simplified - Single Action)
     */
    public function approveEnrollment($enrollmentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sped_teacher') {
            $_SESSION['error'] = 'Access denied - SPED teachers only';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $userId = $_SESSION['user_id'];

        try {
            // 1. Get enrollment details
            $enrollment = $this->enrollmentModel->findById($enrollmentId);
            
            if (!$enrollment) {
                throw new Exception('Enrollment not found');
            }

            if ($enrollment['status'] !== 'pending') {
                throw new Exception('Enrollment is not pending review');
            }

            // 2. Approve ALL documents (if any exist)
            $documents = $this->enrollmentModel->getDocuments($enrollmentId);
            
            if (!empty($documents)) {
                foreach ($documents as $doc) {
                    $this->enrollmentModel->updateDocumentStatus($doc['id'], 'approved', $userId);
                }
            }

            // 3. Create student record (generates LRN if needed)
            require_once __DIR__ . '/../Models/StudentModel.php';
            $studentModel = new StudentModel();
            $studentData = $studentModel->createStudentRecord($enrollmentId, $userId);
            
            // 4. Create learner account with credentials
            $accountData = $studentModel->createLearnerAccount(
                $studentData['id'],
                $studentData['lrn'],
                $enrollment
            );
            
            // 5. Mark enrollment as verified and learner account created
            $this->enrollmentModel->updateStatus($enrollmentId, 'verified', $userId);
            $this->enrollmentModel->markLearnerAccountCreated($enrollmentId);

            // 6. Notify parent - enrollment fully approved with credentials
            $this->notificationModel->create(
                $enrollment['parent_id'],
                'enrollment_approved',
                'Enrollment Approved! ✅',
                "Enrollment approved for {$enrollment['first_name']} {$enrollment['last_name']}. LRN: {$studentData['lrn']}. Temporary password: {$accountData['temp_password']}",
                ['enrollment_id' => $enrollmentId, 'student_id' => $studentData['id']]
            );

            // 7. Send email notification with credentials
            if (class_exists('MailHelper')) {
                $credentialsHtml = "
                    <h2>Enrollment Approved! 🎉</h2>
                    <p>Your enrollment application for <strong>{$enrollment['first_name']} {$enrollment['last_name']}</strong> has been fully approved!</p>
                    
                    <div style='background-color: #f0f8ff; padding: 20px; border-left: 4px solid #1e4072; margin: 20px 0;'>
                        <h3 style='color: #1e4072; margin-top: 0;'>Learner Login Credentials</h3>
                        <p><strong>LRN (Username):</strong> <code style='background: #fff; padding: 5px 10px; border-radius: 3px;'>{$studentData['lrn']}</code></p>
                        <p><strong>Temporary Password:</strong> <code style='background: #fff; padding: 5px 10px; border-radius: 3px;'>{$accountData['temp_password']}</code></p>
                        <p style='color: #a01422; margin-top: 15px;'><strong>⚠️ Important:</strong> Please change this password after first login.</p>
                    </div>
                    
                    <p>The learner can now log in to the SPED LMS portal using these credentials.</p>
                ";
                
                MailHelper::sendNotification(
                    $enrollment['parent_email'],
                    $enrollment['parent_name'] ?? 'Parent',
                    'Enrollment Approved - SPED LMS',
                    $credentialsHtml
                );
            }

            $_SESSION['success'] = "Enrollment approved! Learner account created. LRN: {$studentData['lrn']}, Password: {$accountData['temp_password']}";

        } catch (Exception $e) {
            error_log('Approve enrollment error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to approve enrollment: ' . $e->getMessage();
        }

        // Redirect back to review list
        header('Location: ' . $this->basePath . '/enrollment/review');
        exit;
    }

    /**
     * SPED Teacher: Reject entire enrollment (Simplified - Single Action)
     */
    public function rejectEnrollment($enrollmentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sped_teacher') {
            $_SESSION['error'] = 'Access denied - SPED teachers only';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $rejectionReason = $_POST['rejection_reason'] ?? '';

        if (empty($rejectionReason) || trim($rejectionReason) === '') {
            $_SESSION['error'] = 'Please provide a reason for rejection';
            header('Location: ' . $this->basePath . '/enrollment/review/' . $enrollmentId);
            exit;
        }

        try {
            // 1. Get enrollment details
            $enrollment = $this->enrollmentModel->findById($enrollmentId);
            
            if (!$enrollment) {
                throw new Exception('Enrollment not found');
            }

            if ($enrollment['status'] !== 'pending') {
                throw new Exception('Enrollment is not pending review');
            }

            // 2. Mark enrollment as rejected with reason
            $this->enrollmentModel->updateStatus($enrollmentId, 'rejected', $userId, $rejectionReason);

            // 3. Mark all documents as rejected with the same reason
            $documents = $this->enrollmentModel->getDocuments($enrollmentId);
            
            foreach ($documents as $doc) {
                $this->enrollmentModel->updateDocumentStatus($doc['id'], 'rejected', $userId, $rejectionReason);
            }

            // 4. Notify parent with rejection reason
            $this->notificationModel->create(
                $enrollment['parent_id'],
                'enrollment_rejected',
                'Enrollment Rejected ❌',
                "Your enrollment has been rejected. Reason: {$rejectionReason}. Please review and resubmit.",
                ['enrollment_id' => $enrollmentId]
            );

            // 5. Send email notification
            if (class_exists('MailHelper')) {
                MailHelper::sendNotification(
                    $enrollment['parent_email'],
                    $enrollment['parent_name'] ?? 'Parent',
                    'Enrollment Rejected - SPED LMS',
                    "<h2>Enrollment Rejected</h2><p>Your enrollment application for <strong>{$enrollment['first_name']} {$enrollment['last_name']}</strong> has been rejected.</p><p><strong>Reason:</strong> {$rejectionReason}</p><p>Please review the feedback and resubmit your application.</p>"
                );
            }

            $_SESSION['success'] = 'Enrollment rejected. Parent has been notified with feedback.';

        } catch (Exception $e) {
            error_log('Reject enrollment error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reject enrollment: ' . $e->getMessage();
        }

        // Redirect back to review list
        header('Location: ' . $this->basePath . '/enrollment/review');
        exit;
    }

    /**
     * SPED Teacher: Approve document (Simplified)
     * NOTE: This method is kept for backward compatibility but is no longer used in the UI
     */
    public function approveDocument($documentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sped_teacher') {
            $_SESSION['error'] = 'Access denied - SPED teachers only';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $enrollmentId = $_POST['enrollment_id'] ?? null;

        if (!$enrollmentId) {
            $_SESSION['error'] = 'Missing enrollment ID';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        try {
            // 1. Update this document to approved
            $this->enrollmentModel->updateDocumentStatus($documentId, 'approved', $userId);

            // 2. Check if ALL uploaded documents are now approved
            $allApproved = $this->enrollmentModel->areAllDocumentsApproved($enrollmentId);

            if ($allApproved) {
                // 3. All documents approved → Mark enrollment as verified
                $this->enrollmentModel->updateStatus($enrollmentId, 'verified', $userId);

                // 4. Notify parent - enrollment fully approved
                $enrollment = $this->enrollmentModel->findById($enrollmentId);
                $this->notificationModel->create(
                    $enrollment['parent_id'],
                    'enrollment_approved',
                    'Enrollment Approved! ✅',
                    'All documents have been verified. Your enrollment is now complete!',
                    ['enrollment_id' => $enrollmentId]
                );

                // Send email
                if (class_exists('MailHelper')) {
                    MailHelper::sendNotification(
                        $enrollment['parent_email'],
                        $enrollment['parent_name'] ?? 'Parent',
                        'Enrollment Approved - SPED LMS',
                        "<h2>Enrollment Approved</h2><p>Your enrollment application for <strong>{$enrollment['first_name']} {$enrollment['last_name']}</strong> has been fully approved!</p><p>All documents have been verified.</p>"
                    );
                }

                $_SESSION['success'] = 'Document approved! All documents verified - Enrollment is now complete.';
            } else {
                // 5. Some documents still pending
                $docType = str_replace('_', ' ', ucwords($_POST['document_type'] ?? 'document'));
                $_SESSION['success'] = "Document approved: {$docType}. Waiting for other documents to be reviewed.";
            }

        } catch (Exception $e) {
            error_log('Approve document error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to approve document: ' . $e->getMessage();
        }

        // Redirect back to review page
        header('Location: ' . $this->basePath . '/enrollment/review/' . $enrollmentId);
        exit;
    }

    /**
     * SPED Teacher: Reject document (Simplified)
     */
    public function rejectDocument($documentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sped_teacher') {
            $_SESSION['error'] = 'Access denied - SPED teachers only';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $reviewNote = $_POST['review_note'] ?? 'Document rejected';
        $enrollmentId = $_POST['enrollment_id'] ?? null;

        if (!$enrollmentId) {
            $_SESSION['error'] = 'Missing enrollment ID';
            header('Location: ' . $this->basePath . '/enrollment/review');
            exit;
        }

        if (empty($reviewNote) || trim($reviewNote) === '') {
            $_SESSION['error'] = 'Please provide a reason for rejection';
            header('Location: ' . $this->basePath . '/enrollment/review/' . $enrollmentId);
            exit;
        }

        try {
            // 1. Update this document to rejected
            $this->enrollmentModel->updateDocumentStatus($documentId, 'rejected', $userId, $reviewNote);

            // 2. Mark entire enrollment as rejected (any rejected document = rejected enrollment)
            $this->enrollmentModel->updateStatus($enrollmentId, 'rejected', $userId);

            // 3. Notify parent
            $enrollment = $this->enrollmentModel->findById($enrollmentId);
            $docType = str_replace('_', ' ', ucwords($_POST['document_type'] ?? 'document'));
            
            $this->notificationModel->create(
                $enrollment['parent_id'],
                'document_rejected',
                'Document Rejected ❌',
                "Your {$docType} has been rejected. Reason: {$reviewNote}. Please resubmit.",
                ['enrollment_id' => $enrollmentId, 'document_id' => $documentId]
            );

            // Send email
            if (class_exists('MailHelper')) {
                MailHelper::sendNotification(
                    $enrollment['parent_email'],
                    $enrollment['parent_name'] ?? 'Parent',
                    'Document Rejected - SPED LMS',
                    "<h2>Document Rejected</h2><p>Your {$docType} for <strong>{$enrollment['first_name']} {$enrollment['last_name']}</strong> has been rejected.</p><p><strong>Reason:</strong> {$reviewNote}</p><p>Please upload a new document.</p>"
                );
            }

            $_SESSION['success'] = "Document rejected: {$docType}. Parent has been notified.";

        } catch (Exception $e) {
            error_log('Reject document error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reject document: ' . $e->getMessage();
        }

        // Redirect back to review page
        header('Location: ' . $this->basePath . '/enrollment/review/' . $enrollmentId);
        exit;
    }

    /**
     * Prepare enrollment data from POST
     */
    private function prepareEnrollmentData($post) {
        return [
            // Metadata - MUST be set by calling method or here
            'parent_id' => $post['parent_id'] ?? null, // Will be overridden by calling method
            'enrollment_type' => $post['enrollment_type'] ?? 'new',
            'school_year' => $post['school_year'] ?? date('Y') . '-' . (date('Y') + 1),
            'previous_enrollment_id' => $post['previous_enrollment_id'] ?? null,
            'is_draft' => isset($post['is_draft']) ? (bool)$post['is_draft'] : false,
            'status' => $post['status'] ?? 'draft',
            
            // Learner Information
            'lrn' => $post['lrn'] ?? null,
            'last_name' => strtoupper(trim($post['last_name'] ?? '')),
            'first_name' => strtoupper(trim($post['first_name'] ?? '')),
            'middle_name' => $post['middle_name'] ? strtoupper(trim($post['middle_name'])) : null,
            'extension_name' => $post['extension_name'] ?? null,
            'birth_date' => $post['birth_date'] ?? null,
            'sex' => $post['sex'] ?? '',
            'age' => $post['age'] ?? null,
            'birth_place' => $post['birth_place'] ?? null,
            'mother_tongue' => $post['mother_tongue'] ?? null,
            'is_indigenous_people' => isset($post['is_indigenous_people']) ? 1 : 0,
            'indigenous_group' => $post['indigenous_group'] ?? null,
            'is_4ps_beneficiary' => isset($post['is_4ps_beneficiary']) ? 1 : 0,
            'fourps_household_id' => $post['fourps_household_id'] ?? null,
            
            // Disabilities
            'disability_visual' => isset($post['disability_visual']) ? 1 : 0,
            'disability_hearing' => isset($post['disability_hearing']) ? 1 : 0,
            'disability_learning' => isset($post['disability_learning']) ? 1 : 0,
            'disability_speech' => isset($post['disability_speech']) ? 1 : 0,
            'disability_intellectual' => isset($post['disability_intellectual']) ? 1 : 0,
            'disability_physical' => isset($post['disability_physical']) ? 1 : 0,
            'disability_emotional' => isset($post['disability_emotional']) ? 1 : 0,
            'disability_chronic_illness' => isset($post['disability_chronic_illness']) ? 1 : 0,
            'disability_others' => isset($post['disability_others']) ? 1 : 0,
            'disability_others_specify' => $post['disability_others_specify'] ?? null,
            
            // Current Address
            'current_house_no' => $post['current_house_no'] ?? null,
            'current_barangay' => $post['current_barangay'] ?? null,
            'current_city' => $post['current_city'] ?? null,
            'current_province' => $post['current_province'] ?? null,
            'current_zip_code' => $post['current_zip_code'] ?? null,
            
            // Permanent Address
            'same_as_current_address' => isset($post['same_as_current_address']) ? 1 : 0,
            'permanent_house_no' => $post['permanent_house_no'] ?? null,
            'permanent_barangay' => $post['permanent_barangay'] ?? null,
            'permanent_city' => $post['permanent_city'] ?? null,
            'permanent_province' => $post['permanent_province'] ?? null,
            'permanent_zip_code' => $post['permanent_zip_code'] ?? null,
            
            // Parent/Guardian Info
            'father_last_name' => $post['father_last_name'] ? strtoupper(trim($post['father_last_name'])) : null,
            'father_first_name' => $post['father_first_name'] ? strtoupper(trim($post['father_first_name'])) : null,
            'father_middle_name' => $post['father_middle_name'] ? strtoupper(trim($post['father_middle_name'])) : null,
            'father_contact_number' => $post['father_contact_number'] ?? null,
            'mother_maiden_last_name' => $post['mother_maiden_last_name'] ? strtoupper(trim($post['mother_maiden_last_name'])) : null,
            'mother_first_name' => $post['mother_first_name'] ? strtoupper(trim($post['mother_first_name'])) : null,
            'mother_middle_name' => $post['mother_middle_name'] ? strtoupper(trim($post['mother_middle_name'])) : null,
            'mother_contact_number' => $post['mother_contact_number'] ?? null,
            'guardian_last_name' => $post['guardian_last_name'] ? strtoupper(trim($post['guardian_last_name'])) : null,
            'guardian_first_name' => $post['guardian_first_name'] ? strtoupper(trim($post['guardian_first_name'])) : null,
            'guardian_middle_name' => $post['guardian_middle_name'] ? strtoupper(trim($post['guardian_middle_name'])) : null,
            'guardian_contact_number' => $post['guardian_contact_number'] ?? null,
            
            // Previous School
            'previous_school_id' => $post['previous_school_id'] ?? null,
            'previous_school_name' => $post['previous_school_name'] ?? null,
            'previous_school_address' => $post['previous_school_address'] ?? null,
            'previous_grade_level' => $post['previous_grade_level'] ?? null,
            'previous_school_year' => $post['previous_school_year'] ?? null,
            'previous_school_type' => $post['previous_school_type'] ?? null,
            
            // Enrollment Details
            'grade_level_to_enroll' => $post['grade_level_to_enroll'] ?? '',
            'is_balik_aral' => isset($post['is_balik_aral']) ? 1 : 0,
            'is_pept_passer' => isset($post['is_pept_passer']) ? 1 : 0,
            'pept_rating' => $post['pept_rating'] ?? null,
            'is_als_passer' => isset($post['is_als_passer']) ? 1 : 0,
            'als_rating' => $post['als_rating'] ?? null,
            
            // SHS Details
            'shs_track' => $post['shs_track'] ?? null,
            'shs_strand' => $post['shs_strand'] ?? null,
            'shs_semester' => $post['shs_semester'] ?? null,
            
            // Learning Modality
            'modality_modular_print' => isset($post['modality_modular_print']) ? 1 : 0,
            'modality_modular_digital' => isset($post['modality_modular_digital']) ? 1 : 0,
            'modality_online' => isset($post['modality_online']) ? 1 : 0,
            'modality_educational_tv' => isset($post['modality_educational_tv']) ? 1 : 0,
            'modality_radio' => isset($post['modality_radio']) ? 1 : 0,
            'modality_blended' => isset($post['modality_blended']) ? 1 : 0,
            'modality_face_to_face' => isset($post['modality_face_to_face']) ? 1 : 0,
            'preferred_distance_modality' => $post['preferred_distance_modality'] ?? null,
            
            // Signature
            'signature_data' => $post['signature_data'] ?? null,
            'date_signed' => date('Y-m-d'),
            
            // Timestamps
            'draft_saved_at' => isset($post['is_draft']) && $post['is_draft'] ? date('Y-m-d H:i:s') : null,
            'submitted_at' => $post['submitted_at'] ?? null,
            'verified_by' => $post['verified_by'] ?? null,
            'verified_at' => $post['verified_at'] ?? null,
        ];
    }

    /**
     * Handle document uploads
     */
    private function handleDocumentUploads($enrollmentId) {
        $uploadDir = __DIR__ . '/../../public/uploads/enrollment/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $documentTypes = [
            'psa_birth_cert' => 'PSA Birth Certificate',
            'pwd_id' => 'PWD ID',
            'medical_record' => 'Medical Record',
            'beef_form' => 'BEEF Form'
        ];

        foreach ($documentTypes as $type => $label) {
            if (isset($_FILES[$type]) && $_FILES[$type]['error'] === UPLOAD_ERR_OK) {
                $result = $this->uploadFile($_FILES[$type], $uploadDir, $type . '_' . $enrollmentId . '_');
                
                if ($result['success']) {
                    $this->enrollmentModel->addDocument($enrollmentId, $type, $result['path']);
                }
            }
        }
    }

    /**
     * Copy documents from previous enrollment (for returning students)
     */
    private function copyDocumentsFromPreviousEnrollment($previousEnrollmentId, $newEnrollmentId) {
        error_log("Copying documents from enrollment $previousEnrollmentId to $newEnrollmentId");
        
        // Get documents from previous enrollment
        $previousDocuments = $this->enrollmentModel->getDocuments($previousEnrollmentId);
        
        if (empty($previousDocuments)) {
            error_log("No documents found in previous enrollment $previousEnrollmentId");
            return;
        }
        
        $copiedCount = 0;
        foreach ($previousDocuments as $doc) {
            // Only copy approved documents
            if ($doc['status'] === 'approved') {
                // Link the same file to new enrollment (no need to copy physical file)
                $this->enrollmentModel->addDocument(
                    $newEnrollmentId, 
                    $doc['document_type'], 
                    $doc['file_path']
                );
                $copiedCount++;
                error_log("Copied document: {$doc['document_type']} from previous enrollment");
            }
        }
        
        error_log("Total documents copied: $copiedCount");
    }

    /**
     * Upload file helper (simplified - no encryption)
     */
    private function uploadFile($file, $uploadDir, $prefix) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large'];
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true, 
                'path' => 'uploads/enrollment/' . $filename,
                'original_name' => $file['name']
            ];
        }

        return ['success' => false, 'error' => 'Upload failed'];
    }

    /**
     * Notify SPED teachers of new enrollment
     */
    private function notifySPEDTeachers($enrollmentId) {
        require_once __DIR__ . '/../Models/UserModel.php';
        $userModel = new UserModel();
        $spedTeachers = $userModel->getUsersByRole('sped_teacher');

        foreach ($spedTeachers as $teacher) {
            $this->notificationModel->create(
                $teacher['id'],
                'new_enrollment',
                'New Enrollment Submission',
                'A new enrollment application has been submitted and requires your review.',
                ['enrollment_id' => $enrollmentId]
            );
        }
    }

    /**
     * Show returning student lookup page
     */
    public function returningLookup() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/enrollment/returning_lookup.php';
    }

    /**
     * Search student for returning enrollment (AJAX)
     */
    public function searchStudent() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $searchType = $_GET['search_type'] ?? '';
        
        try {
            $students = [];
            
            // Get optional school year filter
            $schoolYear = $_GET['school_year'] ?? null;
            
            if ($searchType === 'lrn') {
                $lrn = $_GET['lrn'] ?? '';
                
                if (empty($lrn) || !preg_match('/^\d{12}$/', $lrn)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid LRN format']);
                    exit;
                }
                
                $student = $this->enrollmentModel->searchByLRN($lrn, $schoolYear);
                if ($student) {
                    $students = [$student];
                }
                
            } elseif ($searchType === 'name') {
                $lastName = $_GET['last_name'] ?? '';
                $firstName = $_GET['first_name'] ?? '';
                $middleName = $_GET['middle_name'] ?? '';
                $suffix = $_GET['suffix'] ?? '';
                
                if (empty($lastName) || empty($firstName)) {
                    echo json_encode(['success' => false, 'message' => 'Last name and first name are required']);
                    exit;
                }
                
                $students = $this->enrollmentModel->searchByName($lastName, $firstName, $middleName, $suffix, $schoolYear);
                
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid search type']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'students' => $students,
                'count' => count($students)
            ]);
            
        } catch (Exception $e) {
            error_log("Search student error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Search failed']);
        }
        
        exit;
    }
}
