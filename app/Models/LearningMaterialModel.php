<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Learning Material Model

require_once __DIR__ . '/../../config/db.php';

class LearningMaterialModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create learning material
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO learning_materials (
                    learner_iep_id, material_name, material_type, file_path, 
                    description, is_assignment, due_date, points, uploaded_by
                ) VALUES (
                    :learner_iep_id, :material_name, :material_type, :file_path,
                    :description, :is_assignment, :due_date, :points, :uploaded_by
                )
            ");
            
            $stmt->execute([
                'learner_iep_id' => $data['learner_iep_id'],
                'material_name' => $data['material_name'],
                'material_type' => $data['material_type'],
                'file_path' => $data['file_path'] ?? null,
                'description' => $data['description'] ?? '',
                'is_assignment' => $data['is_assignment'] ?? false,
                'due_date' => $data['due_date'] ?? null,
                'points' => $data['points'] ?? 0,
                'uploaded_by' => $data['uploaded_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create material: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get materials by student ID
     */
    public function getByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    lm.*,
                    lp.status as progress_status,
                    lp.completed_at,
                    lp.time_spent_minutes,
                    lp.stars_earned,
                    at.activity_type,
                    asub.id as submission_id,
                    asub.submitted_at,
                    asub.graded,
                    asub.grade
                FROM learning_materials lm
                JOIN learner_iep li ON lm.learner_iep_id = li.id
                LEFT JOIN learner_progress lp ON lm.id = lp.material_id AND lp.student_id = :student_id
                LEFT JOIN activity_templates at ON lm.id = at.material_id
                LEFT JOIN assignment_submissions asub ON lm.id = asub.material_id AND asub.student_id = :student_id
                WHERE li.student_id = :student_id
                ORDER BY lm.uploaded_at DESC
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get materials by student: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get materials by learner IEP ID
     */
    public function getByLearnerIepId($learnerIepId) {
        try {
            $stmt = $this->db->prepare("
                SELECT lm.*, u.name as uploaded_by_name
                FROM learning_materials lm
                JOIN users u ON lm.uploaded_by = u.id
                WHERE lm.learner_iep_id = :learner_iep_id
                ORDER BY lm.uploaded_at DESC
            ");
            
            $stmt->execute(['learner_iep_id' => $learnerIepId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get materials by IEP: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get material by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT lm.*, u.name as uploaded_by_name
                FROM learning_materials lm
                JOIN users u ON lm.uploaded_by = u.id
                WHERE lm.id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get material by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete material
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM learning_materials WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log('Failed to delete material: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count materials by student
     */
    public function countByStudent($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM learning_materials lm
                JOIN learner_iep li ON lm.learner_iep_id = li.id
                WHERE li.student_id = :student_id
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log('Failed to count materials: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get modules only (not assignments)
     */
    public function getModulesByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    lm.*,
                    lp.status as progress_status,
                    lp.completed_at,
                    lp.stars_earned,
                    at.activity_type
                FROM learning_materials lm
                JOIN learner_iep li ON lm.learner_iep_id = li.id
                LEFT JOIN learner_progress lp ON lm.id = lp.material_id AND lp.student_id = :student_id
                LEFT JOIN activity_templates at ON lm.id = at.material_id
                WHERE li.student_id = :student_id
                AND lm.is_assignment = FALSE
                ORDER BY lm.uploaded_at DESC
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get modules: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get assignments only
     */
    public function getAssignmentsByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    lm.*,
                    asub.id as submission_id,
                    asub.submitted_at,
                    asub.graded,
                    asub.grade,
                    asub.teacher_feedback
                FROM learning_materials lm
                JOIN learner_iep li ON lm.learner_iep_id = li.id
                LEFT JOIN assignment_submissions asub ON lm.id = asub.material_id AND asub.student_id = :student_id
                WHERE li.student_id = :student_id
                AND lm.is_assignment = TRUE
                ORDER BY lm.due_date ASC, lm.uploaded_at DESC
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get assignments: ' . $e->getMessage());
            return [];
        }
    }
}
