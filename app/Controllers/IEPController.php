<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-14
// Part of: SPED LMS — IEP Controller (living IEP + form sections 1–4)

require_once __DIR__ . '/../../config/db.php';
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
    // INDEX — IEP Repository (list all IEPs per role)
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
    // CREATE — Start new IEP draft for a student
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
        if ($existing && in_array($existing['status'], ['draft'])) {
            // Resume existing draft
            header('Location: ' . BASE_PATH . '/iep/form/' . $existing['id']);
            exit;
        }

        // Create new draft
        $schoolYear = date('Y') . '-' . (date('Y') + 1);
        $iepId = $this->iepModel->create($studentId, $pdsp['id'], $this->userId, $schoolYear);

        $this->logActivity('iep.created', $iepId, "Created IEP draft for student: $studentId");

        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    // ============================================================
    // FORM — IEP (sections 1–4 + legacy upload/sign blocks)
    // ============================================================
    public function form($iepId) {
        $iep = $this->iepModel->findById($iepId);
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $role     = $this->userRole;
        $readOnly = false;

        if ($role === 'sped_teacher' || $role === 'admin') {
            if ((int) $iep['drafted_by'] !== (int) $this->userId && $role !== 'admin') {
                $_SESSION['error'] = 'You can only edit IEPs you drafted.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
            $readOnly = false;
        } elseif (in_array($role, ['guidance','principal','parent'], true)) {
            $db2 = Database::getInstance()->getConnection();
            $stmt2 = $db2->prepare("SELECT id FROM iep_copies WHERE iep_id = :iep_id AND sent_to = :user_id LIMIT 1");
            $stmt2->execute(['iep_id' => $iepId, 'user_id' => $this->userId]);
            $hasCopy = $stmt2->fetch();
            if (!$hasCopy && !in_array($iep['status'], ['signed','signing'], true)) {
                $_SESSION['error'] = 'This IEP has not been shared with you yet.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
            $readOnly = true;
        } else {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->iepModel->seedIepDomainsFromPdspIfEmpty((int) $iepId, (int) $iep['pdsp_id']);
        $iepDomains = $this->iepModel->getIepDomains((int) $iepId);
        $iepCore    = $this->iepModel->getIepCore((int) $iepId);
        if ($iepCore === null) {
            $suggest = $this->iepModel->suggestPriorityNeedsFromPdsp((int) $iep['pdsp_id']);
            $this->iepModel->upsertIepCore((int) $iepId, '', $suggest, '');
            $iepCore = $this->iepModel->getIepCore((int) $iepId);
        }

        $pdspDomainRows = $this->iepModel->getPdspDomainRows((int) $iep['pdsp_id']);

        if (!$readOnly) {
            $this->iepModel->ensureDefaultStepForIep((int) $iepId);
        }
        $this->iepModel->refreshObservationUnlockedForIep((int) $iepId);
        $iepStepsRaw = $this->iepModel->getStepsForIep((int) $iepId);
        $iepSteps = [];
        foreach ($iepStepsRaw as $s) {
            $sid = (int) $s['id'];
            $iepSteps[] = array_merge($s, [
                'lesson_plans' => $this->iepModel->getLessonPlansLinkedToStep($sid),
                'materials'    => $this->iepModel->getMaterialsLinkedToStep($sid),
            ]);
        }

        $hasStepObjective = false;
        foreach ($iepSteps as $sx) {
            if (trim((string) ($sx['step_objective'] ?? '')) !== '') {
                $hasStepObjective = true;
                break;
            }
        }

        $this->iepModel->ensureIepSignatoriesDigitalColumns();
        $signatories    = $this->iepModel->getSignatories($iepId);
        $studentData    = $this->iepModel->getStudentAutoFill($iep['student_id']);
        $linkedParent   = $this->iepModel->getLinkedParent($iep['student_id']);
        $userRole       = $role;

        $inlineSignSlot = null;
        if (($iep['status'] ?? '') === 'signing' && in_array($role, ['sped_teacher', 'parent', 'guidance', 'principal', 'admin'], true)) {
            foreach ($signatories as $sigRow) {
                $p = trim((string) ($sigRow['signature_image_path'] ?? ''));
                if ($p !== '') {
                    continue;
                }
                if ($role === 'admin') {
                    continue;
                }
                if ($this->currentUserMaySignIepSlot($iep, $sigRow)) {
                    $inlineSignSlot = $sigRow;
                    break;
                }
            }
        }

        $reevalBanner = ($iep['status'] === 'signed'
            && !empty($iep['re_evaluation_date'])
            && strtotime($iep['re_evaluation_date'] . ' 23:59:59') < time());

        $iepEditLogs = [];
        if (($iep['status'] ?? '') === 'signed' && in_array($role, ['sped_teacher', 'admin'], true)) {
            $iepEditLogs = $this->iepModel->getIepEditLogs((int) $iepId);
        }

        if (in_array($role, ['guidance','principal','parent'], true)) {
            $this->iepModel->markCopyViewed($iepId, $this->userId);
        }

        $this->logActivity('iep.viewed', $iepId, "Viewed IEP form");

        $showSigningControls = (($iep['status'] ?? '') === 'signing'
            && in_array($role, ['sped_teacher', 'admin'], true)
            && ($role === 'admin' || (int) $iep['drafted_by'] === (int) $this->userId));
        $allSignaturesCaptured = false;
        if (($iep['status'] ?? '') === 'signing') {
            $allSignaturesCaptured = $this->iepModel->allSignatoriesSignatureComplete((int) $iepId);
        }

        $basePath = BASE_PATH;
        require __DIR__ . '/../Views/iep/form_simplified.php';
    }

    /**
     * Save IEP header, domains, core fields, and re-evaluation date (Sections 1–4 payload).
     */
    public function savePartOne() {
        if (!in_array($this->userRole, ['sped_teacher','admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iepId = (int) ($_POST['iep_id'] ?? 0);
        if ($iepId <= 0) {
            $_SESSION['error'] = 'Invalid IEP.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iep = $this->iepModel->findById($iepId);
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        if ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin') {
            $_SESSION['error'] = 'You can only edit IEPs you drafted.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $wasSigned = (($iep['status'] ?? '') === 'signed');
        $snapCore  = $wasSigned ? $this->iepModel->getIepCore($iepId) : null;
        $snapDomains = $wasSigned ? array_column($this->iepModel->getIepDomains($iepId), 'domain_name') : [];

        try {
            $this->iepModel->ensurePartOneSaveSchema();

            $schoolYear = trim((string) ($_POST['school_year'] ?? ''));
            if ($schoolYear === '') {
                throw new \InvalidArgumentException('School year is required.');
            }

            $reEval = trim((string) ($_POST['re_evaluation_date'] ?? ''));
            $reEvalSql = null;
            if ($reEval !== '') {
                $dt = \DateTime::createFromFormat('Y-m-d', $reEval);
                if (!$dt || $dt->format('Y-m-d') !== $reEval) {
                    throw new \InvalidArgumentException('Please enter a valid re-evaluation date (use the date picker).');
                }
                $reEvalSql = $reEval;
            }

            $domainNames = [];
            if (!empty($_POST['domain_names_json'])) {
                $decoded = json_decode((string) $_POST['domain_names_json'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $d) {
                        if (is_string($d) && trim($d) !== '') {
                            $domainNames[] = trim($d);
                        }
                    }
                }
            }

            $this->iepModel->update($iepId, [
                'school_year'           => $schoolYear,
                're_evaluation_date'    => $reEvalSql,
                'header_learner_name'   => $this->nullableTrim($_POST['header_learner_name'] ?? null),
                'header_learner_age'    => $this->nullableTrim($_POST['header_learner_age'] ?? null),
                'header_lrn'            => $this->nullableTrim($_POST['header_lrn'] ?? null),
                'header_section'        => $this->nullableTrim($_POST['header_section'] ?? null),
                'header_teacher_name'   => $this->nullableTrim($_POST['header_teacher_name'] ?? null),
                'header_school_name'    => $this->nullableTrim($_POST['header_school_name'] ?? null),
                'header_grade_level'    => $this->nullableTrim($_POST['header_grade_level'] ?? null),
            ]);

            $this->iepModel->replaceIepDomains($iepId, $domainNames);

            $newDd = $this->nullableTrim($_POST['developmental_domain'] ?? null);
            if ($newDd === null || $newDd === '') {
                $newDd = implode('; ', $domainNames);
            }
            $newPn = $this->nullableTrim($_POST['priority_needs'] ?? null);
            $newTo = $this->nullableTrim($_POST['terminal_objectives'] ?? null);
            $this->iepModel->upsertIepCore($iepId, $newDd, $newPn, $newTo);

            if ($wasSigned) {
                $this->appendSignedIepEditLogs(
                    $iepId,
                    $iep,
                    $snapCore,
                    $snapDomains,
                    $schoolYear,
                    $reEvalSql,
                    $domainNames,
                    $newDd,
                    $newPn,
                    $newTo
                );
            }

            $_SESSION['success'] = 'IEP details saved.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('IEPController::savePartOne: ' . $e->getMessage());
            $hint = 'Could not save IEP details. Please try again.';
            if (stripos($e->getMessage(), 'Unknown column') !== false
                || stripos($e->getMessage(), "doesn't exist") !== false) {
                $hint = 'Database is missing required tables or columns. Ask an administrator to run migrations, then try again.';
            }
            $_SESSION['error'] = $hint;
        }

        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    /**
     * Save IEP steps table (Section 5) — JSON payload in steps_json (AJAX).
     */
    public function saveSteps() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher','admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        $iepId = (int) ($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);
        if (!$iep || ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'IEP not found or access denied.']);
            exit;
        }
        $raw = $_POST['steps_json'] ?? '[]';
        $rows = json_decode((string) $raw, true);
        if (!is_array($rows) || count($rows) < 1) {
            echo json_encode(['success' => false, 'message' => 'At least one IEP step row is required.']);
            exit;
        }
        if (count($rows) > 10) {
            echo json_encode(['success' => false, 'message' => 'A maximum of 10 steps is allowed.']);
            exit;
        }
        try {
            $this->iepModel->syncIepStepsFromPayload($iepId, $rows);
            $stepsOut = $this->iepModel->getStepsForIep($iepId);
            $stepBrief = [];
            foreach ($stepsOut as $r) {
                $stepBrief[] = [
                    'id'          => (int) $r['id'],
                    'step_number' => (int) $r['step_number'],
                ];
            }
            echo json_encode(['success' => true, 'steps' => $stepBrief]);
        } catch (\Throwable $e) {
            error_log('IEPController::saveSteps: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage() ?: 'Could not save IEP steps.']);
        }
        exit;
    }

    /**
     * AJAX — create lesson plan from IEP step drawer (Process 6 tables + junction).
     */
    public function createLessonPlanForStep() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher','admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $body = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $iepId = (int) ($body['iep_id'] ?? 0);
        $stepId = (int) ($body['step_id'] ?? 0);
        $title = trim((string) ($body['title'] ?? ''));
        $domainUi = trim((string) ($body['domain'] ?? ''));
        $assignmentType = trim((string) ($body['assignment_type'] ?? 'individual'));
        $targetStepNumber = (int) ($body['target_step_number'] ?? 0);
        $stepsPayloadRaw = $body['steps_json'] ?? '';

        if ($iepId <= 0 || $title === '') {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }

        $iep = $this->iepModel->findById($iepId);
        if (!$iep || ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'IEP not found or access denied.']);
            exit;
        }

        if ($stepId <= 0) {
            $rows = json_decode((string) $stepsPayloadRaw, true);
            if (!is_array($rows) || count($rows) < 1 || $targetStepNumber < 1 || $targetStepNumber > 10) {
                echo json_encode(['success' => false, 'message' => 'Step table data is missing or invalid for this action.']);
                exit;
            }
            if (count($rows) > 10) {
                echo json_encode(['success' => false, 'message' => 'A maximum of 10 steps is allowed.']);
                exit;
            }
            try {
                $this->iepModel->syncIepStepsFromPayload($iepId, $rows);
            } catch (\Throwable $e) {
                error_log('createLessonPlanForStep sync: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage() ?: 'Could not save steps.']);
                exit;
            }
            $stepId = $this->iepModel->findStepIdByIepAndStepNumber($iepId, $targetStepNumber);
            if ($stepId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Could not resolve the step row.']);
                exit;
            }
        }

        if (!in_array($assignmentType, ['individual', 'shared'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid assignment type.']);
            exit;
        }

        if (!$this->iepModel->stepBelongsToIep($stepId, $iepId)) {
            echo json_encode(['success' => false, 'message' => 'Invalid step for this IEP.']);
            exit;
        }

        if ($domainUi === '') {
            $chips = $this->iepModel->getIepDomains($iepId);
            $domainUi = trim((string) (($chips[0] ?? [])['domain_name'] ?? ''));
        }
        $pdspDomain = $this->mapIepDrawerDomainToLessonPlanEnum($domainUi);
        if ($pdspDomain === '') {
            $pdspDomain = 'communication_language';
        }

        require_once __DIR__ . '/../Models/LessonPlanModel.php';
        $lpModel = new LessonPlanModel();
        $studentId = ($assignmentType === 'individual') ? (int) $iep['student_id'] : null;

        try {
            $lpId = (int) $lpModel->create([
                'iep_id'          => $iepId,
                'student_id'      => $studentId,
                'created_by'      => $this->userId,
                'title'           => $title,
                'pdsp_domain'     => $pdspDomain,
                'assignment_type' => $assignmentType,
                'document_path'   => null,
            ]);
            if ($assignmentType === 'individual' && $studentId) {
                $lpModel->assignToStudent($lpId, $studentId, $this->userId);
            }
            $this->iepModel->linkLessonPlanToStep($stepId, $lpId);
            $this->iepModel->refreshObservationUnlockedForIep($iepId);
            echo json_encode(['success' => true, 'lesson_plan_id' => $lpId, 'title' => $title]);
        } catch (\Throwable $e) {
            error_log('createLessonPlanForStep: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Could not create lesson plan.']);
        }
        exit;
    }

    /**
     * AJAX — remove lesson plan link from step (junction only).
     */
    public function unlinkLessonPlanFromStep() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher','admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $body = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $iepId = (int) ($body['iep_id'] ?? 0);
        $stepId = (int) ($body['step_id'] ?? 0);
        $lessonPlanId = (int) ($body['lesson_plan_id'] ?? 0);
        if ($iepId <= 0 || $stepId <= 0 || $lessonPlanId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            exit;
        }
        $iep = $this->iepModel->findById($iepId);
        if (!$iep || ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        if (!$this->iepModel->stepBelongsToIep($stepId, $iepId)) {
            echo json_encode(['success' => false, 'message' => 'Invalid step.']);
            exit;
        }
        $this->iepModel->unlinkLessonPlanFromStep($stepId, $lessonPlanId);
        $this->iepModel->refreshObservationUnlockedForIep($iepId);
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * AJAX (GET) — lesson plans for this IEP that can be linked to a step (excludes already linked on that step).
     */
    public function lessonPlansForIepStepJson() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $iepId  = (int) ($_GET['iep_id'] ?? 0);
        $stepId = (int) ($_GET['step_id'] ?? 0);
        if ($iepId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid IEP.']);
            exit;
        }
        $iep = $this->iepModel->findById($iepId);
        if (!$iep || ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'IEP not found or access denied.']);
            exit;
        }
        require_once __DIR__ . '/../Models/LessonPlanModel.php';
        $lpModel = new LessonPlanModel();
        $rows    = $lpModel->getByIepId($iepId);
        $exclude = [];
        if ($stepId > 0 && $this->iepModel->stepBelongsToIep($stepId, $iepId)) {
            foreach ($this->iepModel->getLessonPlansLinkedToStep($stepId) as $r) {
                $exclude[(int) ($r['id'] ?? 0)] = true;
            }
        }
        $domainLabels = [
            'perceptuo_cognitive'    => 'Perceptuo-Cognitive',
            'psychosocial'           => 'Psychosocial',
            'socio_emotional'        => 'Socio-Emotional',
            'psychomotor'            => 'Psychomotor',
            'daily_living_skills'    => 'Daily Living Skills',
            'communication_language' => 'Communication & Language',
        ];
        $out = [];
        foreach ($rows as $lp) {
            $id = (int) ($lp['id'] ?? 0);
            if ($id <= 0 || !empty($exclude[$id])) {
                continue;
            }
            $dom = (string) ($lp['pdsp_domain'] ?? '');
            $out[] = [
                'id'              => $id,
                'title'           => (string) ($lp['title'] ?? ''),
                'status'          => (string) ($lp['status'] ?? ''),
                'pdsp_domain'     => $dom,
                'domain_label'    => $domainLabels[$dom] ?? ucwords(str_replace('_', ' ', $dom)),
                'document_path'   => (string) ($lp['document_path'] ?? ''),
            ];
        }
        echo json_encode(['success' => true, 'lesson_plans' => $out]);
        exit;
    }

    /**
     * AJAX — link an existing Process 6 lesson plan (same IEP) to this step via iep_step_lesson_plans.
     */
    public function linkExistingLessonPlanToStep() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $body = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $iepId          = (int) ($body['iep_id'] ?? 0);
        $stepId         = (int) ($body['step_id'] ?? 0);
        $lessonPlanId   = (int) ($body['lesson_plan_id'] ?? 0);
        $targetStepNumber = (int) ($body['target_step_number'] ?? 0);
        $stepsPayloadRaw = $body['steps_json'] ?? '';

        if ($iepId <= 0 || $lessonPlanId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing IEP or lesson plan.']);
            exit;
        }

        $iep = $this->iepModel->findById($iepId);
        if (!$iep || ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'IEP not found or access denied.']);
            exit;
        }

        if ($stepId <= 0) {
            $rows = json_decode((string) $stepsPayloadRaw, true);
            if (!is_array($rows) || count($rows) < 1 || $targetStepNumber < 1 || $targetStepNumber > 10) {
                echo json_encode(['success' => false, 'message' => 'Step table data is missing or invalid for this action.']);
                exit;
            }
            if (count($rows) > 10) {
                echo json_encode(['success' => false, 'message' => 'A maximum of 10 steps is allowed.']);
                exit;
            }
            try {
                $this->iepModel->syncIepStepsFromPayload($iepId, $rows);
            } catch (\Throwable $e) {
                error_log('linkExistingLessonPlanToStep sync: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage() ?: 'Could not save steps.']);
                exit;
            }
            $stepId = $this->iepModel->findStepIdByIepAndStepNumber($iepId, $targetStepNumber);
            if ($stepId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Could not resolve the step row.']);
                exit;
            }
        }

        if (!$this->iepModel->stepBelongsToIep($stepId, $iepId)) {
            echo json_encode(['success' => false, 'message' => 'Invalid step for this IEP.']);
            exit;
        }

        require_once __DIR__ . '/../Models/LessonPlanModel.php';
        $lpModel = new LessonPlanModel();
        $lp      = $lpModel->findById($lessonPlanId);
        if (!$lp || (int) ($lp['iep_id'] ?? 0) !== $iepId) {
            echo json_encode(['success' => false, 'message' => 'Lesson plan not found or does not belong to this IEP.']);
            exit;
        }

        try {
            $this->iepModel->linkLessonPlanToStep($stepId, $lessonPlanId);
            $this->iepModel->refreshObservationUnlockedForIep($iepId);
            echo json_encode([
                'success'         => true,
                'lesson_plan_id'  => $lessonPlanId,
                'title'           => (string) ($lp['title'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            error_log('linkExistingLessonPlanToStep: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Could not link lesson plan.']);
        }
        exit;
    }

    /**
     * JSON — read-only Process 7 submission summary for a step (reference panel).
     */
    public function stepProgress($stepId) {
        header('Content-Type: application/json; charset=utf-8');
        $stepId = (int) $stepId;
        $iepId = (int) ($_GET['iep_id'] ?? 0);
        if ($stepId <= 0 || $iepId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        $iep = $this->iepModel->findById($iepId);
        if (!$iep) {
            echo json_encode(['success' => false, 'message' => 'IEP not found']);
            exit;
        }
        $role = $this->userRole;
        if (!in_array($role, ['sped_teacher','admin','guidance','principal','parent'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        if (!$this->iepModel->stepBelongsToIep($stepId, $iepId)) {
            echo json_encode(['success' => false, 'message' => 'Invalid step']);
            exit;
        }
        if (in_array($role, ['guidance','principal','parent'], true)) {
            // read-only viewers: allow summary only
        }
        $rows = $this->iepModel->getProgressSubmissionsForStep($stepId, (int) $iep['student_id']);
        echo json_encode(['success' => true, 'rows' => $rows]);
        exit;
    }

    private function mapIepDrawerDomainToLessonPlanEnum(string $ui): string {
        $k = strtolower(str_replace([' ', '-'], ['_', '_'], $ui));
        $map = [
            'communication'           => 'communication_language',
            'communication_language' => 'communication_language',
            'communication_and_language' => 'communication_language',
            'daily_living_skills'     => 'daily_living_skills',
            'daily living skills'     => 'daily_living_skills',
            'motor_skills'            => 'psychomotor',
            'psychomotor'             => 'psychomotor',
            'social_emotional'        => 'socio_emotional',
            'social-emotional'        => 'socio_emotional',
            'socio_emotional'         => 'socio_emotional',
            'academic'                => 'perceptuo_cognitive',
            'perceptuo_cognitive'     => 'perceptuo_cognitive',
            'perceptuo__cognitive'    => 'perceptuo_cognitive',
            'vocational'              => 'psychosocial',
            'psychosocial'            => 'psychosocial',
        ];
        return $map[$k] ?? '';
    }

    private function nullableTrim($v): ?string {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    }

    // ============================================================
    // UPLOAD IEP DOCUMENT — AJAX (simplified upload)
    // ============================================================
    public function upload() {
        header('Content-Type: application/json');

        if (!in_array($this->userRole, ['sped_teacher','admin'])) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        if (!$iepId || !isset($_FILES['iep_document'])) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $iep = $this->iepModel->findById($iepId);
        if (!$iep || $iep['status'] === 'signed') {
            echo json_encode(['success' => false, 'message' => 'This IEP cannot accept a new upload in its current state.']);
            exit;
        }

        $file = $_FILES['iep_document'];
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

        // Create student-specific directory
        $uploadDir = __DIR__ . '/../../public/uploads/iep/' . $iep['student_id'] . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'iep_' . $iepId . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
            exit;
        }

        $path = 'uploads/iep/' . $iep['student_id'] . '/' . $filename;
        $this->iepModel->update($iepId, ['signed_document_path' => $path]);
        
        $this->logActivity('iep.document_uploaded', $iepId, 'IEP document uploaded: ' . $file['name']);

        echo json_encode([
            'success'  => true,
            'message'  => 'Document uploaded successfully',
            'filename' => $file['name'],
            'path'     => $path,
            'fileType' => $ext,
            'fileSize' => round($file['size'] / 1024, 1) . ' KB'
        ]);
        exit;
    }

    // ============================================================
    // SUBMIT IEP — Final submission with validation
    // ============================================================
    public function submitIEP() {
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

        if ($iep['status'] !== 'draft') {
            $_SESSION['error'] = 'Only draft IEPs can be submitted.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Validation (living IEP — no signed file required)
        $errors = [];

        $domainRows = $this->iepModel->getIepDomains($iepId);
        if (empty($domainRows)) {
            $errors[] = 'Add at least one developmental domain (Section 3) before marking the IEP as signed.';
        }

        $reEvalDate = trim((string) ($_POST['re_evaluation_date'] ?? ''));
        if ($reEvalDate === '') {
            $errors[] = 'Re-evaluation date is required (Section 4).';
        }

        $steps = $this->iepModel->getStepsForIep($iepId);
        $hasObjective = false;
        foreach ($steps as $st) {
            if (trim((string) ($st['step_objective'] ?? '')) !== '') {
                $hasObjective = true;
                break;
            }
        }
        if (!$hasObjective) {
            $errors[] = 'At least one IEP step must have a step objective filled in (Section 5).';
        }

        // At least one signatory
        $signatoryRoles = ['parent_guardian', 'guidance_counselor', 'teacher', 'sned_teacher', 'school_head', 'ilrc_supervisor'];
        $hasSignatory = false;
        foreach ($signatoryRoles as $role) {
            if (!empty($_POST['signatory_' . $role]) && !empty($_POST['signatory_name_' . $role])) {
                $hasSignatory = true;
                break;
            }
        }
        if (!$hasSignatory) {
            $errors[] = 'At least one signatory is required.';
        }

        // Save signatories payload
        $signatories = [];
        foreach ($signatoryRoles as $role) {
            if (!empty($_POST['signatory_' . $role]) && !empty($_POST['signatory_name_' . $role])) {
                $signatories[] = [
                    'role' => $role,
                    'name' => trim($_POST['signatory_name_' . $role]),
                ];
            }
        }

        $finalizeMode = trim((string) ($_POST['iep_finalize_mode'] ?? 'meeting_record'));
        if ($finalizeMode !== 'digital_collect') {
            $finalizeMode = 'meeting_record';
        }

        if ($finalizeMode === 'digital_collect') {
            $eligibleDigital = false;
            foreach (['parent_guardian', 'guidance_counselor', 'school_head', 'sned_teacher'] as $r) {
                if (!empty($_POST['signatory_' . $r]) && !empty($_POST['signatory_name_' . $r])) {
                    $eligibleDigital = true;
                    break;
                }
            }
            if (!$eligibleDigital) {
                $errors[] = 'For in-app digital signatures, include at least one of: Parent, Guidance, School Head, or SNEd Teacher (those slots receive a sign link).';
            }

            $db = Database::getInstance()->getConnection();
            foreach ($signatories as $s) {
                if ($s['role'] !== 'parent_guardian') {
                    continue;
                }
                $par = $this->iepModel->getLinkedParent($iep['student_id']);
                if (!$par) {
                    $errors[] = 'Digital signing with a Parent slot requires a linked parent account on the student enrollment.';
                    break;
                }
            }
            foreach ($signatories as $s) {
                if ($s['role'] !== 'guidance_counselor') {
                    continue;
                }
                $c = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'guidance' AND status = 'active'")->fetchColumn();
                if ($c < 1) {
                    $errors[] = 'No active Guidance user exists to receive the IEP sign request.';
                    break;
                }
            }
            foreach ($signatories as $s) {
                if ($s['role'] !== 'school_head') {
                    continue;
                }
                $c = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'principal' AND status = 'active'")->fetchColumn();
                if ($c < 1) {
                    $errors[] = 'No active Principal user exists to receive the School Head sign request.';
                    break;
                }
            }
        }

        // Optional: scan/photo of paper-signed Part III (meeting record path only)
        if ($finalizeMode === 'meeting_record') {
            $mf = $_FILES['meeting_signing_proof'] ?? null;
            if ($mf && ($mf['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (($mf['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $errors[] = 'Face-to-face signing proof could not be read. Try a smaller file or a different format.';
                } elseif (($mf['size'] ?? 0) > 10 * 1024 * 1024) {
                    $errors[] = 'Signing proof must be 10MB or smaller.';
                } else {
                    $ext = strtolower(pathinfo((string) ($mf['name'] ?? ''), PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
                        $errors[] = 'Signing proof must be a JPG, PNG, or PDF file.';
                    }
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['iep_errors'] = $errors;
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        $this->iepModel->update($iepId, ['re_evaluation_date' => $reEvalDate]);

        if ($finalizeMode === 'meeting_record') {
            $proofPath = null;
            $mf = $_FILES['meeting_signing_proof'] ?? null;
            if ($mf && ($mf['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && !empty($mf['tmp_name'])) {
                $ext = strtolower(pathinfo((string) ($mf['name'] ?? ''), PATHINFO_EXTENSION));
                $uploadDir = __DIR__ . '/../../public/uploads/iep/' . (int) $iep['student_id'] . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = 'iep_meeting_proof_' . $iepId . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($mf['tmp_name'], $uploadDir . $filename)) {
                    $_SESSION['error'] = 'Could not save the signing proof file. Please try again.';
                    header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
                    exit;
                }
                $proofPath = 'uploads/iep/' . (int) $iep['student_id'] . '/' . $filename;
            }
            $this->iepModel->saveSignatories($iepId, $signatories);
            if ($proofPath !== null) {
                $this->iepModel->update($iepId, ['signed_document_path' => $proofPath]);
            }
            $this->iepModel->markSigned($iepId, 'print_upload');
            $this->sendSignedCopies($iepId, $iep);
            $this->notifyProcess6Unlocked($iepId, $iep);
            $this->logActivity('iep.signed', $iepId, 'IEP marked as signed (meeting record)');
            $_SESSION['success'] = 'IEP marked as signed (meeting record). Guidance, Principal, and Parent have been notified.'
                . ($proofPath ? ' Signing proof was saved.' : '');
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Digital collect: pending slots + f2f for teacher / ILRC
        $now = date('Y-m-d H:i:s');
        $pendingRoles = ['parent_guardian', 'guidance_counselor', 'school_head', 'sned_teacher'];
        $rows = [];
        foreach ($signatories as $s) {
            if (in_array($s['role'], $pendingRoles, true)) {
                $rows[] = [
                    'role'                 => $s['role'],
                    'name'                 => $s['name'],
                    'send_status'          => 'pending',
                    'signature_image_path' => null,
                    'signed_at'            => null,
                ];
            } else {
                $rows[] = [
                    'role'                 => $s['role'],
                    'name'                 => $s['name'],
                    'send_status'          => 'signed',
                    'signature_image_path' => 'f2f_signed',
                    'signed_at'            => $now,
                ];
            }
        }
        $this->iepModel->replaceSignatoryRows($iepId, $rows);

        $hasPending = false;
        foreach ($rows as $rw) {
            if (($rw['send_status'] ?? '') === 'pending') {
                $hasPending = true;
                break;
            }
        }

        if (!$hasPending) {
            $this->iepModel->markSigned($iepId, 'digital');
            $this->sendSignedCopies($iepId, $iep);
            $this->notifyProcess6Unlocked($iepId, $iep);
            $this->logActivity('iep.signed', $iepId, 'IEP marked as signed (digital, no pending slots)');
            $_SESSION['success'] = 'IEP marked as signed. All signatory slots were completed on file; Guidance, Principal, and Parent have been notified.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        $this->iepModel->update($iepId, ['status' => 'signing', 'signing_method' => 'digital']);
        $iepFresh = $this->iepModel->findById($iepId);
        $this->sendDigitalSignatureInvites($iepId, $iepFresh);

        $this->logActivity('iep.signing_started', $iepId, 'IEP sent for digital signatures');
        $_SESSION['success'] = 'IEP is now in signing. Invited roles were notified in-app (and by email when available). Open this page to copy sign links or finalize when every signature is captured.';
        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    /**
     * Signatory canvas page (Process 5 — digital path).
     */
    public function signPage($iepId, $signatoryId) {
        $iepId = (int) $iepId;
        $signatoryId = (int) $signatoryId;
        $iep = $this->iepModel->findById($iepId);
        $sig = $this->iepModel->getSignatoryById($signatoryId);

        if (!$iep || !$sig || (int) $sig['iep_id'] !== $iepId) {
            $_SESSION['error'] = 'Invalid signature link.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        if (($iep['status'] ?? '') !== 'signing') {
            $_SESSION['error'] = 'This IEP is not open for digital signing.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        if (!$this->currentUserMaySignIepSlot($iep, $sig)) {
            $_SESSION['error'] = 'You are not authorized to sign this signatory slot.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        if (!empty($sig['signature_image_path'])) {
            $_SESSION['success'] = 'This slot already has a signature on file.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        $domains     = $this->iepModel->getIepDomains($iepId);
        $signatories = $this->iepModel->getSignatories($iepId);
        $studentData = $this->iepModel->getStudentAutoFill($iep['student_id']);
        $basePath    = BASE_PATH;

        require __DIR__ . '/../Views/iep/sign.php';
    }

    /**
     * AJAX — save canvas signature PNG for one signatory row.
     */
    public function saveSignature() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $iepId        = (int) ($_POST['iep_id'] ?? 0);
        $signatoryId  = (int) ($_POST['signatory_id'] ?? 0);
        $signatureB64 = (string) ($_POST['signature_data'] ?? '');

        if (!$iepId || !$signatoryId || $signatureB64 === '') {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $sig = $this->iepModel->getSignatoryById($signatoryId);
        $iep = $this->iepModel->findById($iepId);
        if (!$sig || !$iep || (int) $sig['iep_id'] !== $iepId) {
            echo json_encode(['success' => false, 'message' => 'Invalid signatory']);
            exit;
        }

        if (!$this->currentUserMaySignIepSlot($iep, $sig)) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to sign this slot']);
            exit;
        }

        if (($iep['status'] ?? '') !== 'signing') {
            echo json_encode(['success' => false, 'message' => 'IEP is not accepting new digital signatures right now']);
            exit;
        }

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureB64));
        if ($imageData === false || $imageData === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid image data']);
            exit;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/signatures/iep/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'sig_iep' . $iepId . '_' . $signatoryId . '_' . time() . '.png';
        if (file_put_contents($uploadDir . $filename, $imageData) === false) {
            echo json_encode(['success' => false, 'message' => 'Could not save file']);
            exit;
        }

        $path = 'uploads/signatures/iep/' . $filename;
        $this->iepModel->saveSignatureImage($signatoryId, $path);

        $this->logActivity('iep.signature_saved', $iepId, 'Signature saved for signatory ' . $signatoryId);
        echo json_encode(['success' => true, 'message' => 'Signature saved successfully', 'path' => $path]);
        exit;
    }

    /**
     * After all digital slots are signed, SPED teacher finalizes (same checks as submit).
     */
    public function finalizeDigitalIep() {
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $iepId = (int) ($_POST['iep_id'] ?? 0);
        $iep   = $this->iepModel->findById($iepId);
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        if ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin') {
            $_SESSION['error'] = 'You can only finalize IEPs you drafted.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        if (($iep['status'] ?? '') !== 'signing') {
            $_SESSION['error'] = 'This IEP is not waiting for digital completion.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        $errors = [];
        $domainRows = $this->iepModel->getIepDomains($iepId);
        if (empty($domainRows)) {
            $errors[] = 'Add at least one developmental domain (Section 3) before finalizing.';
        }
        if (trim((string) ($iep['re_evaluation_date'] ?? '')) === '') {
            $errors[] = 'Re-evaluation date is required (Section 4).';
        }
        $steps = $this->iepModel->getStepsForIep($iepId);
        $hasObjective = false;
        foreach ($steps as $st) {
            if (trim((string) ($st['step_objective'] ?? '')) !== '') {
                $hasObjective = true;
                break;
            }
        }
        if (!$hasObjective) {
            $errors[] = 'At least one IEP step must have a step objective (Section 5).';
        }
        if (!$this->iepModel->allSignatoriesSignatureComplete($iepId)) {
            $errors[] = 'Every signatory row must have a signature (canvas) or an on-file attestation before you can finalize.';
        }

        if (!empty($errors)) {
            $_SESSION['iep_errors'] = $errors;
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        $this->iepModel->markSigned($iepId, 'digital');
        $this->sendSignedCopies($iepId, $iep);
        $this->notifyProcess6Unlocked($iepId, $iep);

        $this->logActivity('iep.signed', $iepId, 'IEP finalized after digital signatures');
        $_SESSION['success'] = 'IEP is now fully signed. Guidance, Principal, and Parent have been notified.';
        header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
        exit;
    }

    /**
     * Hard-delete a draft IEP (POST).
     */
    public function deleteDraft($iepId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }
        $iepId = (int) $iepId;
        $ok    = $this->iepModel->deleteDraftIep($iepId, (int) $this->userId, $this->userRole === 'admin');
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Draft IEP deleted permanently.' : 'Could not delete this draft (not found, not a draft, or not yours).';
        header('Location: ' . BASE_PATH . '/iep');
        exit;
    }

    /**
     * JSON — students eligible for a new IEP (signed PDSP, no current-year draft).
     */
    public function eligibleStudentsJson() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $rows = $this->iepModel->getEligibleStudents($this->userId);
        echo json_encode(['success' => true, 'students' => $rows]);
        exit;
    }

    /**
     * Printable IEP layout (DepEd-style, no app chrome).
     */
    public function printForm($iepId) {
        $iepId = (int) $iepId;
        $iep   = $this->iepModel->findById($iepId);
        if (!$iep) {
            $_SESSION['error'] = 'IEP not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }
        $role = $this->userRole;
        if ($role === 'sped_teacher' || $role === 'admin') {
            if ((int) $iep['drafted_by'] !== (int) $this->userId && $role !== 'admin') {
                $_SESSION['error'] = 'You can only print IEPs you drafted.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
        } elseif (in_array($role, ['guidance', 'principal', 'parent'], true)) {
            $db2  = Database::getInstance()->getConnection();
            $stmt = $db2->prepare('SELECT id FROM iep_copies WHERE iep_id = :iep_id AND sent_to = :user_id LIMIT 1');
            $stmt->execute(['iep_id' => $iepId, 'user_id' => $this->userId]);
            $hasCopy = $stmt->fetch();
            if (!$hasCopy && !in_array($iep['status'], ['signed', 'signing'], true)) {
                $_SESSION['error'] = 'This IEP has not been shared with you yet.';
                header('Location: ' . BASE_PATH . '/iep');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/dashboard');
            exit;
        }

        $this->iepModel->seedIepDomainsFromPdspIfEmpty((int) $iepId, (int) $iep['pdsp_id']);
        $iepDomains = $this->iepModel->getIepDomains((int) $iepId);
        $iepCore    = $this->iepModel->getIepCore((int) $iepId) ?: ['developmental_domain' => '', 'priority_needs' => '', 'terminal_objectives' => ''];
        $iepSteps   = $this->iepModel->getStepsForIep((int) $iepId);
        foreach ($iepSteps as &$s) {
            $sid = (int) $s['id'];
            $s['lesson_plans'] = $this->iepModel->getLessonPlansLinkedToStep($sid);
        }
        unset($s);
        $signatories = $this->iepModel->getSignatories($iepId);
        $studentData = $this->iepModel->getStudentAutoFill($iep['student_id']);
        $basePath    = BASE_PATH;

        require __DIR__ . '/../Views/iep/print_form.php';
        exit;
    }

    /**
     * Upload a document onto an existing lesson plan from the IEP form drawer (multipart).
     */
    public function uploadLessonPlanDocForIep() {
        header('Content-Type: application/json; charset=utf-8');
        if (!in_array($this->userRole, ['sped_teacher', 'admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $iepId        = (int) ($_POST['iep_id'] ?? 0);
        $lessonPlanId = (int) ($_POST['lesson_plan_id'] ?? 0);
        if ($iepId <= 0 || $lessonPlanId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            exit;
        }
        $iep = $this->iepModel->findById($iepId);
        if (!$iep || ((int) $iep['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            exit;
        }
        require_once __DIR__ . '/../Models/LessonPlanModel.php';
        $lpModel = new LessonPlanModel();
        $lp      = $lpModel->findById($lessonPlanId);
        if (!$lp || (int) ($lp['iep_id'] ?? 0) !== $iepId) {
            echo json_encode(['success' => false, 'message' => 'Lesson plan not found for this IEP.']);
            exit;
        }
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
            exit;
        }
        $file = $_FILES['document'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed.']);
            exit;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File must be under 10MB.']);
            exit;
        }
        $studentId = (int) ($lp['student_id'] ?? $iep['student_id']);
        $uploadDir = __DIR__ . '/../../public/uploads/lesson_plans/' . $studentId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = 'lp_' . $lessonPlanId . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
            exit;
        }
        $relativePath = 'uploads/lesson_plans/' . $studentId . '/' . $fileName;
        $lpModel->update($lessonPlanId, ['document_path' => $relativePath]);
        echo json_encode(['success' => true, 'path' => $relativePath]);
        exit;
    }

    // ============================================================
    // DOWNLOAD DOCUMENT — Secure file serving
    // ============================================================
    public function downloadDocument($iepId) {
        $iep = $this->iepModel->findById($iepId);
        if (!$iep || empty($iep['signed_document_path'])) {
            $_SESSION['error'] = 'Document not found.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        // Check access permissions
        $role = $this->userRole;
        $hasAccess = false;

        if (in_array($role, ['sped_teacher', 'guidance', 'principal', 'admin'])) {
            $hasAccess = true;
        } elseif ($role === 'parent') {
            $hasAccess = false;
        }

        if (!$hasAccess) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . BASE_PATH . '/iep');
            exit;
        }

        $filePath = __DIR__ . '/../../public/' . $iep['signed_document_path'];
        if (!file_exists($filePath)) {
            $_SESSION['error'] = 'File not found on server.';
            header('Location: ' . BASE_PATH . '/iep/form/' . $iepId);
            exit;
        }

        // Serve file
        $filename = basename($filePath);
        $mimeType = mime_content_type($filePath);
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function notifyProcess6Unlocked(int $iepId, array $iep): void {
        $this->notifModel->create(
            $this->userId,
            'process6_unlocked',
            'IEP Signed — Process 6 Unlocked',
            "The IEP for {$iep['student_name']} has been signed. You can now implement the IEP (Process 6).",
            json_encode(['iep_id' => $iepId, 'student_id' => $iep['student_id']])
        );
    }

    /**
     * Notify users who have a pending canvas slot (parent, guidance, principal, SNEd drafter).
     */
    private function sendDigitalSignatureInvites(int $iepId, array $iep): void {
        $db    = Database::getInstance()->getConnection();
        $appUrl = rtrim((string) (getenv('APP_URL') ?: ''), '/');
        $base   = ($appUrl !== '' ? $appUrl : '') . BASE_PATH;

        foreach ($this->iepModel->getSignatories($iepId) as $sig) {
            if (($sig['send_status'] ?? '') !== 'pending') {
                continue;
            }
            if (!empty($sig['signature_image_path'])) {
                continue;
            }
            $role = $sig['signatory_role'] ?? '';
            $recipients = [];
            if ($role === 'parent_guardian') {
                $p = $this->iepModel->getLinkedParent($iep['student_id']);
                if ($p) {
                    $recipients[] = $p;
                }
            } elseif ($role === 'guidance_counselor') {
                $stmt = $db->query("SELECT id, email, name FROM users WHERE role = 'guidance' AND status = 'active' LIMIT 25");
                $recipients = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            } elseif ($role === 'school_head') {
                $stmt = $db->query("SELECT id, email, name FROM users WHERE role = 'principal' AND status = 'active' LIMIT 25");
                $recipients = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            } elseif ($role === 'sned_teacher') {
                $stmt = $db->prepare("SELECT id, email, name FROM users WHERE id = ? AND status = 'active' LIMIT 1");
                $stmt->execute([(int) $iep['drafted_by']]);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($u) {
                    $recipients[] = $u;
                }
            }

            if (empty($recipients)) {
                continue;
            }

            $signUrl = $base . '/iep/sign/' . $iepId . '/' . (int) $sig['id'];
            foreach ($recipients as $user) {
                $this->notifModel->create(
                    (int) $user['id'],
                    'iep_signature_request',
                    'IEP signature required',
                    "Please sign the IEP for {$iep['student_name']}. Use Open sign page below.",
                    json_encode(['iep_id' => $iepId, 'signatory_id' => (int) $sig['id']])
                );
                if (!empty($user['email'])) {
                    MailHelper::sendNotification(
                        $user['email'],
                        $user['name'],
                        'IEP signature required — ' . $iep['student_name'] . ' — SPED LMS',
                        "<h2 style=\"color:#1e4072;\">IEP signature required</h2>
                         <p>Dear {$user['name']},</p>
                         <p>Please sign the Individualized Education Program (IEP) for <strong>{$iep['student_name']}</strong> in the SPED LMS.</p>
                         <p><a href=\"{$signUrl}\" style=\"background:#a01422;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;\">Open sign page</a></p>
                         <p>Best regards,<br>SPED LMS</p>"
                    );
                }
            }
            $this->iepModel->markSignatoryRequestSent((int) $sig['id']);
        }
    }

    private function currentUserMaySignIepSlot(array $iep, array $sig): bool {
        $slot = $sig['signatory_role'] ?? '';
        if ($slot === 'parent_guardian') {
            $p = $this->iepModel->getLinkedParent($iep['student_id']);
            return (bool) ($p && (int) $p['id'] === (int) $this->userId);
        }
        if ($slot === 'guidance_counselor' && $this->userRole === 'guidance') {
            return true;
        }
        if ($slot === 'school_head' && $this->userRole === 'principal') {
            return true;
        }
        if ($slot === 'sned_teacher' && $this->userRole === 'sped_teacher' && (int) $iep['drafted_by'] === (int) $this->userId) {
            return true;
        }
        return false;
    }

    private function sendSignedCopies($iepId, $iep) {
        $db = Database::getInstance()->getConnection();
        $recipients = [];

        // Parent
        $parent = $this->iepModel->getLinkedParent($iep['student_id']);
        if ($parent) $recipients[] = $parent;

        // Guidance + Principal
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE role IN ('guidance','principal') AND status = 'active'");
        $stmt->execute();
        foreach ($stmt->fetchAll() as $s) $recipients[] = $s;

        $link = getenv('APP_URL') . BASE_PATH . '/iep/form/' . $iepId;

        foreach ($recipients as $user) {
            // Record copy
            $this->iepModel->recordCopy($iepId, $user['id']);

            // In-system notification
            $this->notifModel->create($user['id'], 'iep_signed',
                'IEP Signed — ' . $iep['student_name'],
                "The IEP for {$iep['student_name']} has been signed and is now available for viewing.",
                json_encode(['iep_id' => $iepId])
            );

            // Email (Guidance and Principal only, not Parent)
            if (!empty($user['email']) && in_array($user['role'] ?? '', ['guidance','principal'])) {
                MailHelper::sendNotification($user['email'], $user['name'],
                    'IEP Signed — ' . $iep['student_name'] . ' — SPED LMS',
                    "<h2 style='color:#1e4072;'>IEP Signed</h2>
                     <p>Dear {$user['name']},</p>
                     <p>The IEP for <strong>{$iep['student_name']}</strong> has been signed and is now available for viewing.</p>
                     <p><a href='{$link}' style='background:#a01422;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>View IEP</a></p>
                     <p>Best regards,<br>SPED LMS</p>"
                );
            }
        }
    }

    /**
     * After Sections 2–4 save on an already-signed IEP, append rows to iep_edit_logs (best-effort).
     */
    private function appendSignedIepEditLogs(
        int $iepId,
        array $iepBefore,
        ?array $coreBefore,
        array $domainsBefore,
        string $schoolYear,
        ?string $reEvalSql,
        array $domainNamesAfter,
        ?string $newDd,
        ?string $newPn,
        ?string $newTo
    ): void {
        $norm = static function ($v): string {
            if ($v === null) {
                return '';
            }
            return trim((string) $v);
        };
        $domainsCanon = static function (array $a): string {
            $t = array_values(array_unique(array_filter(array_map('trim', $a))));
            sort($t);
            return json_encode($t, JSON_UNESCAPED_UNICODE);
        };

        $pairs = [
            'school_year'          => [$norm($iepBefore['school_year'] ?? ''), $norm($schoolYear)],
            're_evaluation_date'   => [
                $norm($iepBefore['re_evaluation_date'] ?? ''),
                $reEvalSql === null ? '' : $norm($reEvalSql),
            ],
            'header_learner_name'  => [$norm($iepBefore['header_learner_name'] ?? ''), $norm($_POST['header_learner_name'] ?? '')],
            'header_learner_age'   => [$norm($iepBefore['header_learner_age'] ?? ''), $norm($_POST['header_learner_age'] ?? '')],
            'header_lrn'           => [$norm($iepBefore['header_lrn'] ?? ''), $norm($_POST['header_lrn'] ?? '')],
            'header_section'       => [$norm($iepBefore['header_section'] ?? ''), $norm($_POST['header_section'] ?? '')],
            'header_teacher_name'  => [$norm($iepBefore['header_teacher_name'] ?? ''), $norm($_POST['header_teacher_name'] ?? '')],
            'header_school_name'   => [$norm($iepBefore['header_school_name'] ?? ''), $norm($_POST['header_school_name'] ?? '')],
            'header_grade_level'   => [$norm($iepBefore['header_grade_level'] ?? ''), $norm($_POST['header_grade_level'] ?? '')],
        ];

        $beforeDom = $domainsCanon($domainsBefore);
        $afterDom  = $domainsCanon($domainNamesAfter);
        if ($beforeDom !== $afterDom) {
            try {
                $this->iepModel->insertIepEditLog($iepId, (int) $this->userId, 'iep_domains', $beforeDom, $afterDom);
            } catch (\Throwable $e) {
                error_log('iep_edit_logs: ' . $e->getMessage());
            }
        }

        $cb = $coreBefore ?? ['developmental_domain' => '', 'priority_needs' => '', 'terminal_objectives' => ''];
        $corePairs = [
            'developmental_domain' => [$norm($cb['developmental_domain'] ?? ''), $norm($newDd)],
            'priority_needs'       => [$norm($cb['priority_needs'] ?? ''), $norm($newPn)],
            'terminal_objectives'  => [$norm($cb['terminal_objectives'] ?? ''), $norm($newTo)],
        ];
        foreach ($corePairs as $fn => $pr) {
            $pairs[$fn] = $pr;
        }

        foreach ($pairs as $fieldName => $pr) {
            if ($pr[0] === $pr[1]) {
                continue;
            }
            try {
                $this->iepModel->insertIepEditLog(
                    $iepId,
                    (int) $this->userId,
                    $fieldName,
                    $pr[0] !== '' ? $pr[0] : null,
                    $pr[1] !== '' ? $pr[1] : null
                );
            } catch (\Throwable $e) {
                error_log('iep_edit_logs: ' . $e->getMessage());
            }
        }
    }

    private function logActivity($action, $recordId, $details) {
        try {
            $stmt = Database::getInstance()->getConnection()->prepare("
                INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address, created_at)
                VALUES (:user_id, :action, 'iep_records', :record_id, :details, :ip, NOW())
            ");
            $stmt->execute([
                'user_id'   => $this->userId,
                'action'    => $action,
                'record_id' => $recordId,
                'details'   => $details,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Activity log failed: " . $e->getMessage());
        }
    }
}