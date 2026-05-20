<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-07
// Part of: SPED LMS — IEP Meeting Controller

require_once __DIR__ . '/../Models/IEPMeetingModel.php';

class IEPMeetingController {
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
        
        $this->meetingModel = new IEPMeetingModel();
    }

    /**
     * Show meeting details
     */
    public function show($meetingId) {
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'parent', 'master_teacher', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }
            
            // Get meeting
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                $_SESSION['error'] = 'Meeting not found';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            // Parent ownership check — parent can only view meetings for their own children
            if ($this->userRole === 'parent') {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT COUNT(*) FROM student_records sr
                    JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                    WHERE sr.id = :student_id AND es.parent_id = :parent_id
                ");
                $stmt->execute(['student_id' => $meeting['student_id'], 'parent_id' => $this->userId]);
                if ($stmt->fetchColumn() == 0) {
                    http_response_code(403);
                    $_SESSION['error'] = 'Access denied';
                    header('Location: ' . BASE_PATH . '/iep/meetings');
                    exit;
                }
            }
            
            // Only SPED Teacher can access PDSP — all others are read-only viewers
            $canAccessPDSP = in_array($this->userRole, ['sped_teacher', 'admin']);
            $isReadOnly = !$canAccessPDSP;
            
            // Get PDSP status if exists
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            $pdsp = $pdspModel->getByMeetingId($meetingId);
            
            // Log activity
            $this->logActivity('meeting.view', 'iep_meetings', $meetingId, 'Viewed meeting details');
            
            // Load view
            $basePath = BASE_PATH;
            require __DIR__ . '/../Views/iep_meeting/show.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->show() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading meeting';
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;
        }
    }

    /**
     * Show PDSP form for a meeting
     */
    public function pdspForm($meetingId) {
        try {
            // Only SPED Teacher can access PDSP form
            if (!in_array($this->userRole, ['sped_teacher', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied. Only SPED Teachers can manage PDSP forms.';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }
            
            // Get meeting
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                $_SESSION['error'] = 'Meeting not found';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            // Get or create PDSP record
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            
            $pdsp = $pdspModel->getByMeetingId($meetingId);
            if (!$pdsp) {
                // Create new PDSP record
                $pdspId = $pdspModel->create($meetingId, $meeting['student_id'], $this->userId);
                $pdsp = $pdspModel->findById($pdspId);
            }
            
            // Get existing domains
            $domains = $pdspModel->getDomains($pdsp['id']);
            
            // Get signatories
            $signatories = $pdspModel->getSignatories($pdsp['id']);
            
            // Define domain structure
            $domainNames = [
                'Perceptuo-Cognitive',
                'Psychosocial',
                'Socio-Emotional',
                'Psychomotor',
                'Daily Living Skills',
                'Communication and Language'
            ];
            
            // Performance levels
            $performanceLevels = [
                'beginning' => 'Beginning (74% and below)',
                'developing' => 'Developing (75-79%)',
                'approaching' => 'Approaching Proficiency (80-84%)',
                'proficient' => 'Proficient (85-89%)',
                'advanced' => 'Advanced (90% and above)'
            ];
            
            // Determine permissions
            $canEdit = ($this->userRole === 'sped_teacher' && $pdsp['status'] === 'draft');
            $canUploadDocument = in_array($this->userRole, ['sped_teacher', 'guidance', 'principal']);
            $canMarkAsSigned = ($this->userRole === 'sped_teacher' && $pdsp['status'] === 'draft');
            $isReadOnly = !$canEdit;
            
            // Check if signed document exists
            $hasSignedDocument = $pdspModel->hasSignedDocument($pdsp['id']);
            
            // Log activity
            $this->logActivity('pdsp.view', 'pdsp_records', $pdsp['id'], 'Viewed PDSP form');
            
            // Load simplified view (upload only)
            $basePath = BASE_PATH;
            require __DIR__ . '/../Views/iep_meeting/pdsp_form_simplified.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->pdspForm() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading PDSP form';
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;
        }
    }

    /**
     * Save PDSP form data
     */
    public function savePDSP() {
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            $pdspId = $_POST['pdsp_id'] ?? null;
            $meetingId = $_POST['meeting_id'] ?? null;
            
            if (!$pdspId) {
                $_SESSION['error'] = 'PDSP ID required';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            
            // Delete existing domains first
            $pdspModel->deleteDomains($pdspId);
            
            // Save each domain with all sub-domains
            $domains = $_POST['domains'] ?? [];
            foreach ($domains as $domainName => $subDomains) {
                if (is_array($subDomains)) {
                    foreach ($subDomains as $subDomainData) {
                        // Skip empty rows
                        if (empty($subDomainData['sub_domain']) && empty($subDomainData['skills_description'])) {
                            continue;
                        }
                        
                        $pdspModel->saveDomain($pdspId, [
                            'domain_name' => $domainName,
                            'sub_domain' => $subDomainData['sub_domain'] ?? '',
                            'skills_description' => $subDomainData['skills_description'] ?? '',
                            'mastered' => isset($subDomainData['mastered']) && $subDomainData['mastered'] == '1',
                            'educational_recommendation' => $subDomainData['educational_recommendation'] ?? '',
                            'q1_level' => $subDomainData['q1_level'] ?? null,
                            'q2_level' => $subDomainData['q2_level'] ?? null
                        ]);
                    }
                }
            }
            
            // Log activity
            $this->logActivity('pdsp.save', 'pdsp_records', $pdspId, 'Saved PDSP form data');
            
            $_SESSION['success'] = 'PDSP form saved successfully';
            header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId . '/pdsp');
            exit;
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->savePDSP() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error saving PDSP form: ' . $e->getMessage();
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;
        }
    }

    /**
     * Show signature page
     */
    public function signPDSP($pdspId) {
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin', 'parent'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }
            
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            
            $pdsp = $pdspModel->findById($pdspId);
            if (!$pdsp) {
                $_SESSION['error'] = 'PDSP not found';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            // Determine signatory role for current user
            $signatoryRole = $this->determineSignatoryRole();
            
            // Pass data to view
            $basePath = BASE_PATH;
            
            // Load view
            require __DIR__ . '/../Views/iep_meeting/pdsp_sign.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->signPDSP() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading signature page';
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;
        }
    }

    /**
     * Save signature
     */
    public function saveSignature() {
        header('Content-Type: application/json');
        
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            $pdspId = $input['pdsp_id'] ?? null;
            $signatoryRole = $input['signatory_role'] ?? null;
            $signatoryName = $input['signatory_name'] ?? null;
            $signatureData = $input['signature_data'] ?? null;
            
            if (!$pdspId || !$signatoryRole || !$signatoryName || !$signatureData) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            // Decode base64 signature image
            $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
            $signatureData = str_replace(' ', '+', $signatureData);
            $imageData = base64_decode($signatureData);
            
            // Create signatures directory
            $uploadDir = __DIR__ . '/../../public/uploads/signatures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $filename = 'signature_' . $pdspId . '_' . $signatoryRole . '_' . time() . '.png';
            $filepath = $uploadDir . $filename;
            
            // Save image
            if (!file_put_contents($filepath, $imageData)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save signature image']);
                exit;
            }
            
            // Save to database
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            
            $result = $pdspModel->saveSignature($pdspId, $signatoryRole, $signatoryName, 'uploads/signatures/' . $filename);
            
            if ($result) {
                // Check if all signatures are complete
                if ($pdspModel->areAllSignaturesComplete($pdspId)) {
                    // Update PDSP status to complete
                    $pdspModel->update($pdspId, ['status' => 'complete']);
                    
                    // Update meeting status to completed
                    $pdsp = $pdspModel->findById($pdspId);
                    $this->meetingModel->update($pdsp['meeting_id'], ['status' => 'completed']);
                    
                    // Send notification to SPED Teacher
                    require_once __DIR__ . '/../Models/NotificationModel.php';
                    $notificationModel = new NotificationModel();
                    $notificationModel->create(
                        $pdsp['filled_by'],
                        'pdsp_complete',
                        'PDSP Form Complete',
                        'All signatures have been collected for ' . $pdsp['student_name'] . '. Process 5 is now unlocked.',
                        ['pdsp_id' => $pdspId]
                    );
                    
                    // TODO: Send PHPMailer email notification
                }
                
                // Log activity
                $this->logActivity('pdsp.sign', 'pdsp_signatures', null, 
                    "Signed PDSP as $signatoryRole");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Signature saved successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save signature']);
            }
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->saveSignature() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving signature']);
        }
    }

    /**
     * AI Extract PDSP data from uploaded image using Tesseract OCR
     */
    public function aiExtract() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied. SPED teachers only.']);
                exit;
            }
            
            // Check if Tesseract OCR is installed
            require_once __DIR__ . '/../../config/tesseract.php';
            
            if (!TESSERACT_ENABLED) {
                http_response_code(503);
                echo json_encode([
                    'success' => false, 
                    'message' => 'OCR auto-fill is not available. Tesseract OCR is not installed. Please install Tesseract or fill the form manually.',
                    'install_required' => true,
                    'install_url' => 'https://github.com/tesseract-ocr/tesseract'
                ]);
                exit;
            }
            
            $pdspId = $_POST['pdsp_id'] ?? null;
            if (!$pdspId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'PDSP ID required']);
                exit;
            }
            
            // Check if signed document exists
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            
            if (!$pdspModel->hasSignedDocument($pdspId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Please upload signed document first before using OCR extraction']);
                exit;
            }
            
            // Get PDSP record
            $pdsp = $pdspModel->findById($pdspId);
            if (!$pdsp) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'PDSP not found']);
                exit;
            }
            
            // Use the already uploaded signed document
            $filepath = __DIR__ . '/../../public/' . $pdsp['signed_document_path'];
            
            if (!file_exists($filepath)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Signed document file not found']);
                exit;
            }
            
            // Extract text using Tesseract OCR
            require_once __DIR__ . '/../Helpers/TesseractHelper.php';
            
            $ocrResult = TesseractHelper::extractText($filepath);
            
            if (!$ocrResult['success']) {
                error_log("Tesseract OCR error: " . $ocrResult['error']);
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => 'OCR extraction failed. Please fill the form manually.',
                    'error' => $ocrResult['error']
                ]);
                exit;
            }
            
            // Parse extracted text into PDSP structure
            $domains = TesseractHelper::parsePDSPText($ocrResult['text']);
            
            if (empty($domains)) {
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Could not extract form data. Please fill the form manually.',
                    'hint' => 'Make sure the document is clear and legible'
                ]);
                exit;
            }
            
            // Update PDSP record
            $pdspModel->update($pdspId, [
                'ai_extracted' => true
            ]);
            
            // Log activity
            $this->logActivity('pdsp.ocr_extract', 'pdsp_records', $pdspId, 'OCR extracted PDSP data from signed document using Tesseract');
            
            echo json_encode([
                'success' => true,
                'message' => 'Form auto-filled successfully. Please review and correct all fields.',
                'domains' => $domains,
                'note' => 'OCR accuracy depends on document quality. Please verify all extracted data.'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->aiExtract() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'OCR extraction unavailable. Please fill the form manually.']);
        }
    }

    /**
     * Upload signed handwritten document
     */
    public function uploadSignedDocument() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $pdspId = $_POST['pdsp_id'] ?? null;
            if (!$pdspId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'PDSP ID required']);
                exit;
            }
            
            // Validate file upload
            if (!isset($_FILES['signed_document']) || $_FILES['signed_document']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'File upload failed']);
                exit;
            }
            
            $file = $_FILES['signed_document'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and PDF files are accepted']);
                exit;
            }
            
            // Validate file size (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB']);
                exit;
            }
            
            // Create upload directory
            $uploadDir = __DIR__ . '/../../public/uploads/pdsp_signed/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'pdsp_signed_' . $pdspId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
                exit;
            }
            
            // Save to database
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            
            $result = $pdspModel->uploadSignedDocument($pdspId, 'uploads/pdsp_signed/' . $filename);
            
            if ($result) {
                // Log activity
                $this->logActivity('pdsp.upload_signed_document', 'pdsp_records', $pdspId, 
                    'Uploaded signed handwritten document');
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Signed document uploaded successfully',
                    'filename' => $filename,
                    'filepath' => 'uploads/pdsp_signed/' . $filename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save document path']);
            }
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->uploadSignedDocument() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error uploading document']);
        }
    }

    /**
     * Mark PDSP as signed with validation
     */
    public function markAsSigned() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Get POST data
            $input = json_decode(file_get_contents('php://input'), true);
            
            $pdspId = $input['pdsp_id'] ?? null;
            $signatories = $input['signatories'] ?? [];
            $domains = $input['domains'] ?? [];
            
            if (!$pdspId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'PDSP ID required']);
                exit;
            }
            
            // Validation errors array
            $errors = [];
            
            // Load PDSP
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            $pdsp = $pdspModel->findById($pdspId);
            
            if (!$pdsp) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'PDSP not found']);
                exit;
            }
            
            // Validate signed document uploaded
            if (empty($pdsp['signed_document_path'])) {
                $errors[] = 'Signed handwritten document must be uploaded';
            }
            
            // Validate signatories
            if (empty($signatories)) {
                $errors[] = 'At least one signatory must be selected';
            } else {
                foreach ($signatories as $signatory) {
                    if (empty($signatory['name'])) {
                        $errors[] = 'Signatory name is required for ' . ($signatory['role'] ?? 'unknown role');
                    }
                }
            }
            
            // Validate domains
            $domainNames = [
                'Perceptuo-Cognitive',
                'Psychosocial',
                'Socio-Emotional',
                'Psychomotor',
                'Daily Living Skills',
                'Communication and Language'
            ];
            
            foreach ($domainNames as $domainName) {
                if (!isset($domains[$domainName]) || empty($domains[$domainName])) {
                    $errors[] = "Domain '$domainName' has no entries";
                    continue;
                }
                
                foreach ($domains[$domainName] as $index => $row) {
                    $rowNum = $index + 1;
                    
                    if (empty($row['sub_domain'])) {
                        $errors[] = "$domainName - Row $rowNum: Sub-Domain is required";
                    }
                    if (empty($row['skills_description'])) {
                        $errors[] = "$domainName - Row $rowNum: Skills Description is required";
                    }
                    if (!isset($row['mastered']) || $row['mastered'] === '') {
                        $errors[] = "$domainName - Row $rowNum: Mastered status must be selected";
                    }
                    if (empty($row['educational_recommendation'])) {
                        $errors[] = "$domainName - Row $rowNum: Educational Recommendation is required";
                    }
                    if (empty($row['q1_level'])) {
                        $errors[] = "$domainName - Row $rowNum: Q1 Level is required";
                    }
                    if (empty($row['q2_level'])) {
                        $errors[] = "$domainName - Row $rowNum: Q2 Level is required";
                    }
                }
            }
            
            // If validation fails, return errors
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
                exit;
            }
            
            // Mark as signed
            $result = $pdspModel->markAsSigned($pdspId, $signatories);
            
            if ($result) {
                // Update meeting status to completed
                $this->meetingModel->update($pdsp['meeting_id'], ['status' => 'completed']);
                
                // Send notifications to Guidance and Principal
                require_once __DIR__ . '/../Models/NotificationModel.php';
                $notificationModel = new NotificationModel();
                
                // Get Guidance and Principal users
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT id, name, email FROM users
                    WHERE role IN ('guidance', 'principal') AND status = 'active'
                ");
                $stmt->execute();
                $staff = $stmt->fetchAll();
                
                $signatoryNames = array_map(function($s) { return $s['name']; }, $signatories);
                $signatoryList = implode(', ', $signatoryNames);
                
                foreach ($staff as $user) {
                    $notificationModel->create(
                        $user['id'],
                        'pdsp_signed',
                        'PDSP Form Signed',
                        "PDSP for {$pdsp['student_name']} has been signed. Signatories: $signatoryList. Process 5 is now unlocked.",
                        ['pdsp_id' => $pdspId, 'meeting_id' => $pdsp['meeting_id']]
                    );
                    
                    // TODO: Send PHPMailer email notification
                }
                
                // Log activity
                $this->logActivity('pdsp.mark_signed', 'pdsp_records', $pdspId, 
                    "Marked PDSP as signed with signatories: $signatoryList");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'PDSP marked as signed successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to mark as signed']);
            }
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->markAsSigned() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error marking as signed']);
        }
    }

    /**
     * Determine signatory role for current user
     */
    private function determineSignatoryRole() {
        switch ($this->userRole) {
            case 'sped_teacher':
                return 'sped_teacher';
            case 'guidance':
                return 'ilrc_supervisor';
            case 'principal':
                return 'school_head';
            case 'parent':
                return 'parent_guardian';
            default:
                return null;
        }
    }

    /**
     * Update meeting details (SPED Teacher only)
     */
    public function updateMeeting($meetingId) {
        try {
            if (!in_array($this->userRole, ['sped_teacher', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }

            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                $_SESSION['error'] = 'Meeting not found';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }

            if (!in_array($meeting['status'], ['scheduled', 'rescheduled'])) {
                $_SESSION['error'] = 'Only scheduled meetings can be edited';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }

            $meetingDate = $_POST['meeting_date'] ?? null;
            $meetingTime = $_POST['meeting_time'] ?? null;
            $location    = trim($_POST['meeting_location'] ?? '');
            $agenda      = trim($_POST['agenda'] ?? '');

            if (!$meetingDate || !$meetingTime || !$location) {
                $_SESSION['error'] = 'Date, time, and venue are required';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }

            $meetingDatetime = date('Y-m-d H:i:s', strtotime($meetingDate . ' ' . $meetingTime));

            $this->meetingModel->update($meetingId, [
                'meeting_date'     => $meetingDatetime,
                'meeting_location' => $location,
                'agenda'           => $agenda,
                'status'           => 'rescheduled',
            ]);

            // Re-notify participants
            $this->sendMeetingNotifications($meetingId);

            $this->logActivity('meeting.update', 'iep_meetings', $meetingId,
                "Updated meeting details: date=$meetingDatetime, venue=$location");

            $_SESSION['success'] = 'Meeting updated successfully. Participants have been notified.';
            header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
            exit;

        } catch (Exception $e) {
            error_log("IEPMeetingController->updateMeeting() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error updating meeting: ' . $e->getMessage();
            header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
            exit;
        }
    }

    /**
     * Cancel meeting — SPED Teacher, Guidance, or Principal
     * Parent is obliged to attend — cannot cancel
     */
    public function cancelMeeting($meetingId) {
        try {
            $allowedRoles = ['sped_teacher', 'guidance', 'principal', 'admin'];
            if (!in_array($this->userRole, $allowedRoles)) {
                http_response_code(403);
                $_SESSION['error'] = 'Only SPED Teacher, Guidance, or Principal can cancel a meeting.';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }

            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                $_SESSION['error'] = 'Meeting not found';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }

            if (!in_array($meeting['status'], ['scheduled', 'rescheduled'])) {
                $_SESSION['error'] = 'Only scheduled meetings can be cancelled';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }

            $reason = trim($_POST['cancellation_reason'] ?? '');
            if (empty($reason)) {
                $_SESSION['error'] = 'Please provide a reason for cancellation';
                header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
                exit;
            }

            $this->meetingModel->update($meetingId, [
                'status'              => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            // Notify all participants
            require_once __DIR__ . '/../Models/NotificationModel.php';
            $notifModel = new NotificationModel();
            $db = Database::getInstance()->getConnection();

            // Get parent of the student
            $stmt = $db->prepare("
                SELECT u.id, u.name FROM users u
                JOIN enrollment_submissions es ON u.id = es.parent_id
                JOIN student_records sr ON es.id = sr.enrollment_id
                WHERE sr.id = :student_id LIMIT 1
            ");
            $stmt->execute(['student_id' => $meeting['student_id']]);
            $parent = $stmt->fetch();

            // Get guidance and principal
            $stmt = $db->prepare("SELECT id, name FROM users WHERE role IN ('guidance','principal') AND status='active'");
            $stmt->execute();
            $staff = $stmt->fetchAll();

            $recipients = $staff;
            if ($parent) $recipients[] = $parent;

            $cancellerName = $_SESSION['user_name'] ?? 'Staff';
            foreach ($recipients as $r) {
                $notifModel->create(
                    $r['id'],
                    'meeting_cancelled',
                    'IEP Meeting Cancelled',
                    "The IEP meeting for {$meeting['student_name']} scheduled on " .
                    date('M d, Y g:i A', strtotime($meeting['meeting_date'])) .
                    " has been cancelled by {$cancellerName}. Reason: {$reason}",
                    ['meeting_id' => $meetingId]
                );
            }

            $this->logActivity('meeting.cancel', 'iep_meetings', $meetingId,
                "Cancelled by {$this->userRole}: $reason");

            $_SESSION['success'] = 'Meeting cancelled. All participants have been notified.';
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;

        } catch (Exception $e) {
            error_log("IEPMeetingController->cancelMeeting() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error cancelling meeting: ' . $e->getMessage();
            header('Location: ' . BASE_PATH . '/iep/meetings/' . $meetingId);
            exit;
        }
    }

    /**
     * List all IEP meetings
     */
    public function index() {
        try {
            // Check permission — parent can view their child's meetings (read-only)
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'parent', 'master_teacher', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }
            
            // For parent: only show meetings for their children
            if ($this->userRole === 'parent') {
                $upcomingMeetings = $this->meetingModel->getAll(['upcoming' => true,  'parent_id' => $this->userId]);
                $pastMeetings     = $this->meetingModel->getAll(['past' => true, 'parent_id' => $this->userId]);
            } else {
                $upcomingMeetings = $this->meetingModel->getAll(['upcoming' => true]);
                $pastMeetings     = $this->meetingModel->getAll(['past' => true]);
            }
            
            // Pass data to view
            $basePath = BASE_PATH;
            
            // Log activity
            $this->logActivity('meeting.list', 'iep_meetings', null, 'Viewed meeting list');
            
            // Load view
            require __DIR__ . '/../Views/iep_meeting/index.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->index() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading meetings';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
    }

    /**
     * Show meeting scheduler form
     */
    public function schedule() {
        try {
            // Check permission - only SPED Teacher can schedule
            if (!in_array($this->userRole, ['sped_teacher', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied. SPED teachers only.';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            // Get students with finalized assessments
            require_once __DIR__ . '/../Models/AssessmentModel.php';
            require_once __DIR__ . '/../Models/StudentModel.php';
            
            $assessmentModel = new AssessmentModel();
            $studentModel = new StudentModel();
            
            // Get students with finalized assessments
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT DISTINCT s.id, s.student_name, s.lrn
                FROM student_records s
                JOIN assessment_records ar ON s.id = ar.student_id
                WHERE ar.status = 'finalized'
                ORDER BY s.student_name
            ");
            $students = $stmt->fetchAll();
            
            // Get suggested dates (when all participants available)
            $suggestedDates = $this->meetingModel->getSuggestedDates();
            
            // Pass data to view
            $basePath = BASE_PATH;
            
            // Log activity
            $this->logActivity('meeting.schedule_form', 'iep_meetings', null, 'Opened meeting scheduler');
            
            // Load view
            require __DIR__ . '/../Views/iep_meeting/schedule.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->schedule() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading scheduler';
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;
        }
    }

    /**
     * Create IEP meeting
     */
    public function createMeeting() {
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied';
                header('Location: ' . BASE_PATH . '/iep/meetings');
                exit;
            }
            
            // Get form data
            $studentId = $_POST['student_id'] ?? null;
            $meetingDate = $_POST['meeting_date'] ?? null;
            $meetingTime = $_POST['meeting_time'] ?? null;
            $meetingLocation = $_POST['meeting_location'] ?? $_POST['venue'] ?? null;
            $agendaNotes = $_POST['agenda_notes'] ?? $_POST['agenda'] ?? null;
            $manualOverride = isset($_POST['manual_override']) && $_POST['manual_override'] === '1';
            $overrideReason = $_POST['override_reason'] ?? null;
            
            // Validate required fields
            if (!$studentId || !$meetingDate || !$meetingTime) {
                $_SESSION['error'] = 'Student, date, and time are required';
                header('Location: ' . BASE_PATH . '/iep/meetings/schedule');
                exit;
            }
            
            if (!$meetingLocation) {
                $_SESSION['error'] = 'Meeting location (venue) is required';
                header('Location: ' . BASE_PATH . '/iep/meetings/schedule');
                exit;
            }
            
            // Get latest finalized assessment for student
            require_once __DIR__ . '/../Models/AssessmentModel.php';
            $assessmentModel = new AssessmentModel();
            $assessment = $assessmentModel->getLatest($studentId);
            $assessmentId = ($assessment && $assessment['status'] === 'finalized') ? $assessment['id'] : null;
            
            // Create meeting
            $meetingData = [
                'student_id' => $studentId,
                'assessment_id' => $assessmentId,
                'scheduled_by' => $this->userId,
                'meeting_date' => $meetingDate,
                'meeting_time' => $meetingTime,
                'meeting_location' => $meetingLocation,
                'agenda_notes' => $agendaNotes
            ];
            
            if ($manualOverride && $overrideReason) {
                $meetingData['agenda_notes'] = "MANUAL OVERRIDE: $overrideReason\n\n" . $agendaNotes;
            }
            
            $meetingId = $this->meetingModel->create($meetingData);
            
            // Send notifications
            $this->sendMeetingNotifications($meetingId);
            
            // Log activity
            $this->logActivity('meeting.create', 'iep_meetings', $meetingId, 
                "Created IEP meeting for student: $studentId on $meetingDate");
            
            $_SESSION['success'] = 'IEP meeting scheduled successfully. Notifications sent to all participants.';
            header('Location: ' . BASE_PATH . '/iep/meetings');
            exit;
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->createMeeting() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error creating meeting: ' . $e->getMessage();
            header('Location: ' . BASE_PATH . '/iep/meetings/schedule');
            exit;
        }
    }

    /**
     * Send meeting notifications to all participants
     */
    private function sendMeetingNotifications($meetingId) {
        try {
            require_once __DIR__ . '/../Helpers/MailHelper.php';
            require_once __DIR__ . '/../Models/NotificationModel.php';
            
            $meeting = $this->meetingModel->findById($meetingId);
            if (!$meeting) {
                return;
            }
            
            // Get participants: Guidance, Principal, Parent
            $db = Database::getInstance()->getConnection();
            
            // Get Guidance and Principal
            $stmt = $db->prepare("
                SELECT id, name, email FROM users
                WHERE role IN ('guidance', 'principal') AND status = 'active'
            ");
            $stmt->execute();
            $staff = $stmt->fetchAll();
            
            // Get Parent
            $stmt = $db->prepare("
                SELECT u.id, u.name, u.email
                FROM users u
                JOIN enrollment_submissions es ON u.id = es.parent_id
                JOIN student_records sr ON es.id = sr.enrollment_id
                WHERE sr.id = :student_id
                LIMIT 1
            ");
            $stmt->execute(['student_id' => $meeting['student_id']]);
            $parent = $stmt->fetch();
            
            $participants = $staff;
            if ($parent) {
                $participants[] = $parent;
            }
            
            // Prepare email content
            $subject = "IEP Meeting Scheduled - {$meeting['student_name']}";
            $meetingDateTime = date('F d, Y g:i A', strtotime($meeting['meeting_date']));
            $location = $meeting['meeting_location'] ?? 'TBA';
            
            foreach ($participants as $participant) {
                // Send email
                $body = "
                <h2>IEP Meeting Scheduled</h2>
                <p>Dear {$participant['name']},</p>
                <p>An IEP meeting has been scheduled for the following student:</p>
                
                <h3>Meeting Details</h3>
                <p><strong>Student:</strong> {$meeting['student_name']} (LRN: {$meeting['lrn']})</p>
                <p><strong>Date & Time:</strong> $meetingDateTime</p>
                <p><strong>Venue:</strong> $location</p>
                
                <h3>Agenda</h3>
                <p>" . nl2br(htmlspecialchars($meeting['agenda'] ?? $meeting['agenda_notes'] ?? '')) . "</p>
                
                <p><strong>Scheduled by:</strong> {$meeting['scheduled_by_name']}</p>
                
                <p>Please mark your calendar and attend this important meeting.</p>
                
                <p>Best regards,<br>SPED LMS System</p>
                ";
                
                @MailHelper::sendNotification($participant['email'], $participant['name'], $subject, $body);
                
                // Create in-system notification
                $notificationModel = new NotificationModel();
                $notificationModel->create(
                    $participant['id'],
                    'iep_meeting_scheduled',
                    'IEP Meeting Scheduled',
                    "IEP meeting for {$meeting['student_name']} on $meetingDateTime. Venue: $location",
                    [
                        'meeting_id' => $meetingId,
                        'student_name' => $meeting['student_name'],
                        'meeting_date' => $meeting['meeting_date'],
                        'meeting_time' => $meeting['meeting_time']
                    ]
                );
                
                // Save notification record
                $this->meetingModel->saveNotification($meetingId, $participant['id'], 'both');
            }
            
            error_log("Sent meeting notifications for meeting ID: $meetingId");
            
        } catch (Exception $e) {
            error_log("Failed to send meeting notifications: " . $e->getMessage());
            // Don't throw - notification failure shouldn't block meeting creation
        }
    }

    /**
     * Show availability calendar for current user
     */
    public function availability() {
        try {
            // Check permission - only staff roles can manage availability
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                $_SESSION['error'] = 'Access denied. Staff only.';
                header('Location: ' . BASE_PATH . '/dashboard');
                exit;
            }
            
            // Get current month or requested month
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            
            // Validate year and month
            $year = (int)$year;
            $month = (int)$month;
            if ($month < 1) { $month = 12; $year--; }
            if ($month > 12) { $month = 1; $year++; }
            
            // Get recurring availability
            $recurringAvailability = $this->meetingModel->getRecurringAvailability($this->userId);
            
            // Get exceptions for this month
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));
            $exceptions = $this->meetingModel->getExceptions($this->userId, $startDate, $endDate);
            
            // Generate calendar data
            $calendarData = $this->generateCalendarData($year, $month, $recurringAvailability, $exceptions);
            
            // Pass data to view
            $basePath = BASE_PATH;
            $currentYear = $year;
            $currentMonth = $month;
            
            // Log activity
            $this->logActivity('availability.view', 'user_availability', null, 'Viewed availability calendar');
            
            // Load view
            require __DIR__ . '/../Views/iep_meeting/availability.php';
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->availability() ERROR: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading availability calendar';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }
    }

    /**
     * Save recurring availability (weekly schedule)
     */
    public function saveRecurringAvailability() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Get selected days from POST
            $days = $_POST['days'] ?? [];
            
            // Validate days (0-6)
            $validDays = [];
            foreach ($days as $day) {
                $day = (int)$day;
                if ($day >= 0 && $day <= 6) {
                    $validDays[] = $day;
                }
            }
            
            // Save to database
            $result = $this->meetingModel->saveRecurringAvailability($this->userId, $validDays);
            
            if ($result) {
                // Log activity
                $this->logActivity('availability.save_recurring', 'user_availability', null, 
                    'Saved recurring availability: ' . implode(',', $validDays));
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Weekly schedule saved successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to save weekly schedule'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->saveRecurringAvailability() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error saving availability']);
        }
    }

    /**
     * Toggle exception date
     */
    public function toggleExceptionDate() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Get date and availability from POST
            $date = $_POST['date'] ?? null;
            $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
            $note = isset($_POST['note']) ? trim($_POST['note']) : null;
            if ($note === '') $note = null;
            
            if (!$date) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Date required']);
                exit;
            }
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid date format']);
                exit;
            }
            
            $result = $this->meetingModel->toggleException($this->userId, $date, $isAvailable, $note);
            
            if ($result) {
                // Log activity
                $this->logActivity('availability.toggle_exception', 'user_availability', null, 
                    "Toggled exception for date: $date");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Exception toggled successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to toggle exception'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->toggleExceptionDate() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error toggling exception']);
        }
    }

    /**
     * Generate calendar data for a month
     */
    private function generateCalendarData($year, $month, $recurringAvailability, $exceptions) {
        $firstDay    = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $dayOfWeek   = date('w', $firstDay);

        // Fetch IEP meetings for this month for the current user
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));
        $iepMeetings = [];
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT DATE(meeting_date) as mdate, sr.student_name
                FROM iep_meetings im
                JOIN student_records sr ON im.student_id = sr.id
                WHERE (im.scheduled_by = :uid OR im.guidance_id = :uid2 OR im.principal_id = :uid3)
                  AND DATE(meeting_date) BETWEEN :start AND :end
                  AND im.status IN ('scheduled','rescheduled')
            ");
            $stmt->execute([
                'uid' => $this->userId, 'uid2' => $this->userId, 'uid3' => $this->userId,
                'start' => $startDate, 'end' => $endDate
            ]);
            foreach ($stmt->fetchAll() as $row) {
                $iepMeetings[$row['mdate']] = $row['student_name'];
            }
        } catch (Exception $e) {
            error_log("generateCalendarData IEP meetings fetch failed: " . $e->getMessage());
        }

        $calendar = [];
        $week = [];

        for ($i = 0; $i < $dayOfWeek; $i++) {
            $week[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dow  = date('w', strtotime($date));

            $isAvailable = false;
            $isException = false;
            $note        = null;

            if (isset($exceptions[$date])) {
                $isAvailable = $exceptions[$date]['is_available'];
                $note        = $exceptions[$date]['note'] ?? null;
                $isException = true;
            } elseif (isset($recurringAvailability[$dow])) {
                $isAvailable = $recurringAvailability[$dow];
            }

            $week[] = [
                'day'         => $day,
                'date'        => $date,
                'is_available'=> $isAvailable,
                'is_exception'=> $isException,
                'is_today'    => $date === date('Y-m-d'),
                'note'        => $note,
                'iep_meeting' => $iepMeetings[$date] ?? null,
            ];

            if (count($week) === 7) {
                $calendar[] = $week;
                $week = [];
            }
        }

        while (count($week) < 7 && count($week) > 0) {
            $week[] = null;
        }
        if (!empty($week)) {
            $calendar[] = $week;
        }

        return $calendar;
    }

    /**
     * Submit PDSP (Simplified - Upload Only)
     */
    public function submitPDSP() {
        header('Content-Type: application/json');
        
        try {
            // Check permission
            if (!in_array($this->userRole, ['sped_teacher', 'admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied. SPED teachers only.']);
                exit;
            }
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            $pdspId = $input['pdsp_id'] ?? null;
            $meetingId = $input['meeting_id'] ?? null;
            $signatories = $input['signatories'] ?? [];
            
            if (!$pdspId || !$meetingId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'PDSP ID and Meeting ID required']);
                exit;
            }
            
            // Validate signatories
            if (empty($signatories)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'At least one signatory is required']);
                exit;
            }
            
            // Load PDSP
            require_once __DIR__ . '/../Models/PDSPModel.php';
            $pdspModel = new PDSPModel();
            $pdsp = $pdspModel->findById($pdspId);
            
            if (!$pdsp) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'PDSP not found']);
                exit;
            }
            
            // Validate document uploaded
            if (empty($pdsp['signed_document_path'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Please upload PDSP document first']);
                exit;
            }
            
            // Update PDSP status to 'signed' and save signatories
            $result = $pdspModel->update($pdspId, [
                'status' => 'signed',
                'signatories' => json_encode($signatories),
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$result) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update PDSP']);
                exit;
            }
            
            // Update meeting status to 'completed'
            $meetingResult = $this->meetingModel->update($meetingId, [
                'status' => 'completed'
            ]);
            
            if (!$meetingResult) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update meeting status']);
                exit;
            }
            
            // Send notifications to Guidance and Principal
            require_once __DIR__ . '/../Models/NotificationModel.php';
            $notificationModel = new NotificationModel();
            
            // Get Guidance and Principal users
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id, name, email FROM users
                WHERE role IN ('guidance', 'principal') AND status = 'active'
            ");
            $stmt->execute();
            $staff = $stmt->fetchAll();
            
            $signatoryNames = array_map(function($s) { return $s['name']; }, $signatories);
            $signatoryList = implode(', ', $signatoryNames);
            
            foreach ($staff as $user) {
                $notificationModel->create(
                    $user['id'],
                    'pdsp_completed',
                    'PDSP Completed',
                    "PDSP for {$pdsp['student_name']} has been completed. Signatories: $signatoryList.",
                    ['pdsp_id' => $pdspId, 'meeting_id' => $meetingId]
                );
            }
            
            // Log activity
            $this->logActivity('pdsp.submit', 'pdsp_records', $pdspId, 
                "Submitted PDSP with signatories: $signatoryList. Meeting marked as completed.");
            
            echo json_encode([
                'success' => true,
                'message' => 'PDSP submitted successfully. Meeting status updated to Completed.'
            ]);
            
        } catch (Exception $e) {
            error_log("IEPMeetingController->submitPDSP() ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error submitting PDSP']);
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
