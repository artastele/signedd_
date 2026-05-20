<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Learning Controller (Learner Side)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/LessonPlanModel.php';
require_once __DIR__ . '/../Models/GamificationModel.php';

class LearningController {

    private LessonPlanModel   $model;
    private GamificationModel $gamification;
    private int    $userId;
    private string $userRole;
    private string $basePath;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        $this->userId   = (int) $_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? '';
        $this->basePath      = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model         = new LessonPlanModel();
        $this->gamification  = new GamificationModel();
    }

    // ----------------------------------------------------------------
    // Resolve student_records.id for the current session user.
    // For role=learner: email is learner_{LRN}@spedlms.local
    // For role=parent:  look up via enrollment_submissions.parent_id
    // ----------------------------------------------------------------
    private function getStudentId(): ?int {
        try {
            $db = Database::getInstance()->getConnection();

            if ($this->userRole === 'learner') {
                $stmt = $db->prepare("SELECT email FROM users WHERE id = :uid LIMIT 1");
                $stmt->execute(['uid' => $this->userId]);
                $user = $stmt->fetch();

                if ($user && preg_match('/learner_(\d+)@/', $user['email'], $m)) {
                    $lrn  = $m[1];
                    $stmt = $db->prepare("SELECT id FROM student_records WHERE lrn = :lrn LIMIT 1");
                    $stmt->execute(['lrn' => $lrn]);
                    $row = $stmt->fetch();
                    if ($row) return (int) $row['id'];
                }
            }

            // Allow teachers to view submissions via ?student_id
            if (in_array($this->userRole, ['sped_teacher', 'admin']) && isset($_GET['student_id'])) {
                return (int) $_GET['student_id'];
            }

            // Fallback / parent role
            $stmt = $db->prepare("
                SELECT sr.id
                FROM student_records sr
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                WHERE es.parent_id = :uid
                LIMIT 1
            ");
            $stmt->execute(['uid' => $this->userId]);
            $row = $stmt->fetch();
            if ($row) return (int) $row['id'];

            return null;
        } catch (\Throwable $e) {
            error_log('LearningController::getStudentId() error: ' . $e->getMessage());
            return null;
        }
    }

    private function getStudentName(int $studentId): string {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT student_name FROM student_records WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $studentId]);
            $row = $stmt->fetch();
            return $row ? $row['student_name'] : 'Learner';
        } catch (\Throwable $e) {
            return 'Learner';
        }
    }

    // ----------------------------------------------------------------
    // GET /learning/dashboard
    // ----------------------------------------------------------------
    public function dashboard(): void {
        $studentId   = $this->getStudentId();
        $studentName = $studentId ? $this->getStudentName($studentId) : 'Learner';
        $basePath    = $this->basePath;

        // Gamification summary for sidebar/topbar
        $gamSummary  = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP     = $gamSummary['total_xp'];
        $totalStars  = $gamSummary['total_stars'];

        if (!$studentId) {
            $lessonPlans     = [];
            $upcomingDue     = [];
            $overallComplete = 0;
            $overallTotal    = 0;
            require __DIR__ . '/../Views/dashboard/learner.php';
            return;
        }

        $lessonPlans = $this->model->getPublishedForStudent($studentId);
        foreach ($lessonPlans as &$lp) {
            $progress = $this->model->getLessonProgress((int) $lp['id'], $studentId);
            $lp['activity_count']  = $progress['total'];
            $lp['completed_count'] = $progress['completed'];
        }
        unset($lp);

        $overall         = $this->model->getStudentOverallProgress($studentId);
        $overallComplete = $overall['completed'];
        $overallTotal    = $overall['total'];
        $avgScore        = $overall['avg_score'] ?? 0;
        $upcomingDue     = $this->model->getUpcomingDue($studentId);

        // Badges — full list with earned/locked status (for My Badges tab)
        $badges          = $this->gamification->getBadgesWithStatus($studentId);
        // Total possible stars = 3 per activity that has been graded
        $totalStarsPossible = $overallTotal * 3;

        require __DIR__ . '/../Views/dashboard/learner.php';
    }

    // ----------------------------------------------------------------
    // GET /learning/lesson/{id}
    // ----------------------------------------------------------------
    public function lessonView(string $lessonPlanId): void {
        $lessonPlanId = (int) $lessonPlanId;
        $studentId    = $this->getStudentId();
        $basePath     = $this->basePath;
        $studentName  = $studentId ? $this->getStudentName($studentId) : 'Learner';

        // Gamification summary for sidebar/topbar
        $gamSummary = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP    = $gamSummary['total_xp'];
        $totalStars = $gamSummary['total_stars'];

        $lessonPlan = $this->model->findById($lessonPlanId);
        if (!$lessonPlan || $lessonPlan['status'] !== 'published') {
            $_SESSION['error'] = 'Lesson plan not found.';
            header('Location: ' . $this->basePath . '/learning/dashboard');
            exit;
        }

        $materials  = $this->model->getMaterials($lessonPlanId);
        $activities = $this->model->getActivities($lessonPlanId);

        foreach ($activities as &$act) {
            $act['submission']     = $studentId
                ? $this->model->getActivityWithStatus((int) $act['id'], $studentId)
                : null;
            $act['activity_data']  = json_decode($act['activity_data'] ?? '{}', true) ?? [];
        }
        unset($act);

        // Log 'opened'
        if ($studentId) {
            try {
                $db   = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    INSERT IGNORE INTO lms_logs
                        (student_id, activity_id, material_id, action, performed_by, performed_at)
                    VALUES (:student_id, NULL, NULL, 'opened', :performed_by, NOW())
                ");
                $stmt->execute(['student_id' => $studentId, 'performed_by' => $this->userId]);
            } catch (\Throwable $e) {
                error_log('LearningController::lessonView() log error: ' . $e->getMessage());
            }
        }

        require __DIR__ . '/../Views/learning/lesson_view.php';
    }

    // ----------------------------------------------------------------
    // GET /learning/activity/{id}
    // ----------------------------------------------------------------
    public function activityPlay(string $activityId): void {
        $activityId  = (int) $activityId;
        $studentId   = $this->getStudentId();
        $basePath    = $this->basePath;
        $studentName = $studentId ? $this->getStudentName($studentId) : 'Learner';

        // Gamification summary for sidebar/topbar
        $gamSummary = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP    = $gamSummary['total_xp'];
        $totalStars = $gamSummary['total_stars'];

        $activity = $this->model->getActivityById($activityId);
        if (!$activity) {
            $_SESSION['error'] = 'Activity not found.';
            header('Location: ' . $this->basePath . '/learning/dashboard');
            exit;
        }

        $activity['activity_data'] = json_decode($activity['activity_data'] ?? '{}', true) ?? [];

        $submission = $studentId
            ? $this->model->getActivityWithStatus($activityId, $studentId)
            : null;

        require __DIR__ . '/../Views/learning/activity_play.php';
    }

    // ----------------------------------------------------------------
    // POST /learning/activity/{id}/submit
    // ----------------------------------------------------------------
    public function submitActivity(string $activityId): void {
        header('Content-Type: application/json');
        $activityId = (int) $activityId;
        $studentId  = $this->getStudentId();

        if (!$studentId) {
            echo json_encode(['success' => false, 'message' => 'Student record not found.']);
            exit;
        }

        $input   = json_decode(file_get_contents('php://input'), true) ?? [];
        $answers = $input['answers'] ?? [];

        $activity = $this->model->getActivityById($activityId);
        if (!$activity) {
            echo json_encode(['success' => false, 'message' => 'Activity not found.']);
            exit;
        }

        $activityData = json_decode($activity['activity_data'] ?? '{}', true) ?? [];
        $type         = $activity['activity_type'] ?? '';
        $autoScore    = null;
        $maxScore     = (int) ($activity['max_score'] ?? 0);

        switch ($type) {
            case 'multiple_choice':
                $score = 0;
                foreach ($activityData['questions'] ?? [] as $qi => $q) {
                    $userAnswer = $answers[$qi] ?? null;
                    $questionPoints = (int) ($q['points'] ?? 1);

                    if (isset($q['correct_answer'])) {
                        if ((string) $q['correct_answer'] === (string) $userAnswer) {
                            $score += $questionPoints;
                        }
                        continue;
                    }

                    foreach ($q['options'] ?? [] as $oi => $opt) {
                        $isCorrect = null;
                        if (isset($opt['is_correct'])) {
                            $isCorrect = $opt['is_correct'];
                        } elseif (isset($opt['isCorrect'])) {
                            $isCorrect = $opt['isCorrect'];
                        } elseif (isset($opt['correct_answer'])) {
                            $isCorrect = $opt['correct_answer'];
                        }

                        if (!empty($isCorrect) && (string) $oi === (string) $userAnswer) {
                            $score += $questionPoints;
                            break;
                        }
                    }
                }
                $autoScore = $score;
                break;

            case 'true_false':
                $correctAnswer = $activityData['answer'] ?? $activityData['correct_answer'] ?? null;
                if ($correctAnswer === null && isset($activityData['questions'][0]['correct_answer'])) {
                    $correctAnswer = $activityData['questions'][0]['correct_answer'];
                }
                $given = $answers[0] ?? ($answers['answer'] ?? null);
                $points = (int) ($activityData['points'] ?? $activityData['questions'][0]['points'] ?? 1);
                $autoScore = (strtolower((string) $given) === strtolower((string) $correctAnswer))
                    ? $points
                    : 0;
                break;

            case 'fill_in_blanks':
                $score = 0;
                if (!empty($activityData['sentences'])) {
                    foreach ($activityData['sentences'] as $si => $sentence) {
                        $given = strtolower(trim($answers[$si] ?? ''));
                        foreach ($sentence['answers'] ?? [] as $ca) {
                            if ($given === strtolower(trim($ca))) {
                                $score += (int) ($activityData['points'] ?? 1);
                                break;
                            }
                        }
                    }
                } elseif (!empty($activityData['answers'])) {
                    foreach ($activityData['answers'] as $si => $correct) {
                        $given = strtolower(trim($answers[$si] ?? ''));
                        if ($given === strtolower(trim((string) $correct))) {
                            $score += (int) ($activityData['points'] ?? 1);
                        }
                    }
                } elseif (!empty($activityData['sentence']) && !empty($activityData['answers'])) {
                    foreach ($activityData['answers'] as $si => $correct) {
                        $given = strtolower(trim($answers[$si] ?? ''));
                        if ($given === strtolower(trim((string) $correct))) {
                            $score += (int) ($activityData['points'] ?? 1);
                        }
                    }
                }
                $autoScore = $score;
                break;

            case 'matching':
                $score = 0;
                foreach ($activityData['pairs'] ?? [] as $pi => $pair) {
                    if ((string) ($answers[$pi] ?? null) === (string) $pair['right']) {
                        $score += (int) ($activityData['points'] ?? 1);
                    }
                }
                $autoScore = $score;
                break;

            case 'drag_drop_sort':
            case 'sequencing':
                $correctOrder = $activityData['correct_order'] ?? [];
                if (empty($correctOrder)) {
                    if (!empty($activityData['items'])) {
                        $correctOrder = range(0, count($activityData['items']) - 1);
                    } elseif (!empty($activityData['steps'])) {
                        $correctOrder = range(0, count($activityData['steps']) - 1);
                    }
                }
                $givenOrder = array_values($answers);
                $autoScore = (array_map('strval', $givenOrder) === array_map('strval', $correctOrder))
                    ? (int) ($activityData['points'] ?? 1)
                    : 0;
                break;

            case 'image_label':
            case 'flashcards':
                $autoScore = null;
                break;
        }

        $this->model->saveSubmission($activityId, $studentId, $this->userId, json_encode($answers), $autoScore);

        // ── Step 12-14: Stars, XP, Badges ──────────────────────────
        try {
            // Fetch the submission id we just saved
            $db      = Database::getInstance()->getConnection();
            $stmtSub = $db->prepare("SELECT id FROM lms_submissions WHERE activity_id=:a AND student_id=:s ORDER BY submitted_at DESC LIMIT 1");
            $stmtSub->execute(['a' => $activityId, 's' => $studentId]);
            $subId = (int) $stmtSub->fetchColumn();

            // Step 12 — Stars (auto from score%)
            if ($subId && $autoScore !== null && $maxScore > 0) {
                $this->gamification->saveStarsForSubmission($subId, $studentId, $autoScore, $maxScore);
            }

            // Step 13 — XP per event
            $xpType = in_array($type, ['flashcards', 'image_label']) ? 'view' : ($autoScore !== null ? 'quiz' : 'submission');
            if ($xpType === 'view') {
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'view', $activityId))
                    $this->gamification->awardXP($studentId, 5, 'Viewed activity', 'view', $activityId);
            } elseif ($xpType === 'quiz' && $maxScore > 0) {
                $xp = (int) round(($autoScore / $maxScore) * $maxScore);
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'quiz', $activityId))
                    $this->gamification->awardXP($studentId, max($xp, 1), 'Quiz submitted', 'quiz', $activityId);
            } else {
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'submission', $activityId))
                    $this->gamification->awardXP($studentId, 10, 'Activity submitted', 'submission', $activityId);
            }

            // Lesson completion bonus (+20 XP)
            $prog = $this->model->getLessonProgress((int)$activity['lesson_plan_id'], $studentId);
            if ($prog['completed'] >= $prog['total'] && $prog['total'] > 0) {
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'lesson_bonus', (int)$activity['lesson_plan_id']))
                    $this->gamification->awardXP($studentId, 20, 'Lesson completed!', 'lesson_bonus', (int)$activity['lesson_plan_id']);
            }

            // Step 14 — Badges auto-check
            $totalSubs = (int) $db->query("SELECT COUNT(*) FROM lms_submissions WHERE student_id={$studentId}")->fetchColumn();
            if ($totalSubs >= 1)  $this->gamification->awardBadge($studentId, 'first_activity');
            if ($totalSubs >= 5)  $this->gamification->awardBadge($studentId, 'five_in_a_row');
            if ($autoScore !== null && $maxScore > 0 && $autoScore >= $maxScore)
                $this->gamification->awardBadge($studentId, 'perfect_score');
            if ($prog['completed'] >= $prog['total'] && $prog['total'] > 0)
                $this->gamification->awardBadge($studentId, 'lesson_complete');
            $totalStarsEarned = $this->gamification->getTotalStars($studentId);
            if ($totalStarsEarned >= 10) $this->gamification->awardBadge($studentId, 'star_collector');
            // all_done: all published plans fully complete
            $allDone = $db->query("SELECT COUNT(*) FROM lesson_plans lp JOIN lesson_assignments la ON lp.id=la.lesson_plan_id WHERE la.student_id={$studentId} AND lp.status='published'")->fetchColumn();
            $allDoneSub = $db->query("SELECT COUNT(DISTINCT act.lesson_plan_id) FROM lms_submissions sub JOIN lms_activities act ON sub.activity_id=act.id WHERE sub.student_id={$studentId}")->fetchColumn();
            if ($allDone > 0 && $allDoneSub >= $allDone) $this->gamification->awardBadge($studentId, 'all_done');

            // Badge XP bonus (+15 per new badge)
            $earnedKeys = $this->gamification->getEarnedBadgeKeys($studentId);
            foreach ($earnedKeys as $bk) {
                if (!$this->gamification->xpAlreadyAwarded($studentId, 'badge_bonus', 0))
                    break; // bonus handled separately — skip double counting
            }

            // Log submission
            $db->prepare("INSERT INTO lms_logs (student_id,activity_id,material_id,action,performed_by,performed_at) VALUES (:s,:a,NULL,'submitted',:p,NOW())")
               ->execute(['s' => $studentId, 'a' => $activityId, 'p' => $this->userId]);

        } catch (\Throwable $e) {
            error_log('LearningController::submitActivity() gamification error: ' . $e->getMessage());
        }

        echo json_encode([
            'success'    => true,
            'auto_score' => $autoScore,
            'max_score'  => $maxScore,
            'type'       => $type,
            'message'    => $autoScore !== null
                ? "You got {$autoScore} / {$maxScore} correct!"
                : ($type === 'image_label'
                    ? 'Submitted! Your teacher will review and grade this.'
                    : 'Great job reviewing!'),
        ]);

        exit;
    }

    // ----------------------------------------------------------------
    // GET /learning/progress
    // ----------------------------------------------------------------
    public function progress(): void {
        $studentId   = $this->getStudentId();
        $basePath    = $this->basePath;
        $studentName = $studentId ? $this->getStudentName($studentId) : 'Learner';

        // Gamification summary for sidebar/topbar
        $gamSummary = $studentId ? $this->gamification->getSummary($studentId) : ['total_xp' => 0, 'total_stars' => 0];
        $totalXP    = $gamSummary['total_xp'];
        $totalStars = $gamSummary['total_stars'];

        $overallComplete = 0;
        $overallTotal    = 0;
        $avgScore        = 0;
        $domainProgress  = [];
        $recentGrades    = [];

        if ($studentId) {
            $overall         = $this->model->getStudentOverallProgress($studentId);
            $overallComplete = $overall['completed'];
            $overallTotal    = $overall['total'];
            $avgScore        = $overall['avg_score'] ?? 0;
            $domainProgress  = $this->model->getProgressByDomain($studentId);
            $recentGrades    = $this->getRecentGrades($studentId);
        }

        require __DIR__ . '/../Views/learning/progress.php';
    }

    private function getRecentGrades(int $studentId): array {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT
                    lp.title  AS lesson_plan_title,
                    act.title AS activity_title,
                    g.score,
                    g.max_score,
                    g.graded_at
                FROM lms_grades g
                JOIN lms_submissions sub ON g.submission_id = sub.id
                JOIN lms_activities act  ON sub.activity_id = act.id
                JOIN lesson_plans lp     ON act.lesson_plan_id = lp.id
                WHERE sub.student_id = :student_id
                ORDER BY g.graded_at DESC
                LIMIT 10
            ");
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('LearningController::getRecentGrades() error: ' . $e->getMessage());
            return [];
        }
    }

    // ----------------------------------------------------------------
    // Step 16 — GET /parent/child-progress
    // Lists all children enrolled under the parent with overall progress
    // ----------------------------------------------------------------
    public function parentChildProgress(): void {
        if ($this->userRole !== 'parent') {
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Fetch all students linked to this parent via enrollment_submissions
        $stmt = $db->prepare("
            SELECT sr.id AS student_id,
                   sr.student_name,
                   es.grade_level_to_enroll AS grade_level
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE es.parent_id = :parent_id
              AND es.status = 'verified'
        ");
        $stmt->execute(['parent_id' => $this->userId]);
        $children = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Attach quick summary per child
        foreach ($children as &$child) {
            $sid = (int)$child['student_id'];
            $gam = $this->gamification->getSummary($sid);
            $child['total_xp']    = $gam['total_xp'];
            $child['total_stars'] = $gam['total_stars'];

            // Overall progress count
            $ps = $db->prepare("
                SELECT COUNT(DISTINCT act.id) AS total,
                       COUNT(DISTINCT sub.activity_id) AS done
                FROM lms_activities act
                JOIN lesson_assignments la ON la.lesson_plan_id = act.lesson_plan_id
                LEFT JOIN lms_submissions sub ON sub.activity_id = act.id AND sub.student_id = :sid
                WHERE la.student_id = :sid2
            ");
            $ps->execute(['sid' => $sid, 'sid2' => $sid]);
            $row = $ps->fetch(\PDO::FETCH_ASSOC);
            $child['total']    = (int)($row['total'] ?? 0);
            $child['complete'] = (int)($row['done']  ?? 0);
            $child['pct']      = $child['total'] > 0 ? round(($child['complete'] / $child['total']) * 100) : 0;
        }
        unset($child);

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/dashboard/parent_child_progress.php';
    }

    // ----------------------------------------------------------------
    // Step 17 — GET /parent/child-progress/{id}
    // Parent views one child's scores/XP/stars — NO lesson content
    // ----------------------------------------------------------------
    public function parentStudentProgress(string $studentId): void {
        if ($this->userRole !== 'parent') {
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        $studentId = (int)$studentId;
        $db = Database::getInstance()->getConnection();

        // Security: verify this student belongs to this parent
        $check = $db->prepare("
            SELECT sr.id FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.id = :sid AND es.parent_id = :pid AND es.status = 'verified'
            LIMIT 1
        ");
        $check->execute(['sid' => $studentId, 'pid' => $this->userId]);
        if (!$check->fetch()) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/parent/child-progress');
            exit;
        }

        // Student name
        $sn = $db->prepare("SELECT sr.student_name AS name, es.grade_level_to_enroll AS grade_level FROM student_records sr JOIN enrollment_submissions es ON sr.enrollment_id = es.id WHERE sr.id=:id");
        $sn->execute(['id' => $studentId]);
        $studentInfo = $sn->fetch(\PDO::FETCH_ASSOC);
        $studentName = $studentInfo['name'] ?? 'Student';

        $gam       = $this->gamification->getSummary($studentId);
        $totalXP   = $gam['total_xp'];
        $totalStars= $gam['total_stars'];

        // Overall progress
        $ps = $db->prepare("
            SELECT COUNT(DISTINCT act.id) AS total,
                   COUNT(DISTINCT sub.activity_id) AS done
            FROM lms_activities act
            JOIN lesson_assignments la ON la.lesson_plan_id = act.lesson_plan_id
            LEFT JOIN lms_submissions sub ON sub.activity_id = act.id AND sub.student_id = :sid
            WHERE la.student_id = :sid2
        ");
        $ps->execute(['sid' => $studentId, 'sid2' => $studentId]);
        $pr = $ps->fetch(\PDO::FETCH_ASSOC);
        $overallTotal    = (int)($pr['total'] ?? 0);
        $overallComplete = (int)($pr['done']  ?? 0);
        $pct = $overallTotal > 0 ? round(($overallComplete / $overallTotal) * 100) : 0;

        // Graded activity scores (no content, just score/date)
        $grades = $db->prepare("
            SELECT lp.title AS lesson_plan_title,
                   act.title AS activity_title,
                   g.score,
                   COALESCE(g.max_score, act.max_score) AS max_score,
                   g.graded_at
            FROM lms_grades g
            JOIN lms_submissions sub ON g.submission_id = sub.id
            JOIN lms_activities act  ON sub.activity_id = act.id
            JOIN lesson_plans lp     ON act.lesson_plan_id = lp.id
            WHERE sub.student_id = :sid
            ORDER BY g.graded_at DESC
        ");
        $grades->execute(['sid' => $studentId]);
        $recentGrades = $grades->fetchAll(\PDO::FETCH_ASSOC);

        $basePath = $this->basePath;
        require __DIR__ . '/../Views/dashboard/parent_student_progress.php';
    }
}
