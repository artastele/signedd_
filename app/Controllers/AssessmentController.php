<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-08
// Part of: SPED LMS — Assessment Controller
// Changes: Fixed file_path saved as 'uploads/assessments/' (was 'assessments/')

require_once __DIR__ . '/../Models/AssessmentModel.php';
require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class AssessmentController {
    private $assessmentModel;
    private $studentModel;
    private $enrollmentModel;
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
        
        $this->assessmentModel = new AssessmentModel();
        $this->studentModel = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Assessment dashboard - SPED Teacher reviews pending assessments
     */
    public function index() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get all assessments with student info
            $allAssessments = $this->assessmentModel->getAllWithStudentInfo();
            
            // Separate by status
            $finalized = array_filter($allAssessments, fn($a) => $a['status'] === 'finalized');
            $drafts = array_filter($allAssessments, fn($a) => $a['status'] === 'draft');
            
            // Log activity
            $this->logActivity('assessment.list', 'assessment_records', null, 'Viewed assessment dashboard');
            
            // Load view
            require __DIR__ . '/../Views/assessment/index.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->index() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessments";
        }
    }

    /**
     * Assessment form - SPED Teacher conducts assessment (Process 3 Part I)
     */
    public function conduct($studentId = null) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied. SPED teachers only.';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }
            
            // Get verified students for selector
            $students = $this->studentModel->getVerifiedStudents();
            
            // If student ID provided, get their details for auto-fill
            $studentData = null;
            if ($studentId) {
                $studentData = $this->studentModel->getFullDetails($studentId);
                
                if (!$studentData) {
                    $_SESSION['error'] = 'Student not found';
                    header('Location: ' . BASE_PATH . '/assessment');
                    exit;
                }
            }
            
            // Get existing draft if any
            $draft = null;
            if ($studentId) {
                $draft = $this->assessmentModel->getLatest($studentId);
                if ($draft && $draft['status'] === 'draft') {
                    // Load draft data
                    $studentData['draft'] = $draft;
                }
            }
            
            // Pass basePath to view
            $basePath = BASE_PATH;
            
            // Log activity
            $this->logActivity('assessment.form', 'assessment_records', null, 
                $studentId ? "Opened assessment form for student: $studentId" : "Opened assessment form");
            
            // Load view
            require __DIR__ . '/../Views/assessment/conduct.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->conduct() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading assessment form';
            header('Location: ' . BASE_PATH . '/assessment');
            exit;
        }
    }

    /**
     * AJAX endpoint - Get student data for auto-fill
     */
    public function getStudentData($studentId) {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Get student details
            $studentData = $this->studentModel->getFullDetails($studentId);
            
            if (!$studentData) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Student not found']);
                exit;
            }
            
            // Get existing draft if any
            $draft = $this->assessmentModel->getLatest($studentId);
            if ($draft && $draft['status'] === 'draft') {
                $studentData['draft'] = $draft;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $studentData
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->getStudentData() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error loading student data']);
        }
    }

    /**
     * Submit assessment - SPED Teacher submits complete Part I (Section A + B)
     */
    public function submit() {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                $_SESSION['error'] = 'Access denied. SPED teachers only.';
                header('Location: ' . BASE_PATH . '/assessment');
                exit;
            }
            
            // Get POST data
            $studentId = $_POST['student_id'] ?? null;
            
            if (!$studentId) {
                $_SESSION['error'] = 'Student ID required';
                header('Location: ' . BASE_PATH . '/assessment');
                exit;
            }
            
            // Collect Section A data
            $sectionAData = [
                'last_name' => $_POST['last_name'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? '',
                'extension_name' => $_POST['extension_name'] ?? '',
                'birth_date' => $_POST['birth_date'] ?? '',
                'age' => $_POST['age'] ?? '',
                'sex' => $_POST['sex'] ?? '',
                'religion' => $_POST['religion'] ?? '',
                'home_address' => $_POST['home_address'] ?? '',
                'lrn' => $_POST['lrn'] ?? '',
                'school' => $_POST['school'] ?? '',
                'school_year' => $_POST['school_year'] ?? '',
                'adviser_name' => $_POST['adviser_name'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'father_contact' => $_POST['father_contact'] ?? '',
                'father_occupation' => $_POST['father_occupation'] ?? '',
                'mother_name' => $_POST['mother_name'] ?? '',
                'mother_contact' => $_POST['mother_contact'] ?? '',
                'mother_occupation' => $_POST['mother_occupation'] ?? '',
                'guardian_name' => $_POST['guardian_name'] ?? '',
                'guardian_contact' => $_POST['guardian_contact'] ?? '',
                'guardian_occupation' => $_POST['guardian_occupation'] ?? '',
                'previous_school' => $_POST['previous_school'] ?? '',
                'previous_grade_level' => $_POST['previous_grade_level'] ?? '',
                'previous_school_year' => $_POST['previous_school_year'] ?? '',
                'with_iep' => $_POST['with_iep'] ?? 'no',
                'with_support_services' => $_POST['with_support_services'] ?? 'no',
                'support_services_detail' => $_POST['support_services_detail'] ?? ''
            ];
            
            // Collect services checklist
            $services = $_POST['services'] ?? [];
            if (in_array('Others', $services) && !empty($_POST['services_others_specify'])) {
                $services[] = 'Others: ' . $_POST['services_others_specify'];
            }
            
            $screening = $_POST['screening'] ?? [];
            
            // Get "With Support Services?" value
            $withSupportServices = $_POST['with_support_services'] ?? 'no';
            
            // Validate: at least one service must be checked ONLY if "With Support Services?" is "yes"
            if ($withSupportServices === 'yes' && empty($services)) {
                $_SESSION['error'] = 'Please check at least one service';
                header('Location: ' . BASE_PATH . '/assessment/conduct/' . $studentId);
                exit;
            }
            
            // Collect Section B data (MDT services) - only if services exist
            $mdtServices = $_POST['mdt_services'] ?? [];
            $sectionBData = [];
            
            // Only process MDT data if "With Support Services?" is "yes"
            if ($withSupportServices === 'yes') {
                foreach ($mdtServices as $serviceName) {
                    $serviceId = $this->sanitizeId($serviceName);
                    
                    // Get MDT members
                    $memberNames = $_POST["mdt_member_name_{$serviceId}"] ?? [];
                    $memberDesignations = $_POST["mdt_member_designation_{$serviceId}"] ?? [];
                    
                    $members = [];
                    for ($i = 0; $i < count($memberNames); $i++) {
                        if (!empty($memberNames[$i])) {
                            $members[] = [
                                'name' => $memberNames[$i],
                                'designation' => $memberDesignations[$i] ?? ''
                            ];
                        }
                    }
                    
                    // Get assessment date
                    $assessmentDate = $_POST["mdt_date_{$serviceId}"] ?? null;
                    
                    // Validate: each service must have at least one member and a date
                    if (empty($members)) {
                        $_SESSION['error'] = "Please add at least one MDT member for: $serviceName";
                        header('Location: ' . BASE_PATH . '/assessment/conduct/' . $studentId);
                        exit;
                    }
                    
                    if (empty($assessmentDate)) {
                        $_SESSION['error'] = "Please select assessment date for: $serviceName";
                        header('Location: ' . BASE_PATH . '/assessment/conduct/' . $studentId);
                        exit;
                    }
                    
                    $sectionBData[$serviceName] = [
                        'members' => $members,
                        'date' => $assessmentDate
                    ];
                }
            }
            
            // Create or update assessment record
            $existingDraft = $this->assessmentModel->getLatest($studentId);
            
            if ($existingDraft && $existingDraft['status'] === 'draft') {
                // Finalize existing draft
                $assessmentId = $this->assessmentModel->finalizeDraft(
                    $existingDraft['id'],
                    $sectionAData,
                    $services,
                    $screening,
                    $sectionBData
                );
            } else {
                // Create new finalized assessment
                $assessmentId = $this->assessmentModel->createFinalized(
                    $studentId,
                    $this->userId,
                    $sectionAData,
                    $services,
                    $screening,
                    $sectionBData
                );
            }
            
            // Handle file uploads for each service (multiple files per service)
            $uploadErrors = [];
            foreach ($mdtServices as $serviceName) {
                $serviceId = $this->sanitizeId($serviceName);
                $fileKey = "mdt_file_{$serviceId}";
                
                // Check if files were uploaded for this service
                if (isset($_FILES[$fileKey]) && is_array($_FILES[$fileKey]['name'])) {
                    $fileCount = count($_FILES[$fileKey]['name']);
                    
                    for ($i = 0; $i < $fileCount; $i++) {
                        // Skip if no file or error
                        if ($_FILES[$fileKey]['error'][$i] !== UPLOAD_ERR_OK) {
                            continue;
                        }
                        
                        // Create temporary file array for single file
                        $singleFile = [
                            'name' => $_FILES[$fileKey]['name'][$i],
                            'type' => $_FILES[$fileKey]['type'][$i],
                            'tmp_name' => $_FILES[$fileKey]['tmp_name'][$i],
                            'error' => $_FILES[$fileKey]['error'][$i],
                            'size' => $_FILES[$fileKey]['size'][$i]
                        ];
                        
                        $uploadResult = $this->handleServiceFileUpload(
                            $singleFile,
                            $assessmentId,
                            $serviceName
                        );
                        
                        if (!$uploadResult['success']) {
                            $uploadErrors[] = $serviceName . ' - ' . $singleFile['name'] . ': ' . $uploadResult['message'];
                        }
                    }
                }
            }
            
            // Log activity
            $this->logActivity('assessment.submit', 'assessment_records', $assessmentId, 
                "Submitted Part I assessment for student: $studentId");
            
            // Set success message
            if (!empty($uploadErrors)) {
                $_SESSION['warning'] = 'Assessment submitted but some files failed to upload: ' . implode(', ', $uploadErrors);
            } else {
                $_SESSION['success'] = 'Assessment submitted successfully';
            }
            
            header('Location: ' . BASE_PATH . '/assessment');
            exit;
            
        } catch (Exception $e) {
            error_log("AssessmentController->submit() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error submitting assessment: ' . $e->getMessage();
            header('Location: ' . BASE_PATH . '/assessment/conduct/' . ($studentId ?? ''));
            exit;
        }
    }

    /**
     * Handle file upload for a service
     */
    private function handleServiceFileUpload($file, $assessmentId, $serviceName) {
        try {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Only JPG, PNG, and PDF allowed.'
                ];
            }
            
            // Validate file size (10MB)
            $maxSize = 10 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                return [
                    'success' => false,
                    'message' => 'File too large. Maximum size is 10MB.'
                ];
            }
            
            // Create upload directory if not exists
            // Files must be inside public/uploads/ to be web-accessible
            $uploadDir = __DIR__ . '/../../public/uploads/assessments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'assessment_' . $assessmentId . '_' . $this->sanitizeId($serviceName) . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to save file'
                ];
            }
            
            // Save to database
            $this->assessmentModel->saveServiceDocument(
                $assessmentId,
                $serviceName,
                'uploads/assessments/' . $filename,
                $fileType,
                $file['name']
            );
            
            return [
                'success' => true,
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sanitize string for use as ID
     */
    private function sanitizeId($str) {
        return preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($str));
    }

    /**
     * Assessment form - Parent fills education history and assessment info (OLD - DEPRECATED)
     */
    public function conductOld($studentId) {
        try {
            // Get student with enrollment data
            $student = $this->studentModel->getWithDetails($studentId);
            
            if (!$student) {
                http_response_code(404);
                echo "Student not found";
                return;
            }
            
            // Check if parent owns this student (from enrollment)
            if ($this->userRole === 'parent' && $student['parent_id'] !== $this->userId) {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get existing assessment if any
            $existingAssessment = $this->assessmentModel->getLatest($studentId);
            
            // Log activity
            $this->logActivity('assessment.form', 'assessment_records', null, "Opened assessment form for student: $studentId");
            
            // Load view
            require __DIR__ . '/../Views/assessment/conduct.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->conduct() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessment form";
        }
    }

    /**
     * AJAX endpoint - Delete service document
     */
    public function deleteServiceDocument($documentId) {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Delete document
            $result = $this->assessmentModel->deleteServiceDocument($documentId);
            
            if ($result) {
                // Log activity
                $this->logActivity('assessment.delete_document', 'assessment_documents', $documentId, 
                    "Deleted service document");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Document not found']);
            }
            
        } catch (Exception $e) {
            error_log("AssessmentController->deleteServiceDocument() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting document']);
        }
    }

    /**
     * Save draft - SPED Teacher saves Section A as draft
     */
    public function saveDraft() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Get POST data
            $studentId = $_POST['student_id'] ?? null;
            
            if (!$studentId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID required']);
                exit;
            }
            
            // Collect Section A data
            $sectionAData = [
                'last_name' => $_POST['last_name'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? '',
                'extension_name' => $_POST['extension_name'] ?? '',
                'birth_date' => $_POST['birth_date'] ?? '',
                'age' => $_POST['age'] ?? '',
                'sex' => $_POST['sex'] ?? '',
                'religion' => $_POST['religion'] ?? '',
                'home_address' => $_POST['home_address'] ?? '',
                'lrn' => $_POST['lrn'] ?? '',
                'school' => $_POST['school'] ?? '',
                'school_year' => $_POST['school_year'] ?? '',
                'adviser_name' => $_POST['adviser_name'] ?? '',
                'father_name' => $_POST['father_name'] ?? '',
                'father_contact' => $_POST['father_contact'] ?? '',
                'father_occupation' => $_POST['father_occupation'] ?? '',
                'mother_name' => $_POST['mother_name'] ?? '',
                'mother_contact' => $_POST['mother_contact'] ?? '',
                'mother_occupation' => $_POST['mother_occupation'] ?? '',
                'guardian_name' => $_POST['guardian_name'] ?? '',
                'guardian_contact' => $_POST['guardian_contact'] ?? '',
                'guardian_occupation' => $_POST['guardian_occupation'] ?? '',
                'previous_school' => $_POST['previous_school'] ?? '',
                'previous_grade_level' => $_POST['previous_grade_level'] ?? '',
                'previous_school_year' => $_POST['previous_school_year'] ?? '',
                'with_iep' => $_POST['with_iep'] ?? 'no',
                'with_support_services' => $_POST['with_support_services'] ?? 'no',
                'support_services_detail' => $_POST['support_services_detail'] ?? ''
            ];
            
            // Collect services checklist
            $services = $_POST['services'] ?? [];
            if (in_array('Others', $services) && !empty($_POST['services_others_specify'])) {
                $services[] = 'Others: ' . $_POST['services_others_specify'];
            }
            
            $screening = $_POST['screening'] ?? [];
            
            // Check if draft already exists
            $existingDraft = $this->assessmentModel->getLatest($studentId);
            
            if ($existingDraft && $existingDraft['status'] === 'draft') {
                // Update existing draft
                $assessmentId = $this->assessmentModel->updateDraft(
                    $existingDraft['id'],
                    $sectionAData,
                    $services,
                    $screening
                );
            } else {
                // Create new draft
                $assessmentId = $this->assessmentModel->createDraft(
                    $studentId,
                    $this->userId,
                    $sectionAData,
                    $services,
                    $screening
                );
            }
            
            // Log activity
            $this->logActivity('assessment.draft', 'assessment_records', $assessmentId, 
                "Saved draft for student: $studentId");
            
            echo json_encode([
                'success' => true,
                'message' => 'Draft saved successfully',
                'assessment_id' => $assessmentId
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->saveDraft() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving draft']);
        }
    }

    /**
     * View assessment - SPED Teacher reviews submitted assessment
     */
    public function view($assessmentId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo "Access Denied";
                exit;
            }
            
            // Get assessment
            $assessment = $this->assessmentModel->findById($assessmentId);
            
            if (!$assessment) {
                http_response_code(404);
                echo "Assessment not found";
                return;
            }
            
            // Get MDT services with documents
            require_once __DIR__ . '/../Models/AssessmentServiceModel.php';
            $serviceModel = new AssessmentServiceModel();
            $assessment['mdt_services'] = $serviceModel->getByAssessmentId($assessmentId);
            
            // Decode education history if it's JSON
            if (!empty($assessment['education_history']) && is_string($assessment['education_history'])) {
                $assessment['education_history'] = json_decode($assessment['education_history'], true);
            }
            
            // Log activity
            $this->logActivity('assessment.view', 'assessment_records', $assessmentId, 'Viewed assessment for review');
            
            // Load view
            require __DIR__ . '/../Views/assessment/view.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->view() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo "Error loading assessment";
        }
    }

    /**
     * Approve assessment
     */
    public function approve($assessmentId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Get assessment
            $assessment = $this->assessmentModel->findById($assessmentId);
            
            if (!$assessment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                return;
            }
            
            // Approve assessment
            $this->assessmentModel->approve($assessmentId, $this->userId);
            
            // Log activity
            $this->logActivity('assessment.approve', 'assessment_records', $assessmentId, 'Approved assessment');
            
            // Send notification to parent
            $this->notifyParentApproved($assessment);
            
            echo json_encode([
                'success' => true,
                'message' => 'Assessment approved successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->approve() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error approving assessment']);
        }
    }

    /**
     * Reject assessment
     */
    public function reject($assessmentId) {
        try {
            // Check permission
            if ($this->userRole !== 'sped_teacher' && $this->userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access Denied']);
                exit;
            }
            
            // Get rejection reason
            $reason = $_POST['reason'] ?? 'No reason provided';
            
            // Get assessment
            $assessment = $this->assessmentModel->findById($assessmentId);
            
            if (!$assessment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                return;
            }
            
            // Reject assessment
            $this->assessmentModel->reject($assessmentId, $this->userId, $reason);
            
            // Log activity
            $this->logActivity('assessment.reject', 'assessment_records', $assessmentId, "Rejected assessment: $reason");
            
            // Send notification to parent
            $this->notifyParentRejected($assessment, $reason);
            
            echo json_encode([
                'success' => true,
                'message' => 'Assessment rejected successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("AssessmentController->reject() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error rejecting assessment']);
        }
    }

    /**
     * View assessment history
     */
    public function history($studentId) {
        try {
            // Get student
            $student = $this->studentModel->findById($studentId);
            
            if (!$student) {
                http_response_code(404);
                $_SESSION['error'] = 'Student not found';
                header('Location: ' . BASE_PATH . '/students');
                exit;
            }
            
            // Check permission
            if ($this->userRole === 'parent') {
                // Parent can only view their own child's assessments
                if ($student['parent_id'] !== $this->userId) {
                    http_response_code(403);
                    $_SESSION['error'] = 'Access denied';
                    header('Location: ' . BASE_PATH . '/dashboard');
                    exit;
                }
            } elseif (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }
            
            // Get assessment history with full details
            $history = $this->assessmentModel->getHistoryWithDetails($studentId);
            
            // Pass basePath to view
            $basePath = BASE_PATH;
            
            // Log activity
            $this->logActivity('assessment.history', 'assessment_records', null, 
                "Viewed assessment history for student: $studentId");
            
            // Load view
            require __DIR__ . '/../Views/assessment/history.php';
            
        } catch (Exception $e) {
            error_log("AssessmentController->history() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading assessment history';
            header('Location: ' . BASE_PATH . '/students');
            exit;
        }
    }

    /**
     * Notify SPED teacher of new assessment submission
     */
    private function notifySpedTeacherNewAssessment($studentId) {
        try {
            $student = $this->studentModel->getWithDetails($studentId);
            
            // Get SPED teachers (for now, send to admin)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id, email, name FROM users
                WHERE role IN ('sped_teacher', 'admin')
                LIMIT 5
            ");
            $stmt->execute();
            $teachers = $stmt->fetchAll();
            
            foreach ($teachers as $teacher) {
                $subject = "New Assessment Submission - {$student['student_name']}";
                
                $body = "
                <h2>New Assessment Submission</h2>
                <p>A new assessment has been submitted for review.</p>
                
                <h3>Student Information</h3>
                <p><strong>Name:</strong> {$student['student_name']}</p>
                <p><strong>LRN:</strong> {$student['lrn']}</p>
                
                <p><a href='" . BASE_PATH . "/assessment/view/{$student['id']}' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Review Assessment</a></p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                
                MailHelper::send($teacher['email'], $subject, $body);
            }
            
        } catch (Exception $e) {
            error_log("Failed to notify SPED teacher: " . $e->getMessage());
        }
    }

    /**
     * Notify parent of assessment approval
     */
    private function notifyParentApproved($assessment) {
        try {
            $subject = "Assessment Approved - {$assessment['student_name']}";
            
            $body = "
            <h2>Assessment Approved</h2>
            <p>Dear {$assessment['parent_name']},</p>
            <p>Your child's educational assessment has been reviewed and approved by the SPED Teacher.</p>
            
            <h3>Student Information</h3>
            <p><strong>Name:</strong> {$assessment['student_name']}</p>
            <p><strong>LRN:</strong> {$assessment['lrn']}</p>
            <p><strong>Quarter:</strong> {$assessment['quarter']}</p>
            
            <p>The next step in the process will be scheduled soon. You will receive further notifications.</p>
            
            <p>Best regards,<br>SPED LMS System</p>
            ";
            
            MailHelper::send($assessment['parent_email'], $subject, $body);
            
        } catch (Exception $e) {
            error_log("Failed to send approval notification: " . $e->getMessage());
        }
    }

    /**
     * Notify parent of assessment rejection
     */
    private function notifyParentRejected($assessment, $reason) {
        try {
            $subject = "Assessment Needs Revision - {$assessment['student_name']}";
            
            $body = "
            <h2>Assessment Needs Revision</h2>
            <p>Dear {$assessment['parent_name']},</p>
            <p>Your child's educational assessment has been reviewed and requires revision.</p>
            
            <h3>Student Information</h3>
            <p><strong>Name:</strong> {$assessment['student_name']}</p>
            <p><strong>LRN:</strong> {$assessment['lrn']}</p>
            
            <h3>Reason for Revision</h3>
            <p>$reason</p>
            
            <p>Please review the assessment and resubmit with the necessary corrections.</p>
            
            <p><a href='" . BASE_PATH . "/assessment/conduct/{$assessment['student_id']}' style='background-color: #a01422; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Resubmit Assessment</a></p>
            
            <p>Best regards,<br>SPED LMS System</p>
            ";
            
            MailHelper::send($assessment['parent_email'], $subject, $body);
            
        } catch (Exception $e) {
            error_log("Failed to send rejection notification: " . $e->getMessage());
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
