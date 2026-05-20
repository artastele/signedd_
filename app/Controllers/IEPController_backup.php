<?php
// DO NOT ALTER WITHOUT APPROVAL ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â IEP Controller (Individualized Education Plan)

require_once __DIR__ . '/../Models/IEPModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Helpers/MailHelper.php';

class IEPController {
    private $iepModel;
    private $notifModel;
    private $userId;
    private $userRole;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
        $this->userId    = $_SESSION['user_id'];
        $this->userRole  = $_SESSION['role'] ?? 'user';
        $this->iepModel  = new IEPModel();
        $this->notifModel = new NotificationModel();
    }

    // ============================================================
    // INDEX ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â IEP Repository (list all IEPs per role)
    // ============================================================
    public function index() {
        $role     = $this->userRole;
        $basePath = BASE_PATH;

        if (!in_array($role, ['sped_teacher','guidance','principal','parent','admin'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Filter params
        $filterYear   = $_GET['school_year'] ?? '';
        $filterStatus = $_GET['status'] ?? '';

        if ($role === 'sped_teacher' || $role === 'admin') {
            $ieps = $this->iepModel->getByTeacher($this->userId);
        } elseif ($role === 'parent') {
            $ieps = $this->iepModel->getSignedForRole('parent', $this->userId);
        } else {
            $ieps = $this->iepModel->getSignedForRole($role, $this->userId);
        }

        // Apply filters
        if ($filterYear) {
            $ieps = array_filter($ieps, fn($i) => $i['school_year'] === $filterYear);
        }
        if ($filterStatus) {
            $ieps = array_filter($ieps, fn($i) => $i['status'] === $filterStatus);
        }

        // Eligible students for new IEP (sped_teacher only)
        $eligibleStudents = [];
        if ($role === 'sped_teacher' || $role === 'admin') {
            $eligibleStudents = $this->iepModel->getEligibleStudents($this->userId);
        }

        require __DIR__ . '/../Views/iep/index.php';
    }

    // ============================================================
    // CREATE ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â Start new IEP draft for a student
    // ============================================================
    public function create() {
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            $_SESSION['error'] = 'Only SPED Teachers can draft an IEP.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $studentId = $_GET['student_id'] ?? null;
        if (!$studentId) {
            $_SESSION['error'] = 'No student selected.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Verify signed PDSP exists
        $pdsp = $this->iepModel->getSignedPDSP($studentId);
        if (!$pdsp) {
            $_SESSION['error'] = 'This student does not have a signed PDSP yet. Complete Process 4 first.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Check if a draft already exists for this student + school year
        $existing = $this->iepModel->getLatestByStudent($studentId);
        if ($existing && in_array($existing['status'], ['draft','signing'])) {
            // Resume existing draft
            header('Location: ' . BASE_PATH . '/iep/form/' . $existing['id']);
            exit;
        }

        // Create new draft
        $schoolYear = date('Y') . '-' . (date('Y') + 1);
        $iepId = $this->iepModel->create($studentId, $pdsp['id'], $this->userId, $schoolYear);

        // Pre-populate domains from PDSP
        $pdspDomains = $this->iepModel->getPDSPDomains($pdsp['id']);
        $domainNames = array_unique(array_column($pdspDomains, 'domain_name'));
        if (!empty($domainNames)) {
            $this->iepModel->saveDomains($iepId, array_values($domainNames));
        }

        $this->logActivity('iep.created', $iepId, "Created IEP draft for student: $studentId");

        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    // ============================================================
    // FORM ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â Show IEP form (draft/edit)
    // ============================================================
    public function form($iepId) {
        $iep = $this->iepModel->findById($iepId);
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // SPED Teacher: must be drafter; others: read-only if signed/locked
        $role     = $this->userRole;
        $readOnly = false;

        if ($role === 'sped_teacher' || $role === 'admin') {
            if ($iep['drafted_by'] != $this->userId && $role !== 'admin') {
                $_SESSION['error'] = 'You can only edit IEPs you drafted.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
            if (in_array($iep['status'], ['signed','locked'])) {
                $readOnly = true;
            }
        } elseif (in_array($role, ['guidance','principal','parent'])) {
            // Can view if a copy was sent to them OR status is signing/signed/locked
            $db2 = Database::getInstance()->getConnection();
            $stmt2 = $db2->prepare("SELECT id FROM iep_copies WHERE iep_id = :iep_id AND sent_to = :user_id LIMIT 1");
            $stmt2->execute(['iep_id' => $iepId, 'user_id' => $this->userId]);
            $hasCopy = $stmt2->fetch();
            if (!$hasCopy && !in_array($iep['status'], ['signed','locked','signing'])) {
                $_SESSION['error'] = 'This IEP has not been shared with you yet.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
            // Read-only ONLY if fully signed/locked ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â otherwise they can sign
            $readOnly = in_array($iep['status'], ['signed','locked']);
        } else {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Load all related data
        $domains        = $this->iepModel->getDomains($iepId);
        $core           = $this->iepModel->getCore($iepId);
        $steps          = $this->iepModel->getSteps($iepId);
        $signatories    = $this->iepModel->getSignatories($iepId);
        $studentData    = $this->iepModel->getStudentAutoFill($iep['student_id']);
        $pdspDomains    = $this->iepModel->getPDSPDomains($iep['pdsp_id']);
        $pdspRecord     = $this->iepModel->getPDSPRecord($iep['pdsp_id']);
        $assessmentDocs = $this->iepModel->getAssessmentDocuments($iep['student_id']);
        $linkedParent   = $this->iepModel->getLinkedParent($iep['student_id']);

        // Auto-fill users for signatory slots
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name FROM users WHERE role = 'guidance' AND status = 'active' ORDER BY name LIMIT 1");
        $stmt->execute();
        $linkedGuidance = $stmt->fetch();

        $stmt = $db->prepare("SELECT id, name FROM users WHERE role = 'principal' AND status = 'active' ORDER BY name LIMIT 1");
        $stmt->execute();
        $linkedPrincipal = $stmt->fetch();

        // Mark copy as viewed for non-teacher roles
        if (in_array($role, ['guidance','principal','parent'])) {
            $this->iepModel->markCopyViewed($iepId, $this->userId);
        }

        $this->logActivity('iep.viewed', $iepId, "Viewed IEP form");

        $basePath = BASE_PATH;
        require __DIR__ . '/../Views/iep/form.php';
    }

    // ============================================================
    // SAVE DRAFT ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â AJAX: save all IEP sections
    // ============================================================
    public function saveDraft() {
        header('Content-Type: application/json');

        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        if (!$iepId) {
            echo json_encode(['success' => false, 'message' => 'Missing IEP ID']);
            exit;
        }

        $iep = $this->iepModel->findById($iepId);
        if (!$iep || in_array($iep['status'], ['signed','locked'])) {
            echo json_encode(['success' => false, 'message' => 'IEP is locked or not found']);
            exit;
        }

        try {
            // Save header fields
            $this->iepModel->update($iepId, [
                'school_year'       => $_POST['school_year']       ?? $iep['school_year'],
                're_evaluation_date'=> $_POST['re_evaluation_date'] ?? null,
                'signing_method'    => $_POST['signing_method']    ?? null,
            ]);

            // Save domains
            $domains = json_decode($_POST['domains'] ?? '[]', true);
            if (!empty($domains)) {
                $this->iepModel->saveDomains($iepId, $domains);
            }

            // Save core
            $this->iepModel->saveCore(
                $iepId,
                $_POST['developmental_domain'] ?? '',
                $_POST['priority_needs']       ?? '',
                $_POST['terminal_objectives']  ?? ''
            );

            // Save steps
            $steps = json_decode($_POST['steps'] ?? '[]', true);
            if (!empty($steps)) {
                $this->iepModel->saveSteps($iepId, $steps);
            }

            // Save signatories
            $signatories = json_decode($_POST['signatories'] ?? '[]', true);
            if (!empty($signatories)) {
                $this->iepModel->saveSignatories($iepId, $signatories);
            }

            $this->logActivity('iep.draft_saved', $iepId, 'Draft saved');
            echo json_encode(['success' => true, 'message' => 'Draft saved']);

        } catch (Exception $e) {
            error_log("IEPController->saveDraft() ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // UPLOAD SIGNED DOCUMENT ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â AJAX (print_upload flow)
    // ============================================================
    public function uploadSignedDoc() {
        header('Content-Type: application/json');

        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        if (!$iepId || !isset($_FILES['signed_doc'])) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $file = $_FILES['signed_doc'];
        $allowedTypes = ['image/jpeg','image/png','application/pdf'];
        $allowedExts  = ['jpg','jpeg','png','pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'message' => 'Only jpg, png, pdf allowed']);
            exit;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/iep_signed/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'iep_signed_' . $iepId . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
            exit;
        }

        $path = 'uploads/iep_signed/' . $filename;
        $this->iepModel->update($iepId, ['signed_document_path' => $path]);
        $this->logActivity('iep.doc_uploaded', $iepId, 'Signed document uploaded');

        echo json_encode([
            'success'  => true,
            'message'  => 'Document uploaded',
            'filename' => $filename,
            'path'     => $path,
        ]);
        exit;
    }


    // ============================================================
    // SEND IEP DRAFT -- send to guidance, principal, parent
    // ============================================================
    public function sendDraft() {
        header('Content-Type: application/json');
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']); exit;
        }
        $iepId = (int)($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);
        if (!$iep) { echo json_encode(['success' => false, 'message' => 'IEP not found']); exit; }

        // Validate before sending
        if (!in_array($iep['status'], ['draft','signing'])) {
            echo json_encode(['success' => false, 'message' => 'Only draft IEPs can be sent.']); exit;
        }
        $signatories = $this->iepModel->getSignatories($iepId);
        if (empty($signatories)) {
            echo json_encode(['success' => false, 'message' => 'Save draft with at least one signatory name first.']); exit;
        }
        if (empty($iep['re_evaluation_date'])) {
            echo json_encode(['success' => false, 'message' => 'Re-evaluation date is required before sending.']); exit;
        }

        // Update status to signing -- now visible to recipients
        $this->iepModel->update($iepId, ['status' => 'signing']);

        $db = Database::getInstance()->getConnection();
        $recipients = [];
        $sent = [];

        // Parent
        $parent = $this->iepModel->getLinkedParent($iep['student_id']);
        if ($parent) $recipients[] = $parent;

        // Guidance + Principal
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE role IN ('guidance','principal') AND status = 'active'");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $s) $recipients[] = $s;

        $link = getenv('APP_URL') . BASE_PATH . '/iep/form/' . $iepId;

        foreach ($recipients as $user) {
            // Record copy (skip if already sent)
            $chk = $db->prepare("SELECT id FROM iep_copies WHERE iep_id = :iid AND sent_to = :uid LIMIT 1");
            $chk->execute(['iid' => $iepId, 'uid' => $user['id']]);
            if (!$chk->fetch()) $this->iepModel->recordCopy($iepId, $user['id']);

            // In-system notification
            $this->notifModel->create($user['id'], 'iep_draft_sent',
                'IEP Draft ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ' . $iep['student_name'],
                "An IEP draft for {$iep['student_name']} has been shared with you for review and signature.",
                json_encode(['iep_id' => $iepId])
            );

            // Email
            if (!empty($user['email'])) {
                MailHelper::sendNotification($user['email'], $user['name'],
                    'IEP Draft Shared ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ' . $iep['student_name'] . ' ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â SPED LMS',
                    "<h2 style='color:#1e4072;'>IEP Draft Shared</h2>
                     <p>Dear {$user['name']},</p>
                     <p>An IEP draft for <strong>{$iep['student_name']}</strong> has been shared with you for review and signature.</p>
                     <p><a href='{$link}' style='background:#a01422;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>View &amp; Sign IEP</a></p>
                     <p>Best regards,<br>SPED LMS</p>"
                );
            }
            $sent[] = $user['name'];
        }

        $this->logActivity('iep.draft_sent', $iepId, 'IEP draft sent to: ' . implode(', ', $sent));
        echo json_encode(['success' => true, 'message' => 'IEP draft sent to: ' . implode(', ', $sent), 'sent_to' => $sent]);
        exit;
    }

    // ============================================================
    // MARK F2F SIGNED -- record physical signature (print_upload)
    // ============================================================
    public function markF2FSigned() {
        header('Content-Type: application/json');
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']); exit;
        }
        $iepId       = (int)($_POST['iep_id']       ?? 0);
        $signatoryId = (int)($_POST['signatory_id'] ?? 0);
        if (!$iepId || !$signatoryId) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']); exit;
        }
        $sig = $this->iepModel->getSignatoryById($signatoryId);
        if (!$sig || $sig['iep_id'] != $iepId) {
            echo json_encode(['success' => false, 'message' => 'Invalid signatory']); exit;
        }
        $stmt = Database::getInstance()->getConnection()->prepare(
            "UPDATE iep_signatories SET signature_image_path = 'f2f_signed', signed_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['id' => $signatoryId]);
        $this->logActivity('iep.f2f_signed', $iepId, "F2F signed: {$sig['signatory_name']}");
        echo json_encode(['success' => true, 'message' => 'Marked as signed on paper']);
        exit;
    }

    // ============================================================
    // SAVE DIGITAL SIGNATURE ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â AJAX (digital flow)
    // ============================================================
    public function saveSignature() {
        header('Content-Type: application/json');

        $iepId        = (int)($_POST['iep_id']        ?? 0);
        $signatoryId  = (int)($_POST['signatory_id']  ?? 0);
        $signatureB64 = $_POST['signature_data']       ?? '';

        if (!$iepId || !$signatoryId || empty($signatureB64)) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        // Verify signatory belongs to this IEP
        $sig = $this->iepModel->getSignatoryById($signatoryId);
        if (!$sig || $sig['iep_id'] != $iepId) {
            echo json_encode(['success' => false, 'message' => 'Invalid signatory']);
            exit;
        }

        // Verify current user is allowed to sign this slot
        $roleMap = [
            'guidance'    => 'guidance_counselor',
            'principal'   => 'school_head',
            'parent'      => 'parent_guardian',
            'sped_teacher'=> 'sned_teacher',
        ];
        $expectedRole = $roleMap[$this->userRole] ?? null;
        if (!$expectedRole || $sig['signatory_role'] !== $expectedRole) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to sign this slot']);
            exit;
        }

        // IEP must be in signing state
        $iep = $this->iepModel->findById($iepId);
        if (!$iep || in_array($iep['status'], ['signed','locked'])) {
            echo json_encode(['success' => false, 'message' => 'IEP is not in signing state']);
            exit;
        }

        // Decode and save PNG
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureB64));
        $uploadDir = __DIR__ . '/../../public/uploads/signatures/iep/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'sig_iep' . $iepId . '_' . $signatoryId . '_' . time() . '.png';
        file_put_contents($uploadDir . $filename, $imageData);

        $path = 'uploads/signatures/iep/' . $filename;
        $this->iepModel->saveSignatureImage($signatoryId, $path);

        $this->logActivity('iep.signature_saved', $iepId, "Signature saved for signatory: $signatoryId ({$sig['signatory_role']})");
        echo json_encode(['success' => true, 'message' => 'Signature saved successfully', 'path' => $path]);
        exit;
    }

    // ============================================================
    public function markSigned() {
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);

        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // IEP must be in 'signing' state to mark as signed
        if ($iep['status'] !== 'signing') {
            $_SESSION['error'] = 'IEP must be sent to signatories first before marking as signed.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Validation
        $errors = [];

        $domains     = $this->iepModel->getDomains($iepId);
        $steps       = $this->iepModel->getSteps($iepId);
        $signatories = $this->iepModel->getSignatories($iepId);
        $core        = $this->iepModel->getCore($iepId);

        if (empty($domains)) {
            $errors[] = 'At least one domain tag is required.';
        }
        if (empty($iep['re_evaluation_date'])) {
            $errors[] = 'Re-evaluation date is required.';
        }

        // At least one step fully filled
        $hasFilledStep = false;
        foreach ($steps as $step) {
            if (!empty($step['objectives']) && !empty($step['activities'])) {
                $hasFilledStep = true;
                break;
            }
        }
        if (!$hasFilledStep) {
            $errors[] = 'At least one step row must have Objectives and Activities filled.';
        }

        // At least one signatory name
        if (empty($signatories)) {
            $errors[] = 'At least one signatory name is required.';
        }

        // Print & upload: signed document required
        if ($iep['signing_method'] === 'print_upload' && empty($iep['signed_document_path'])) {
            $errors[] = 'Signed document upload is required for Print & Upload method.';
        }

        // Digital: check all sent signatories have signed
        if ($iep['signing_method'] === 'digital') {
            $unsigned = array_filter($signatories, fn($s) => empty($s['signature_image_path']));
            if (!empty($unsigned) && empty($_POST['acknowledge_pending'])) {
                $names = implode(', ', array_column($unsigned, 'signatory_name'));
                $errors[] = "The following signatories have not yet signed digitally: $names. Check 'Acknowledge pending signatures' to proceed anyway.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['iep_errors'] = $errors;
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Mark signed + locked
        $this->iepModel->markSigned($iepId);

        // Send copies to Guidance, Principal, Parent
        $this->sendSignedCopies($iepId, $iep);

        // Notify SPED Teacher that Process 6 is unlocked
        $this->notifModel->create(
            $this->userId,
            'process6_unlocked',
            'IEP Signed ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â Process 6 Unlocked',
            "The IEP for {$iep['student_name']} has been signed. You can now implement the IEP (Process 6).",
            json_encode(['iep_id' => $iepId, 'student_id' => $iep['student_id']])
        );

        $this->logActivity('iep.signed', $iepId, "IEP marked as signed and locked");

        $_SESSION['success'] = 'IEP signed and locked successfully. Copies sent to all parties.';
        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    // ============================================================
    // NEW CYCLE ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â preserve old IEP, create new draft
    // ============================================================
    public function newCycle() {
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);

        if (!$iep || !in_array($iep['status'], ['signed','locked'])) {
            $_SESSION['error'] = 'Only signed/locked IEPs can start a new cycle.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Lock the old one explicitly
        $this->iepModel->update($iepId, ['status' => 'locked']);

        // Get signed PDSP (may be same or new)
        $pdsp = $this->iepModel->getSignedPDSP($iep['student_id']);
        if (!$pdsp) {
            $_SESSION['error'] = 'No signed PDSP found. Complete a new IEP meeting first.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $schoolYear = date('Y') . '-' . (date('Y') + 1);
        $newIepId   = $this->iepModel->create($iep['student_id'], $pdsp['id'], $this->userId, $schoolYear);

        // Pre-populate domains from PDSP
        $pdspDomains = $this->iepModel->getPDSPDomains($pdsp['id']);
        $domainNames = array_unique(array_column($pdspDomains, 'domain_name'));
        if (!empty($domainNames)) {
            $this->iepModel->saveDomains($newIepId, array_values($domainNames));
        }

        $this->logActivity('iep.new_cycle', $newIepId, "New IEP cycle started from IEP: $iepId");

        $_SESSION['success'] = 'New IEP cycle started. Previous IEP preserved.';
        header('Location: ' . BASE_PATH . '/iep/form/' . $newIepId);
        exit;
    }

    // ============================================================
    // ============================================================
    public function searchUsers() {
        header('Content-Type: application/json');
        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'users' => []]);
            exit;
        }
        $role   = $_GET['role']   ?? '';
        $search = $_GET['search'] ?? '';
        $users  = $this->iepModel->searchUsersByRole($role, $search);
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    // ============================================================
    // SEND DIGITAL SIGNATURE REQUEST ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â AJAX
    // ============================================================
    public function sendSignatureRequest() {
        header('Content-Type: application/json');

        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $iepId       = (int)($_POST['iep_id']       ?? 0);
        $signatoryId = (int)($_POST['signatory_id'] ?? 0);
        $recipientId = (int)($_POST['recipient_id'] ?? 0); // specific user to notify

        $sig = $this->iepModel->getSignatoryById($signatoryId);
        $iep = $this->iepModel->findById($iepId);

        if (!$sig || !$iep || $sig['iep_id'] != $iepId) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        // Do NOT change IEP status here ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â status only changes when teacher
        // explicitly marks as signed. Sending a signature request keeps draft status.

        // Find recipients ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â use specific user if provided, else find by role
        $db   = Database::getInstance()->getConnection();
        $recipients = [];

        if ($recipientId) {
            $stmt = $db->prepare("SELECT id, email, name FROM users WHERE id = :id AND status = 'active' LIMIT 1");
            $stmt->execute(['id' => $recipientId]);
            $user = $stmt->fetch();
            if ($user) $recipients[] = $user;
        } else {
            $roleMap = [
                'guidance_counselor' => 'guidance',
                'school_head'        => 'principal',
                'sned_teacher'       => 'sped_teacher',
                'teacher'            => 'sped_teacher',
                'parent_guardian'    => 'parent',
                'ilrc_supervisor'    => 'guidance',
            ];
            $dbRole = $roleMap[$sig['signatory_role']] ?? null;
            if ($dbRole) {
                $stmt = $db->prepare("SELECT id, email, name FROM users WHERE role = :role AND status = 'active' LIMIT 5");
                $stmt->execute(['role' => $dbRole]);
                $recipients = $stmt->fetchAll();
            }
        }

            foreach ($recipients as $recipient) {
                // In-system notification
                $this->notifModel->create(
                    $recipient['id'],
                    'iep_signature_request',
                    'IEP Signature Required',
                    "Please sign the IEP for {$iep['student_name']}. Click to open.",
                    json_encode(['iep_id' => $iepId, 'signatory_id' => $signatoryId])
                );

                // Email
                $link = BASE_PATH . '/iep/sign/' . $iepId . '/' . $signatoryId;
                MailHelper::sendNotification(
                    $recipient['email'],
                    $recipient['name'],
                    'IEP Signature Required ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â SPED LMS',
                    "<h2 style='color:#1e4072;'>IEP Signature Required</h2>
                     <p>Dear {$recipient['name']},</p>
                     <p>You are requested to sign the Individualized Education Plan (IEP) for
                     <strong>{$iep['student_name']}</strong>.</p>
                     <p><a href='" . getenv('APP_URL') . $link . "'
                        style='background:#a01422;color:white;padding:10px 20px;
                               text-decoration:none;border-radius:4px;display:inline-block;'>
                        Sign IEP
                     </a></p>
                     <p>Best regards,<br>SPED LMS</p>"
                );
            }

        $this->logActivity('iep.signature_requested', $iepId, "Signature request sent for signatory: $signatoryId");
        echo json_encode(['success' => true, 'message' => 'Signature request sent']);
        exit;
    }

    // ============================================================
    // SIGN PAGE -- signatory opens IEP to sign digitally
    // ============================================================
    public function signPage($iepId, $signatoryId) {
        $iep = $this->iepModel->findById($iepId);
        $sig = $this->iepModel->getSignatoryById($signatoryId);

        if (!$iep || !$sig || $sig['iep_id'] != $iepId) {
            $_SESSION['error'] = 'Invalid signature link.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        // Map system role to signatory role
        $roleMap = [
            'guidance'    => 'guidance_counselor',
            'principal'   => 'school_head',
            'parent'      => 'parent_guardian',
            'sped_teacher'=> 'sned_teacher',
        ];
        $expectedRole = $roleMap[$this->userRole] ?? null;

        // Verify this user is allowed to sign this slot
        if (!$expectedRole || $sig['signatory_role'] !== $expectedRole) {
            $_SESSION['error'] = 'You are not authorized to sign this slot.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // IEP must be in signing state
        if ($iep['status'] !== 'signing') {
            $_SESSION['error'] = 'This IEP is not ready for signing yet.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        if (!empty($sig['signature_image_path'])) {
            $_SESSION['success'] = 'You have already signed this IEP.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        $domains     = $this->iepModel->getDomains($iepId);
        $core        = $this->iepModel->getCore($iepId);
        $steps       = $this->iepModel->getSteps($iepId);
        $signatories = $this->iepModel->getSignatories($iepId);
        $studentData = $this->iepModel->getStudentAutoFill($iep['student_id']);
        $basePath    = BASE_PATH;

        require __DIR__ . '/../Views/iep/sign.php';
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function sendSignedCopies($iepId, $iep) {
        $db = Database::getInstance()->getConnection();

        // Get Guidance, Principal users
        $stmt = $db->prepare("SELECT id, email, name FROM users WHERE role IN ('guidance','principal') AND status = 'active'");
        $stmt->execute();
        $staff = $stmt->fetchAll();

        // Get Parent
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.name
            FROM users u
            JOIN enrollment_submissions es ON es.parent_id = u.id
            JOIN student_records sr ON sr.enrollment_id = es.id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $iep['student_id']]);
        $parent = $stmt->fetch();
        if ($parent) $staff[] = $parent;

        $link = BASE_PATH . '/iep/form/' . $iepId;

        foreach ($staff as $user) {
            // Record copy
            $this->iepModel->recordCopy($iepId, $user['id']);

            // In-system notification
            $this->notifModel->create(
                $user['id'],
                'iep_signed',
                'IEP Signed ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ' . $iep['student_name'],
                "The Individualized Education Plan for {$iep['student_name']} has been signed.",
                json_encode(['iep_id' => $iepId])
            );

            // Email
            MailHelper::sendNotification(
                $user['email'],
                $user['name'],
                'IEP Signed ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ' . $iep['student_name'] . ' ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â SPED LMS',
                "<h2 style='color:#1e4072;'>IEP Signed</h2>
                 <p>Dear {$user['name']},</p>
                 <p>The Individualized Education Plan (IEP) for
                 <strong>{$iep['student_name']}</strong> has been signed.</p>
                 <p><a href='" . getenv('APP_URL') . $link . "'
                    style='background:#1e4072;color:white;padding:10px 20px;
                           text-decoration:none;border-radius:4px;display:inline-block;'>
                    View IEP
                 </a></p>
                 <p>Best regards,<br>SPED LMS</p>"
            );
        }
    }

    private function logActivity($action, $recordId, $details) {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address)
                VALUES (:uid, :action, 'iep_records', :rid, :details, :ip)
            ");
            $stmt->execute([
                'uid'     => $this->userId,
                'action'  => $action,
                'rid'     => $recordId,
                'details' => $details,
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        } catch (Exception $e) {
            error_log("IEPController logActivity failed: " . $e->getMessage());
        }
    }
}
