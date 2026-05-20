<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Learner Progress Model

require_once __DIR__ . '/../../config/db.php';

class LearnerProgressModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get or create progress record
     */
    public function getOrCreate($studentId, $materialId) {
        try {
            // Try to get existing
            $stmt = $this->db->prepare("
                SELECT * FROM learner_progress
                WHERE student_id = :student_id AND material_id = :material_id
            ");
            
            $stmt->execute([
                'student_id' => $studentId,
                'material_id' => $materialId
            ]);
            
            $progress = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$progress) {
                // Create new
                $stmt = $this->db->prepare("
                    INSERT INTO learner_progress (student_id, material_id, status)
                    VALUES (:student_id, :material_id, 'not_started')
                ");
                
                $stmt->execute([
                    'student_id' => $studentId,
                    'material_id' => $materialId
                ]);
                
                return $this->getOrCreate($studentId, $materialId);
            }
            
            return $progress;
        } catch (PDOException $e) {
            error_log('Failed to get/create progress: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update progress status
     */
    public function updateStatus($studentId, $materialId, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE learner_progress
                SET status = :status,
                    started_at = CASE WHEN started_at IS NULL AND :status != 'not_started' THEN CURRENT_TIMESTAMP ELSE started_at END,
                    completed_at = CASE WHEN :status = 'completed' THEN CURRENT_TIMESTAMP ELSE completed_at END
                WHERE student_id = :student_id AND material_id = :material_id
            ");
            
            return $stmt->execute([
                'student_id' => $studentId,
                'material_id' => $materialId,
                'status' => $status
            ]);
        } catch (PDOException $e) {
            error_log('Failed to update progress status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark as completed
     */
    public function markComplete($studentId, $materialId, $starsEarned = 1) {
        try {
            $this->getOrCreate($studentId, $materialId);
            
            $stmt = $this->db->prepare("
                UPDATE learner_progress
                SET status = 'completed',
                    completed_at = CURRENT_TIMESTAMP,
                    stars_earned = :stars_earned
                WHERE student_id = :student_id AND material_id = :material_id
            ");
            
            return $stmt->execute([
                'student_id' => $studentId,
                'material_id' => $materialId,
                'stars_earned' => $starsEarned
            ]);
        } catch (PDOException $e) {
            error_log('Failed to mark complete: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add time spent
     */
    public function addTimeSpent($studentId, $materialId, $minutes) {
        try {
            $this->getOrCreate($studentId, $materialId);
            
            $stmt = $this->db->prepare("
                UPDATE learner_progress
                SET time_spent_minutes = time_spent_minutes + :minutes,
                    status = CASE WHEN status = 'not_started' THEN 'in_progress' ELSE status END
                WHERE student_id = :student_id AND material_id = :material_id
            ");
            
            return $stmt->execute([
                'student_id' => $studentId,
                'material_id' => $materialId,
                'minutes' => $minutes
            ]);
        } catch (PDOException $e) {
            error_log('Failed to add time spent: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get progress by student
     */
    public function getByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    lp.*,
                    lm.material_name,
                    lm.material_type
                FROM learner_progress lp
                JOIN learning_materials lm ON lp.material_id = lm.id
                WHERE lp.student_id = :student_id
                ORDER BY lp.completed_at DESC, lp.started_at DESC
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get progress by student: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count completed materials
     */
    public function countCompleted($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM learner_progress
                WHERE student_id = :student_id AND status = 'completed'
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log('Failed to count completed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total stars earned
     */
    public function getTotalStars($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT SUM(stars_earned) as total_stars
                FROM learner_progress
                WHERE student_id = :student_id
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_stars'] ?? 0;
        } catch (PDOException $e) {
            error_log('Failed to get total stars: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate progress percentage
     */
    public function calculateProgressPercentage($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM learner_progress lp
                JOIN learning_materials lm ON lp.material_id = lm.id
                JOIN learner_iep li ON lm.learner_iep_id = li.id
                WHERE li.student_id = :student_id
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total = $result['total'] ?? 0;
            $completed = $result['completed'] ?? 0;
            
            return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
        } catch (PDOException $e) {
            error_log('Failed to calculate progress: ' . $e->getMessage());
            return 0;
        }
    }
}
