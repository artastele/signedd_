<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-06-01
// Part of: SPED LMS — Lesson Plan Model (IEP Implementation)

require_once __DIR__ . '/../../config/db.php';

class LessonPlanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================================
    // SIGNED IEPs FOR TEACHER
    // ============================================================

    /**
     * Get students with signed IEPs for a given teacher (drafted_by = teacherId)
     * Returns iep_records joined with student_records, plus lesson plan count
     */
    public function getSignedIEPsForTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT
                ir.id            AS iep_id,
                ir.student_id,
                ir.school_year,
                ir.status        AS iep_status,
                ir.signed_document_path,
                ir.re_evaluation_date,
                ir.created_at    AS iep_created_at,
                sr.student_name,
                sr.lrn,
                (
                    SELECT COUNT(*) FROM lesson_plans lp
                    WHERE lp.iep_id = ir.id
                ) AS lesson_plan_count,
                (
                    SELECT COUNT(*) FROM lesson_plans lp
                    WHERE lp.iep_id = ir.id AND lp.status = 'published'
                ) AS published_count
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            WHERE ir.drafted_by = :teacher_id
              AND ir.status = 'signed'
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // LESSON PLANS CRUD
    // ============================================================

    /**
     * Create a new lesson plan
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO lesson_plans
                (iep_id, student_id, created_by, title, pdsp_domain, assignment_type,
                 document_path, status, created_at, updated_at)
            VALUES
                (:iep_id, :student_id, :created_by, :title, :pdsp_domain, :assignment_type,
                 :document_path, 'draft', NOW(), NOW())
        ");
        $stmt->execute([
            'iep_id'          => $data['iep_id'],
            'student_id'      => $data['student_id'] ?? null,
            'created_by'      => $data['created_by'],
            'title'           => $data['title'],
            'pdsp_domain'     => $data['pdsp_domain'],
            'assignment_type' => $data['assignment_type'],
            'document_path'   => $data['document_path'] ?? null,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Find a lesson plan by ID — joins iep_records, student_records, users
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT
                lp.*,
                ir.school_year, ir.status AS iep_status, ir.signed_document_path,
                ir.re_evaluation_date,
                sr.student_name, sr.lrn,
                u.name AS created_by_name
            FROM lesson_plans lp
            JOIN iep_records ir     ON lp.iep_id     = ir.id
            JOIN student_records sr ON ir.student_id  = sr.id
            JOIN users u            ON lp.created_by  = u.id
            WHERE lp.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all lesson plans for a given IEP
     */
    public function getByIepId($iepId) {
        $stmt = $this->db->prepare("
            SELECT lp.*, u.name AS created_by_name
            FROM lesson_plans lp
            JOIN users u ON lp.created_by = u.id
            WHERE lp.iep_id = :iep_id
            ORDER BY lp.created_at DESC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    /**
     * Update allowed fields on a lesson plan
     */
    public function update($id, $data) {
        $allowed = ['title', 'pdsp_domain', 'document_path', 'status', 'published_at'];
        $sets    = [];
        $params  = ['id' => $id];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]      = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        $sets[] = "updated_at = NOW()";
        $stmt = $this->db->prepare(
            "UPDATE lesson_plans SET " . implode(', ', $sets) . " WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    /**
     * Publish a lesson plan
     */
    public function publish($id) {
        $stmt = $this->db->prepare("
            UPDATE lesson_plans
            SET status = 'published', published_at = NOW(), updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $id]);
    }

    // ============================================================
    // LESSON ASSIGNMENTS
    // ============================================================

    /**
     * Get students assigned to a lesson plan
     */
    public function getAssignedStudents($lessonPlanId) {
        $stmt = $this->db->prepare("
            SELECT la.*, sr.student_name, sr.lrn
            FROM lesson_assignments la
            JOIN student_records sr ON la.student_id = sr.id
            WHERE la.lesson_plan_id = :lesson_plan_id
        ");
        $stmt->execute(['lesson_plan_id' => $lessonPlanId]);
        return $stmt->fetchAll();
    }

    /**
     * Assign a lesson plan to a single student (INSERT IGNORE to avoid duplicates)
     */
    public function assignToStudent($lessonPlanId, $studentId, $assignedBy) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO lesson_assignments
                (lesson_plan_id, student_id, assigned_by, assigned_at)
            VALUES
                (:lesson_plan_id, :student_id, :assigned_by, NOW())
        ");
        return $stmt->execute([
            'lesson_plan_id' => $lessonPlanId,
            'student_id'     => $studentId,
            'assigned_by'    => $assignedBy,
        ]);
    }

    /**
     * Assign a lesson plan to ALL students with signed IEPs under this teacher
     */
    public function assignToAllLearners($lessonPlanId, $iepId, $assignedBy) {
        // Get the teacher from the lesson plan
        $lp = $this->findById($lessonPlanId);
        if (!$lp) return false;

        $teacherId = $lp['created_by'];

        // Get all students with signed IEPs for this teacher
        $students = $this->getSignedIEPsForTeacher($teacherId);

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO lesson_assignments
                (lesson_plan_id, student_id, assigned_by, assigned_at)
            VALUES
                (:lesson_plan_id, :student_id, :assigned_by, NOW())
        ");

        foreach ($students as $student) {
            $stmt->execute([
                'lesson_plan_id' => $lessonPlanId,
                'student_id'     => $student['student_id'],
                'assigned_by'    => $assignedBy,
            ]);
        }
        return true;
    }

    // ============================================================
    // LESSON MATERIALS
    // ============================================================

    /**
     * Add a material to a lesson plan
     */
    public function addMaterial($data) {
        $stmt = $this->db->prepare("
            INSERT INTO lesson_materials
                (lesson_plan_id, material_type, title, file_path, external_url,
                 embed_type, display_order, uploaded_at)
            VALUES
                (:lesson_plan_id, :material_type, :title, :file_path, :external_url,
                 :embed_type, :display_order, NOW())
        ");
        $stmt->execute([
            'lesson_plan_id' => $data['lesson_plan_id'],
            'material_type'  => $data['material_type'],
            'title'          => $data['title'],
            'file_path'      => $data['file_path']    ?? null,
            'external_url'   => $data['external_url'] ?? null,
            'embed_type'     => $data['embed_type']   ?? null,
            'display_order'  => $data['display_order'] ?? 0,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all materials for a lesson plan, ordered by display_order
     */
    public function getMaterials($lessonPlanId) {
        $stmt = $this->db->prepare("
            SELECT * FROM lesson_materials
            WHERE lesson_plan_id = :lesson_plan_id
            ORDER BY display_order ASC, uploaded_at ASC
        ");
        $stmt->execute(['lesson_plan_id' => $lessonPlanId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all materials for all lesson plans belonging to an IEP
     */
    public function getMaterialsByIepId($iepId) {
        $stmt = $this->db->prepare("
            SELECT lm.*, lp.title AS lesson_plan_title
            FROM lesson_materials lm
            JOIN lesson_plans lp ON lm.lesson_plan_id = lp.id
            WHERE lp.iep_id = :iep_id
            ORDER BY lm.display_order ASC, lm.uploaded_at ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete a material
     */
    public function deleteMaterial($id) {
        $stmt = $this->db->prepare("DELETE FROM lesson_materials WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get a single material by ID
     */
    public function getMaterialById($id) {
        $stmt = $this->db->prepare("SELECT * FROM lesson_materials WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Update an existing material (title + optional URL / file_path)
     */
    public function updateMaterial($id, $data) {
        $allowed = ['title', 'external_url', 'file_path', 'embed_type'];
        $sets    = [];
        $params  = ['id' => $id];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]      = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        $stmt = $this->db->prepare(
            "UPDATE lesson_materials SET " . implode(', ', $sets) . " WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    /**
     * Count materials for a lesson plan (for display_order)
     */
    public function countMaterials($lessonPlanId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lesson_materials WHERE lesson_plan_id = :lesson_plan_id");
        $stmt->execute(['lesson_plan_id' => $lessonPlanId]);
        return (int) $stmt->fetchColumn();
    }

    // ============================================================
    // LMS ACTIVITIES
    // ============================================================

    /**
     * Add an activity to a lesson plan
     */
    public function addActivity($data) {
        $stmt = $this->db->prepare("
            INSERT INTO lms_activities
                (lesson_plan_id, title, instructions, activity_type, activity_data,
                 max_score, due_date, display_order, created_at)
            VALUES
                (:lesson_plan_id, :title, :instructions, :activity_type, :activity_data,
                 :max_score, :due_date, :display_order, NOW())
        ");
        $stmt->execute([
            'lesson_plan_id' => $data['lesson_plan_id'],
            'title'          => $data['title'],
            'instructions'   => $data['instructions']  ?? null,
            'activity_type'  => $data['activity_type'],
            'activity_data'  => $data['activity_data'], // JSON string
            'max_score'      => $data['max_score']      ?? 0,
            'due_date'       => $data['due_date']       ?? null,
            'display_order'  => $data['display_order']  ?? 0,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all activities for a lesson plan, ordered by display_order
     */
    public function getActivities($lessonPlanId) {
        $stmt = $this->db->prepare("
            SELECT * FROM lms_activities
            WHERE lesson_plan_id = :lesson_plan_id
            ORDER BY display_order ASC, created_at ASC
        ");
        $stmt->execute(['lesson_plan_id' => $lessonPlanId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all activities for all lesson plans belonging to an IEP
     */
    public function getActivitiesByIepId($iepId) {
        $stmt = $this->db->prepare("
            SELECT la.*, lp.title AS lesson_plan_title
            FROM lms_activities la
            JOIN lesson_plans lp ON la.lesson_plan_id = lp.id
            WHERE lp.iep_id = :iep_id
            ORDER BY la.display_order ASC, la.created_at ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete an activity
     */
    public function deleteActivity($id) {
        $stmt = $this->db->prepare("DELETE FROM lms_activities WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get a single activity by ID
     */
    public function getActivityById($id) {
        $stmt = $this->db->prepare("SELECT * FROM lms_activities WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Update basic fields on an existing activity
     * (title, instructions, due_date, max_score — not activity_data)
     */
    public function updateActivity($id, $data) {
        $allowed = ['title', 'instructions', 'due_date', 'max_score'];
        $sets    = [];
        $params  = ['id' => $id];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]      = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        $stmt = $this->db->prepare(
            "UPDATE lms_activities SET " . implode(', ', $sets) . " WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    public function deleteLessonPlan($id) {
        // Explicitly delete child records to ensure cascade (if DB cascade is missing)
        $stmtMat = $this->db->prepare("DELETE FROM lesson_materials WHERE lesson_plan_id = :id");
        $stmtMat->execute(['id' => $id]);

        $stmtAct = $this->db->prepare("DELETE FROM lms_activities WHERE lesson_plan_id = :id");
        $stmtAct->execute(['id' => $id]);

        $stmtAssign = $this->db->prepare("DELETE FROM lesson_assignments WHERE lesson_plan_id = :id");
        $stmtAssign->execute(['id' => $id]);

        $stmt = $this->db->prepare("DELETE FROM lesson_plans WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // ============================================================
    // LOGGING
    // ============================================================

    /**
     * Log a learner action (opened/submitted/graded)
     */
    public function logAction($studentId, $activityId, $materialId, $action, $performedBy) {
        $stmt = $this->db->prepare("
            INSERT INTO lms_logs
                (student_id, activity_id, material_id, action, performed_by, performed_at)
            VALUES
                (:student_id, :activity_id, :material_id, :action, :performed_by, NOW())
        ");
        return $stmt->execute([
            'student_id'   => $studentId,
            'activity_id'  => $activityId,
            'material_id'  => $materialId,
            'action'       => $action,
            'performed_by' => $performedBy,
        ]);
    }

    // ============================================================
    // STATS HELPERS
    // ============================================================

    /**
     * Count total students with signed IEPs for a teacher
     */
    public function countStudentsForTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM iep_records
            WHERE drafted_by = :teacher_id AND status = 'signed'
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count published lesson plans for a teacher
     */
    public function countPublishedForTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM lesson_plans lp
            JOIN iep_records ir ON lp.iep_id = ir.id
            WHERE ir.drafted_by = :teacher_id AND lp.status = 'published'
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count pending (ungraded) submissions for a teacher's lesson plans
     */
    public function countPendingSubmissionsForTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM lms_submissions sub
            JOIN lms_activities act ON sub.activity_id = act.id
            JOIN lesson_plans lp    ON act.lesson_plan_id = lp.id
            JOIN iep_records ir     ON lp.iep_id = ir.id
            LEFT JOIN lms_grades g  ON sub.id = g.submission_id
            WHERE ir.drafted_by = :teacher_id AND g.id IS NULL
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count draft lesson plans for a teacher
     */
    public function countDraftForTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM lesson_plans lp
            JOIN iep_records ir ON lp.iep_id = ir.id
            WHERE ir.drafted_by = :teacher_id AND lp.status = 'draft'
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Prefer item-count from activity JSON so displayed max matches number of questions/blanks, etc.
     *
     * @param array<string,mixed> $activityRow activity_type, max_score, activity_data (string or array)
     */
    public static function displayMaxScoreForActivity(array $activityRow): int {
        $dbMax = (int) ($activityRow['max_score'] ?? $activityRow['activity_max_score'] ?? 0);
        $type  = (string) ($activityRow['activity_type'] ?? '');
        $raw   = $activityRow['activity_data'] ?? null;
        $data  = [];
        if (is_array($raw)) {
            $data = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $data = json_decode($raw, true) ?: [];
        }
        $n = 0;
        switch ($type) {
            case 'multiple_choice':
                $n = count($data['questions'] ?? []);
                break;
            case 'fill_in_blanks':
                $n = count($data['sentences'] ?? []);
                break;
            case 'matching':
                $n = count($data['pairs'] ?? []);
                break;
            case 'true_false':
                $n = 1;
                break;
            case 'image_label':
                $n = count($data['labels'] ?? []);
                break;
            case 'drag_drop_sort':
            case 'sequencing':
                $items = $data['items'] ?? $data['steps'] ?? [];
                $n = count($items);
                break;
            default:
                $n = 0;
        }
        if ($n > 0) {
            return $n;
        }
        return $dbMax > 0 ? $dbMax : 0;
    }

    /**
     * Get all submissions for all activities in a lesson plan
     */
    public function getSubmissionsForLessonPlan($lessonPlanId) {
        $stmt = $this->db->prepare("
            SELECT sub.*, 
                   act.title AS activity_title, act.activity_type, act.max_score AS activity_max_score,
                   act.activity_data,
                   sr.student_name,
                   g.score AS graded_score, g.max_score AS graded_max_score, g.is_complete, g.remarks
            FROM lms_submissions sub
            JOIN lms_activities act ON sub.activity_id = act.id
            JOIN student_records sr ON sub.student_id = sr.id
            LEFT JOIN lms_grades g ON sub.id = g.submission_id
            WHERE act.lesson_plan_id = :lp_id
            ORDER BY sub.submitted_at DESC
        ");
        $stmt->execute(['lp_id' => $lessonPlanId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$r) {
            $r['display_max_score'] = self::displayMaxScoreForActivity([
                'activity_type' => $r['activity_type'] ?? '',
                'max_score'     => $r['activity_max_score'] ?? 0,
                'activity_data' => $r['activity_data'] ?? null,
            ]);
        }
        unset($r);
        return $rows;
    }

    /**
     * Get all learners for a specific teacher with their progress stats
     */
    public function getLearnersForTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT 
                sr.id AS student_id,
                sr.student_name,
                sr.lrn,
                ir.id AS iep_id,
                -- Get total XP and stars
                (SELECT COALESCE(SUM(points), 0) FROM learner_points WHERE student_id = sr.id) AS total_xp,
                (SELECT COALESCE(SUM(stars), 0) FROM activity_stars WHERE student_id = sr.id) AS total_stars,
                -- Get number of published lesson plans
                (SELECT COUNT(*) FROM lesson_plans lp2 WHERE lp2.iep_id = ir.id AND lp2.status = 'published') AS published_plans,
                -- Get number of completed activities (graded)
                (SELECT COUNT(*) FROM lms_grades g 
                 JOIN lms_submissions s ON g.submission_id = s.id 
                 JOIN lms_activities a ON s.activity_id = a.id
                 JOIN lesson_plans lp2 ON a.lesson_plan_id = lp2.id
                 WHERE s.student_id = sr.id AND lp2.iep_id = ir.id AND g.is_complete = 1) AS completed_activities,
                -- Get total number of activities
                (SELECT COUNT(*) FROM lms_activities a 
                 JOIN lesson_plans lp2 ON a.lesson_plan_id = lp2.id 
                 WHERE lp2.iep_id = ir.id AND lp2.status = 'published') AS total_activities
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            WHERE ir.drafted_by = :teacher_id AND ir.status = 'signed'
            ORDER BY sr.student_name ASC
        ");
        $stmt->execute(['teacher_id' => $teacherId]);
        return $stmt->fetchAll();
    }

    /**
     * Get IEP record by ID (simple fetch for workspace)
     */
    public function getIepById($iepId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            JOIN users u            ON ir.drafted_by  = u.id
            WHERE ir.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $iepId]);
        return $stmt->fetch();
    }

    /**
     * Get signatories for an IEP (from iep_signatories table)
     */
    public function getSignatories($iepId) {
        $stmt = $this->db->prepare("
            SELECT * FROM iep_signatories WHERE iep_id = :iep_id ORDER BY id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    /**
     * Get users linked to assigned students for notifications
     * Joins student_records → enrollment_submissions → users (role learner or parent)
     */
    public function getAssignedUserAccounts($lessonPlanId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT u.id AS user_id, u.name, u.role
            FROM lesson_assignments la
            JOIN student_records sr ON la.student_id = sr.id
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON u.id = es.parent_id
            WHERE la.lesson_plan_id = :lesson_plan_id
              AND u.role IN ('learner', 'parent')
        ");
        $stmt->execute(['lesson_plan_id' => $lessonPlanId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // PROCESS 7 — LEARNER SIDE METHODS
    // ============================================================

    /**
     * Get all published lesson plans assigned to a student
     */
    public function getPublishedForStudent($studentId) {
        $stmt = $this->db->prepare("
            SELECT lp.*, ir.school_year, sr.student_name
            FROM lesson_plans lp
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            JOIN iep_records ir ON lp.iep_id = ir.id
            JOIN student_records sr ON ir.student_id = sr.id
            WHERE la.student_id = :student_id AND lp.status = 'published'
            ORDER BY lp.published_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get activity count and completed count for a lesson plan + student
     * Returns ['total' => N, 'completed' => N]
     */
    public function getLessonProgress($lessonPlanId, $studentId) {
        $stmtTotal = $this->db->prepare("
            SELECT COUNT(*) FROM lms_activities WHERE lesson_plan_id = :lp_id
        ");
        $stmtTotal->execute(['lp_id' => $lessonPlanId]);
        $total = (int) $stmtTotal->fetchColumn();

        $stmtDone = $this->db->prepare("
            SELECT COUNT(*) FROM lms_submissions sub
            JOIN lms_activities act ON sub.activity_id = act.id
            WHERE act.lesson_plan_id = :lp_id AND sub.student_id = :student_id
        ");
        $stmtDone->execute(['lp_id' => $lessonPlanId, 'student_id' => $studentId]);
        $completed = (int) $stmtDone->fetchColumn();

        return ['total' => $total, 'completed' => $completed];
    }

    /**
     * Get a single activity with its submission status for a student
     */
    public function getActivityWithStatus($activityId, $studentId) {
        $stmt = $this->db->prepare("
            SELECT la.*,
                   sub.id AS submission_id, sub.answers, sub.auto_score, sub.submitted_at,
                   g.score, g.max_score AS grade_max_score, g.is_complete, g.remarks
            FROM lms_activities la
            LEFT JOIN lms_submissions sub
                   ON sub.activity_id = la.id AND sub.student_id = :student_id
            LEFT JOIN lms_grades g ON g.submission_id = sub.id
            WHERE la.id = :activity_id
            LIMIT 1
        ");
        $stmt->execute(['activity_id' => $activityId, 'student_id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Save a submission (INSERT … ON DUPLICATE KEY UPDATE)
     */
    public function saveSubmission($activityId, $studentId, $submittedBy, $answers, $autoScore) {
        $stmt = $this->db->prepare("
            INSERT INTO lms_submissions
                (activity_id, student_id, submitted_by, answers, auto_score, submitted_at)
            VALUES
                (:activity_id, :student_id, :submitted_by, :answers, :auto_score, NOW())
            ON DUPLICATE KEY UPDATE
                answers      = VALUES(answers),
                auto_score   = VALUES(auto_score),
                submitted_at = NOW()
        ");
        return $stmt->execute([
            'activity_id'  => $activityId,
            'student_id'   => $studentId,
            'submitted_by' => $submittedBy,
            'answers'      => $answers,
            'auto_score'   => $autoScore,
        ]);
    }

    /**
     * Get overall progress for a student (total activities, completed, avg score)
     */
    public function getStudentOverallProgress($studentId) {
        // Total activities in published plans assigned to student
        $stmtTotal = $this->db->prepare("
            SELECT COUNT(DISTINCT act.id)
            FROM lms_activities act
            JOIN lesson_plans lp ON act.lesson_plan_id = lp.id
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            WHERE la.student_id = :student_id AND lp.status = 'published'
        ");
        $stmtTotal->execute(['student_id' => $studentId]);
        $total = (int) $stmtTotal->fetchColumn();

        // Completed = has a submission
        $stmtDone = $this->db->prepare("
            SELECT COUNT(DISTINCT sub.activity_id)
            FROM lms_submissions sub
            JOIN lms_activities act ON sub.activity_id = act.id
            JOIN lesson_plans lp ON act.lesson_plan_id = lp.id
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            WHERE la.student_id = :student_id AND lp.status = 'published'
        ");
        $stmtDone->execute(['student_id' => $studentId]);
        $completed = (int) $stmtDone->fetchColumn();

        // Average score from lms_grades
        $stmtAvg = $this->db->prepare("
            SELECT AVG(g.score / NULLIF(g.max_score, 0) * 100)
            FROM lms_grades g
            JOIN lms_submissions sub ON g.submission_id = sub.id
            JOIN lms_activities act ON sub.activity_id = act.id
            JOIN lesson_plans lp ON act.lesson_plan_id = lp.id
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            WHERE la.student_id = :student_id AND lp.status = 'published'
        ");
        $stmtAvg->execute(['student_id' => $studentId]);
        $avgScore = round((float) $stmtAvg->fetchColumn(), 1);

        return ['total' => $total, 'completed' => $completed, 'avg_score' => $avgScore];
    }

    /**
     * Get progress grouped by PDSP domain
     */
    public function getProgressByDomain($studentId) {
        $stmt = $this->db->prepare("
            SELECT
                lp.pdsp_domain AS domain,
                COUNT(DISTINCT act.id) AS total,
                COUNT(DISTINCT sub.activity_id) AS completed,
                ROUND(AVG(g.score / NULLIF(g.max_score, 0) * 100), 1) AS avg_score
            FROM lesson_plans lp
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            JOIN lms_activities act ON act.lesson_plan_id = lp.id
            LEFT JOIN lms_submissions sub
                   ON sub.activity_id = act.id AND sub.student_id = :student_id
            LEFT JOIN lms_grades g ON g.submission_id = sub.id
            WHERE la.student_id = :student_id2 AND lp.status = 'published'
            GROUP BY lp.pdsp_domain
            ORDER BY lp.pdsp_domain ASC
        ");
        $stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming activities with due dates (within 7 days)
     */
    public function getUpcomingDue($studentId) {
        $stmt = $this->db->prepare("
            SELECT act.*, lp.title AS lesson_plan_title,
                   sub.id AS submission_id
            FROM lms_activities act
            JOIN lesson_plans lp ON act.lesson_plan_id = lp.id
            JOIN lesson_assignments la ON lp.id = la.lesson_plan_id
            LEFT JOIN lms_submissions sub
                   ON sub.activity_id = act.id AND sub.student_id = :student_id
            WHERE la.student_id = :student_id2
              AND lp.status = 'published'
              AND act.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
            ORDER BY act.due_date ASC
        ");
        $stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);
        return $stmt->fetchAll();
    }
}
