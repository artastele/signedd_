<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 & 5
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP Document Controller (P2 & P3)

require_once __DIR__ . '/../Models/IEPP2DocumentModel.php';
require_once __DIR__ . '/../Models/IEPP3DocumentModel.php';
require_once __DIR__ . '/../Models/IEPMeetingModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class IEPDocumentController {
    private $p2Model;
    private $p3Model;
    private $meetingModel;
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
        
        $this->p2Model = new IEPP2DocumentModel();
        $this->p3Model = new IEPP3DocumentModel();
        $this->meetingModel = new IEPMeetingModel();
    }

    /**
     * Unified IEP Documents dashboard — replaces p2/review, p3/sign, approval
     */
    public function documents() {
        try {
            $db = Database::getInstance()->getConnection();
            $role = $this->userRole;

            // Allowed roles
            if (!in_array($role, ['sped_teacher', 'guidance', 'principal', 'parent', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }

            // --- P2 Documents ---
            // For SPED Teacher: all P2 docs they created
            // For Guidance/Principal: P2 docs pending their review
            if ($role === 'sped_teacher' || $role === 'admin') {
                $stmt = $db->prepare("
                    SELECT p2.*, sr.student_name, sr.lrn, im.meeting_date
                    FROM iep_p2_documents p2
                    JOIN student_records sr ON p2.student_id = sr.id
                    JOIN iep_meetings im ON p2.meeting_id = im.id
                    WHERE p2.created_by = :uid
                    ORDER BY p2.created_at DESC
                ");
                $stmt->execute(['uid' => $this->userId]);
            } else {
                $stmt = $db->prepare("
                    SELECT p2.*, sr.student_name, sr.lrn, im.meeting_date
                    FROM iep_p2_documents p2
                    JOIN student_records sr ON p2.student_id = sr.id
                    JOIN iep_meetings im ON p2.meeting_id = im.id
                    WHERE p2.status = 'pending_review'
                    ORDER BY p2.created_at DESC
                ");
                $stmt->execute();
            }
            $p2Documents = $stmt->fetchAll();

            // --- P3 Documents ---
            // For SPED Teacher: all P3 docs they created
            // For Parent/Guidance/Principal: P3 docs pending their signature
            if ($role === 'sped_teacher' || $role === 'admin') {
                $stmt = $db->prepare("
                    SELECT p3.*, sr.student_name, sr.lrn, im.meeting_date
                    FROM iep_p3_documents p3
                    JOIN student_records sr ON p3.student_id = sr.id
                    JOIN iep_meetings im ON p3.meeting_id = im.id
                    WHERE p3.created_by = :uid
                    ORDER BY p3.created_at DESC
                ");
                $stmt->execute(['uid' => $this->userId]);
            } elseif ($role === 'parent') {
                // Parent: only P3 docs for their children
                $stmt = $db->prepare("
                    SELECT p3.*, sr.student_name, sr.lrn, im.meeting_date
                    FROM iep_p3_documents p3
                    JOIN student_records sr ON p3.student_id = sr.id
                    JOIN iep_meetings im ON p3.meeting_id = im.id
                    JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                    WHERE p3.status = 'pending_signatures'
                    AND es.parent_id = :uid
                    AND p3.id NOT IN (
                        SELECT iep_p3_id FROM iep_p3_signatures WHERE signer_id = :uid2
                    )
                    ORDER BY p3.created_at DESC
                ");
                $stmt->execute(['uid' => $this->userId, 'uid2' => $this->userId]);
            } else {
                $stmt = $db->prepare("
                    SELECT p3.*, sr.student_name, sr.lrn, im.meeting_date
                    FROM iep_p3_documents p3
                    JOIN student_records sr ON p3.student_id = sr.id
                    JOIN iep_meetings im ON p3.meeting_id = im.id
                    WHERE p3.status = 'pending_signatures'
                    AND p3.id NOT IN (
                        SELECT iep_p3_id FROM iep_p3_signatures WHERE signer_id = :uid
                    )
                    ORDER BY p3.created_at DESC
                ");
                $stmt->execute(['uid' => $this->userId]);
            }
            $p3Documents = $stmt->fetchAll();

            // --- Approval Queue (Principal only) ---
            $approvalDocuments = [];
            if ($role === 'principal' || $role === 'admin') {
                $stmt = $db->prepare("
                    SELECT p3.*, sr.student_name, sr.lrn, u.name as created_by_name
                    FROM iep_p3_documents p3
                    JOIN student_records sr ON p3.student_id = sr.id
                    JOIN users u ON p3.created_by = u.id
                    WHERE p3.status = 'pending_signatures'
                    ORDER BY p3.created_at DESC
                ");
                $stmt->execute();
                $approvalDocuments = $stmt->fetchAll();
                foreach ($approvalDocuments as &$doc) {
                    $sigStmt = $db->prepare("
                        SELECT signer_role FROM iep_p3_signatures WHERE iep_p3_id = :id
                    ");
                    $sigStmt->execute(['id' => $doc['id']]);
                    $doc['signatures'] = $sigStmt->fetchAll(PDO::FETCH_COLUMN);
                }
            }

            // --- Meetings for SPED Teacher (to create P2/P3) ---
            $meetingsForCreation = [];
            if ($role === 'sped_teacher' || $role === 'admin') {
                $stmt = $db->prepare("
                    SELECT im.*, sr.student_name, sr.lrn
                    FROM iep_meetings im
                    JOIN student_records sr ON im.student_id = sr.id
                    WHERE im.scheduled_by = :uid
                    AND im.status = 'completed'
                    ORDER BY im.meeting_date DESC
                ");
                $stmt->execute(['uid' => $this->userId]);
                $meetingsForCreation = $stmt->fetchAll();
            }

            $this->logActivity('iep_documents.view', 'iep_p3_documents', null, 'Viewed unified IEP documents dashboard');

            $basePath = BASE_PATH;
            require __DIR__ . '/../Views/iep/documents.php';

        } catch (Exception $e) {
            error_log("IEPDocumentController->documents() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading IEP documents';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
    }

    /**
     * List P2 documents for review
     */
    public function listP2ForReview() {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get P2 documents pending review for this user
            // For Guidance: documents where they are assigned as guidance_id
            // For Principal: documents where they are assigned as principal_id
            // For SPED Teacher: documents they created
            $stmt = $db->prepare("
                SELECT DISTINCT p2.*, sr.student_name, sr.lrn, im.meeting_date
                FROM iep_p2_documents p2
                JOIN student_records sr ON p2.student_id = sr.id
                JOIN iep_meetings im ON p2.meeting_id = im.id
                WHERE p2.status = 'pending_review'
                AND (
                    im.guidance_id = ? 
                    OR im.principal_id = ?
                    OR p2.created_by = ?
                )
                ORDER BY p2.created_at DESC
            ");
            $stmt->execute([$this->userId, $this->userId, $this->userId]);
            $p2Documents = $stmt->fetchAll();
            
            $this->logActivity('iep_p2.review_list', 'iep_p2_documents', null, 'Viewed P2 review list');
            
            require __DIR__ . '/../Views/iep/p2_review_list.php';
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->listP2ForReview() ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo "Error loading P2 documents: " . htmlspecialchars($e->getMessage());
        }
    }

    /**
     * List P3 documents for signature
     */
    public function listP3ForSignature() {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get P3 documents pending signature for this user
            $stmt = $db->prepare("
                SELECT DISTINCT p3.*, sr.student_name, sr.lrn, im.meeting_date
                FROM iep_p3_documents p3
                JOIN student_records sr ON p3.student_id = sr.id
                JOIN iep_meetings im ON p3.meeting_id = im.id
                WHERE p3.status = 'pending_signatures'
                AND p3.id NOT IN (
                    SELECT iep_p3_id FROM iep_p3_signatures
                    WHERE signer_id = ?
                )
                ORDER BY p3.created_at DESC
            ");
            $stmt->execute([$this->userId]);
            $p3Documents = $stmt->fetchAll();
            
            $this->logActivity('iep_p3.sign_list', 'iep_p3_documents', null, 'Viewed P3 signature list');
            
            require __DIR__ . '/../Views/iep/p3_sign_list.php';
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->listP3ForSignature() ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo "Error loading P3 documents: " . htmlspecialchars($e->getMessage());
        }
    }

    /**
     * Create IEP P2 form
     */
    public function createP2($meetingId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                http_response_code(404);
                echo "Meeting not found";
                return;
            }
            
            // Check if P2 already exists
            $existingP2 = $this->p2Model->getByMeetingId($meetingId);
            
            $this->logActivity('iep_p2.form_opened', 'iep_p2_documents', null, "Opened P2 form for meeting: $meetingId");
            
            require __DIR__ . '/../Views/iep/p2_form.php';
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->createP2() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading P2 form";
        }
    }

    /**
     * Submit IEP P2
     */
    public function submitP2() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            $meetingId = $_POST['meeting_id'] ?? null;
            
            if (!$meetingId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing meeting ID']);
                return;
            }
            
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Meeting not found']);
                return;
            }
            
            // Collect P2 form data
            $iepData = [
                'domains' => [],
                'remarks' => $_POST['remarks'] ?? '',
                'submitted_at' => date('Y-m-d H:i:s')
            ];
            
            // Collect domain data (6 domains)
            $domains = ['perceptuo_cognitive', 'psychosocial', 'psychomotor', 'socio_emotional', 'daily_living_skills', 'communication_language'];
            
            foreach ($domains as $domain) {
                $iepData['domains'][$domain] = [
                    'skills_description' => $_POST["{$domain}_description"] ?? '',
                    'mastered' => $_POST["{$domain}_mastered"] ?? 'no',
                    'q1_recommendation' => $_POST["{$domain}_q1_rec"] ?? '',
                    'q2_recommendation' => $_POST["{$domain}_q2_rec"] ?? '',
                    'performance_level' => $_POST["{$domain}_performance"] ?? ''
                ];
            }
            
            // Create or update P2 document
            $existingP2 = $this->p2Model->getByMeetingId($meetingId);
            
            if ($existingP2) {
                $p2Id = $existingP2['id'];
            } else {
                $p2Id = $this->p2Model->create(
                    $meetingId,
                    $meeting['student_id'],
                    $iepData,
                    $this->userId
                );
            }
            
            $this->logActivity('iep_p2.submitted', 'iep_p2_documents', $p2Id, "Submitted P2 for meeting: $meetingId");
            
            echo json_encode([
                'success' => true,
                'message' => 'IEP P2 submitted successfully',
                'p2_id' => $p2Id
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->submitP2() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error submitting P2']);
        }
    }

    /**
     * Upload P2 PDF
     */
    public function uploadP2() {
        try {
            $p2Id = $_POST['p2_id'] ?? null;
            
            if (!$p2Id || !isset($_FILES['pdf_file'])) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                return;
            }
            
            $file = $_FILES['pdf_file'];
            
            // Validate file
            if ($file['type'] !== 'application/pdf') {
                echo json_encode(['success' => false, 'message' => 'Only PDF files allowed']);
                return;
            }
            
            if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
                return;
            }
            
            // Save file
            $uploadDir = __DIR__ . '/../../public/uploads/iep_p2/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'p2_' . $p2Id . '_' . time() . '.pdf';
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
                return;
            }
            
            // Update database
            $this->p2Model->uploadPDF($p2Id, '/uploads/iep_p2/' . $fileName);
            
            $this->logActivity('iep_p2.pdf_uploaded', 'iep_p2_documents', $p2Id, 'PDF uploaded');
            
            echo json_encode([
                'success' => true,
                'message' => 'PDF uploaded successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->uploadP2() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error uploading PDF']);
        }
    }

    /**
     * Send P2 for review
     */
    public function sendP2ForReview() {
        try {
            $p2Id = $_POST['p2_id'] ?? null;
            $reviewerId = $_POST['reviewer_id'] ?? null;
            
            if (!$p2Id || !$reviewerId) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                return;
            }
            
            $this->p2Model->sendForReview($p2Id, $reviewerId);
            
            // Send email notification
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT email, name FROM users WHERE id = :id");
            $stmt->execute(['id' => $reviewerId]);
            $reviewer = $stmt->fetch();
            
            if ($reviewer) {
                $subject = "IEP P2 Assessment - Review Required";
                $body = "
                <h2>IEP P2 Assessment Review</h2>
                <p>Dear {$reviewer['name']},</p>
                <p>An IEP P2 assessment has been sent for your review and signature.</p>
                
                <p><a href='" . BASE_PATH . "/iep/p2/$p2Id/review' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Review Assessment</a></p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                MailHelper::send($reviewer['email'], $subject, $body);
            }
            
            $this->logActivity('iep_p2.sent_for_review', 'iep_p2_documents', $p2Id, "Sent for review to user: $reviewerId");
            
            echo json_encode([
                'success' => true,
                'message' => 'Sent for review successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->sendP2ForReview() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error sending for review']);
        }
    }

    /**
     * Review and sign P2
     */
    public function reviewP2($p2Id) {
        try {
            $p2 = $this->p2Model->findById($p2Id);
            if (!$p2) {
                http_response_code(404);
                echo "P2 document not found";
                return;
            }
            
            // Get user role for signature
            $userRole = $this->userRole;
            if ($userRole === 'user') {
                $userRole = 'parent';
            }
            
            $this->logActivity('iep_p2.review_opened', 'iep_p2_documents', $p2Id, 'Opened P2 for review');
            
            require __DIR__ . '/../Views/iep/p2_review.php';
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->reviewP2() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading review page";
        }
    }

    /**
     * Submit P2 review and signature
     */
    public function submitP2Review() {
        try {
            $p2Id = $_POST['p2_id'] ?? null;
            $feedback = $_POST['feedback'] ?? '';
            $signatureData = $_POST['signature_data'] ?? null;
            
            if (!$p2Id || !$signatureData) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }
            
            $userRole = $this->userRole;
            if ($userRole === 'user') {
                $userRole = 'parent';
            }
            
            $this->p2Model->addReview($p2Id, $this->userId, $userRole, $feedback, $signatureData);
            
            $this->logActivity('iep_p2.reviewed_signed', 'iep_p2_documents', $p2Id, "Reviewed and signed by: $userRole");
            
            echo json_encode([
                'success' => true,
                'message' => 'Review submitted successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->submitP2Review() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error submitting review']);
        }
    }

    /**
     * Create IEP P3 form
     */
    public function createP3($meetingId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                http_response_code(404);
                echo "Meeting not found";
                return;
            }
            
            // Check if P3 already exists
            $existingP3 = $this->p3Model->getByMeetingId($meetingId);
            
            $this->logActivity('iep_p3.form_opened', 'iep_p3_documents', null, "Opened P3 form for meeting: $meetingId");
            
            require __DIR__ . '/../Views/iep/p3_form.php';
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->createP3() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading P3 form";
        }
    }

    /**
     * Submit IEP P3
     */
    public function submitP3() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            $meetingId = $_POST['meeting_id'] ?? null;
            
            if (!$meetingId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing meeting ID']);
                return;
            }
            
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Meeting not found']);
                return;
            }
            
            // Collect P3 form data
            $iepData = [
                'learner_name' => $meeting['student_name'],
                'lrn' => $meeting['lrn'],
                'age' => $_POST['age'] ?? '',
                'grade_level' => $_POST['grade_level'] ?? '',
                'section' => $_POST['section'] ?? '',
                'teacher_name' => $_POST['teacher_name'] ?? '',
                'school' => $_POST['school'] ?? '',
                'school_year' => $_POST['school_year'] ?? '',
                'developmental_domain' => $_POST['developmental_domain'] ?? '',
                'priority_needs' => $_POST['priority_needs'] ?? [],
                'terminal_objectives' => $_POST['terminal_objectives'] ?? [],
                'step_objectives' => [],
                'date_re_evaluation' => $_POST['date_re_evaluation'] ?? '',
                'submitted_at' => date('Y-m-d H:i:s')
            ];
            
            // Collect step objectives (10 steps)
            for ($i = 1; $i <= 10; $i++) {
                $iepData['step_objectives'][$i] = [
                    'step_objective' => $_POST["step_{$i}_objective"] ?? '',
                    'activities' => $_POST["step_{$i}_activities"] ?? '',
                    'materials' => $_POST["step_{$i}_materials"] ?? '',
                    'duration' => $_POST["step_{$i}_duration"] ?? '',
                    'evaluation' => $_POST["step_{$i}_evaluation"] ?? '',
                    'observation' => $_POST["step_{$i}_observation"] ?? ''
                ];
            }
            
            // Create or update P3 document
            $existingP3 = $this->p3Model->getByMeetingId($meetingId);
            
            if ($existingP3) {
                $p3Id = $existingP3['id'];
            } else {
                $p3Id = $this->p3Model->create(
                    $meetingId,
                    $meeting['student_id'],
                    $iepData,
                    $this->userId
                );
            }
            
            $this->logActivity('iep_p3.submitted', 'iep_p3_documents', $p3Id, "Submitted P3 for meeting: $meetingId");
            
            echo json_encode([
                'success' => true,
                'message' => 'IEP P3 submitted successfully',
                'p3_id' => $p3Id
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->submitP3() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error submitting P3']);
        }
    }

    /**
     * Upload P3 PDF
     */
    public function uploadP3() {
        try {
            $p3Id = $_POST['p3_id'] ?? null;
            
            if (!$p3Id || !isset($_FILES['pdf_file'])) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                return;
            }
            
            $file = $_FILES['pdf_file'];
            
            // Validate file
            if ($file['type'] !== 'application/pdf') {
                echo json_encode(['success' => false, 'message' => 'Only PDF files allowed']);
                return;
            }
            
            if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
                return;
            }
            
            // Save file
            $uploadDir = __DIR__ . '/../../public/uploads/iep_p3/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'p3_' . $p3Id . '_' . time() . '.pdf';
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
                return;
            }
            
            // Update database
            $this->p3Model->uploadPDF($p3Id, '/uploads/iep_p3/' . $fileName);
            
            $this->logActivity('iep_p3.pdf_uploaded', 'iep_p3_documents', $p3Id, 'PDF uploaded');
            
            echo json_encode([
                'success' => true,
                'message' => 'PDF uploaded successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->uploadP3() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error uploading PDF']);
        }
    }

    /**
     * Send P3 for signature
     */
    public function sendP3ForSignature() {
        try {
            $p3Id = $_POST['p3_id'] ?? null;
            $signerId = $_POST['signer_id'] ?? null;
            $signerRole = $_POST['signer_role'] ?? null;
            
            if (!$p3Id || !$signerId || !$signerRole) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                return;
            }
            
            $this->p3Model->sendForSignature($p3Id, $signerId, $signerRole);
            
            // Send email notification
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT email, name FROM users WHERE id = :id");
            $stmt->execute(['id' => $signerId]);
            $signer = $stmt->fetch();
            
            if ($signer) {
                $subject = "IEP Document - Signature Required";
                $body = "
                <h2>IEP Document Signature Required</h2>
                <p>Dear {$signer['name']},</p>
                <p>An IEP document has been sent for your signature.</p>
                
                <p><a href='" . BASE_PATH . "/iep/p3/$p3Id/sign' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Sign Document</a></p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                MailHelper::send($signer['email'], $subject, $body);
            }
            
            $this->logActivity('iep_p3.sent_for_signature', 'iep_p3_documents', $p3Id, "Sent for signature to: $signerRole");
            
            echo json_encode([
                'success' => true,
                'message' => 'Sent for signature successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->sendP3ForSignature() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error sending for signature']);
        }
    }

    /**
     * Sign P3 document
     */
    public function signP3($p3Id) {
        try {
            $p3 = $this->p3Model->findById($p3Id);
            if (!$p3) {
                http_response_code(404);
                echo "IEP document not found";
                return;
            }

            // All 4 required roles can view and sign
            $allowedRoles = ['sped_teacher', 'parent', 'guidance', 'principal', 'admin'];
            if (!in_array($this->userRole, $allowedRoles)) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/iep/documents');
                exit;
            }

            // Parent ownership check
            if ($this->userRole === 'parent') {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT COUNT(*) FROM student_records sr
                    JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                    WHERE sr.id = :student_id AND es.parent_id = :parent_id
                ");
                $stmt->execute(['student_id' => $p3['student_id'], 'parent_id' => $this->userId]);
                if ($stmt->fetchColumn() == 0) {
                    http_response_code(403);
                    $_SESSION['error'] = 'Access denied';
                    header('Location: ' . BASE_PATH . '/iep/documents');
                    exit;
                }
            }

            // Get signature status as keyed array
            $rawStatus = $this->p3Model->getSignatureStatus($p3Id);
            $signatureStatus = $rawStatus; // passed as-is to view (view builds sigMap)

            $this->logActivity('iep_p3.sign_opened', 'iep_p3_documents', $p3Id, 'Opened IEP for signature');

            $basePath = BASE_PATH;
            $p3Data = $p3;
            require __DIR__ . '/../Views/iep/p3_sign.php';

        } catch (Exception $e) {
            error_log("IEPDocumentController->signP3() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading IEP signature page";
        }
    }

    /**
     * Submit P3 signature
     */
    public function submitP3Signature() {
        header('Content-Type: application/json');
        try {
            $p3Id         = $_POST['p3_id'] ?? null;
            $signatureData = $_POST['signature_data'] ?? null;
            $remarks      = $_POST['remarks'] ?? null;
            $signerRole   = $_POST['signer_role'] ?? $this->userRole;

            if (!$p3Id || !$signatureData) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            // Validate signer role is one of the 4 required
            $allowed = ['sped_teacher', 'parent', 'guidance', 'principal'];
            if (!in_array($signerRole, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Your role is not a required signatory for this IEP']);
                exit;
            }

            $this->p3Model->addSignature($p3Id, $this->userId, $signerRole, $signatureData, $remarks);

            // Check if now fully signed — notify SPED Teacher
            $p3 = $this->p3Model->findById($p3Id);
            if (($p3['status'] ?? '') === 'signed_approved') {
                require_once __DIR__ . '/../Models/NotificationModel.php';
                $notifModel = new NotificationModel();
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id FROM users WHERE role = 'sped_teacher' AND status = 'active'");
                $stmt->execute();
                foreach ($stmt->fetchAll() as $teacher) {
                    $notifModel->create(
                        $teacher['id'],
                        'iep_fully_signed',
                        'IEP Fully Signed',
                        "The IEP for {$p3['student_name']} has been signed by all required parties.",
                        ['p3_id' => $p3Id]
                    );
                }
            }

            $this->logActivity('iep_p3.signed', 'iep_p3_documents', $p3Id, "Signed IEP as: $signerRole");

            echo json_encode(['success' => true, 'message' => 'Signature submitted successfully']);

        } catch (Exception $e) {
            error_log("IEPDocumentController->submitP3Signature() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error submitting signature']);
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

    /**
     * Get IEP approval queue for Principal
     */
    public function approvalQueue() {
        try {
            // Check permission - Principal only
            if ($this->userRole !== 'principal' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get all P3 documents pending principal signature
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT p3.*, sr.lrn, sr.student_name, u.name as created_by_name
                FROM iep_p3_documents p3
                JOIN student_records sr ON p3.student_id = sr.id
                JOIN users u ON p3.created_by = u.id
                WHERE p3.status = 'pending_signatures'
                ORDER BY p3.created_at DESC
            ");
            $stmt->execute();
            $documents = $stmt->fetchAll();
            
            // Get signature status for each document
            foreach ($documents as &$doc) {
                $sigStmt = $db->prepare("
                    SELECT signer_role, COUNT(*) as count
                    FROM iep_p3_signatures
                    WHERE iep_p3_id = :p3_id
                    GROUP BY signer_role
                ");
                $sigStmt->execute(['p3_id' => $doc['id']]);
                $doc['signatures'] = $sigStmt->fetchAll();
            }
            
            $this->logActivity('iep_approval.queue_viewed', 'iep_p3_documents', null, 'Viewed IEP approval queue');
            
            require __DIR__ . '/../Views/iep/approval_queue.php';
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->approvalQueue() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading approval queue";
        }
    }

    /**
     * Approve IEP P3 document (Principal final approval)
     */
    public function approve($documentId) {
        try {
            // Check permission - Principal only
            if ($this->userRole !== 'principal' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            $db = Database::getInstance()->getConnection();
            
            // Get document
            $stmt = $db->prepare("SELECT * FROM iep_p3_documents WHERE id = :id");
            $stmt->execute(['id' => $documentId]);
            $document = $stmt->fetch();
            
            if (!$document) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Document not found']);
                return;
            }
            
            // Update document status to signed_approved
            $stmt = $db->prepare("
                UPDATE iep_p3_documents
                SET status = 'signed_approved'
                WHERE id = :id
            ");
            $stmt->execute(['id' => $documentId]);
            
            // Log activity
            $this->logActivity(
                'iep_p3.approved',
                'iep_p3_documents',
                $documentId,
                'Principal approved IEP P3 document'
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'IEP document approved successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPDocumentController->approve() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error approving document']);
        }
    }
}
