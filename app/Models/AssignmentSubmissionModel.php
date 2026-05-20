<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Assignment Submission Model

require_once __DIR__ . '/../../config/db.php';

class AssignmentSubmissionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create assignment submission
     */
    public function create($materialId, $studentId, $submissionType, $filePath = null, $textAnswer = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO assignment_submissions (
                    material_id, student_id, submission_type, file_path, text_answer
                ) VALUES (
                    :material_id, :student_id, :submission_type, :file_path, :text_answer
                )
            ");
            
            $stmt->execute([
                'material_id' => $materialId,
                'student_id' => $studentId,
                'submission_type' => $submissionType,
                'file_path' => $filePath,
                'text_answer' => $textAnswer
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create submission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get submission by student and material
     */
    public function getByStudentAndMaterial($studentId, $materialId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM assignment_submissions
                WHERE student_id = :student_id AND material_id = :material_id
                ORDER BY submitted_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([
                'student_id' => $studentId,
                'material_id' => $materialId
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get submission: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get submissions by student
     */
    public function getByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    asub.*,
                    lm.material_name,
                    lm.due_date,
                    lm.points
                FROM assignment_submissions asub
                JOIN learning_materials lm ON asub.material_id = lm.id
                WHERE asub.student_id = :student_id
                ORDER BY asub.submitted_at DESC
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get submissions by student: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Grade submission
     */
    public function grade($submissionId, $grade, $feedback, $gradedBy) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assignment_submissions
                SET graded = TRUE,
                    grade = :grade,
                    teacher_feedback = :feedback,
                    graded_at = CURRENT_TIMESTAMP,
                    graded_by = :graded_by
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $submissionId,
                'grade' => $grade,
                'feedback' => $feedback,
                'graded_by' => $gradedBy
            ]);
        } catch (PDOException $e) {
            error_log('Failed to grade submission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count submissions by student
     */
    public function countByStudent($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM assignment_submissions
                WHERE student_id = :student_id
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log('Failed to count submissions: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get ungraded submissions for teacher
     */
    public function getUngradedByTeacher($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    asub.*,
                    lm.material_name,
                    sr.student_name,
                    sr.lrn
                FROM assignment_submissions asub
                JOIN learning_materials lm ON asub.material_id = lm.id
                JOIN learner_iep li ON lm.learner_iep_id = li.id
                JOIN student_records sr ON li.student_id = sr.id
                WHERE li.teacher_id = :teacher_id
                AND asub.graded = FALSE
                ORDER BY asub.submitted_at ASC
            ");
            
            $stmt->execute(['teacher_id' => $teacherId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get ungraded submissions: ' . $e->getMessage());
            return [];
        }
    }
}
