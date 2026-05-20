<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Learner IEP Model

require_once __DIR__ . '/../../config/db.php';

class LearnerIEPModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Assign IEP to student
     */
    public function assignIEP($studentId, $iepId, $teacherId, $startDate, $notes = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO learner_iep (student_id, iep_id, teacher_id, start_date, notes, implementation_status)
                VALUES (:student_id, :iep_id, :teacher_id, :start_date, :notes, 'not_started')
            ");
            
            $stmt->execute([
                'student_id' => $studentId,
                'iep_id' => $iepId,
                'teacher_id' => $teacherId,
                'start_date' => $startDate,
                'notes' => $notes
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to assign IEP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all IEPs assigned by teacher
     */
    public function getByTeacherId($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    li.*,
                    sr.student_name,
                    sr.lrn,
                    COUNT(DISTINCT lm.id) as materials_count,
                    COUNT(DISTINCT CASE WHEN lp.status = 'completed' THEN lp.id END) as completed_count
                FROM learner_iep li
                JOIN student_records sr ON li.student_id = sr.id
                LEFT JOIN learning_materials lm ON li.id = lm.learner_iep_id
                LEFT JOIN learner_progress lp ON sr.id = lp.student_id AND lm.id = lp.material_id
                WHERE li.teacher_id = :teacher_id
                GROUP BY li.id
                ORDER BY li.created_at DESC
            ");
            
            $stmt->execute(['teacher_id' => $teacherId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get IEPs by teacher: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get IEP by student ID
     */
    public function getByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT li.*, sr.student_name, sr.lrn
                FROM learner_iep li
                JOIN student_records sr ON li.student_id = sr.id
                WHERE li.student_id = :student_id
                ORDER BY li.created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get IEP by student: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get IEP by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT li.*, sr.student_name, sr.lrn, u.name as teacher_name
                FROM learner_iep li
                JOIN student_records sr ON li.student_id = sr.id
                JOIN users u ON li.teacher_id = u.id
                WHERE li.id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get IEP by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update implementation status
     */
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE learner_iep 
                SET implementation_status = :status
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'status' => $status
            ]);
        } catch (PDOException $e) {
            error_log('Failed to update IEP status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update progress notes
     */
    public function updateProgress($id, $notes) {
        try {
            $stmt = $this->db->prepare("
                UPDATE learner_iep 
                SET notes = :notes, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'notes' => $notes
            ]);
        } catch (PDOException $e) {
            error_log('Failed to update IEP progress: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get students ready for IEP assignment (have approved P3 documents)
     */
    public function getStudentsReadyForAssignment() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sr.id as student_id,
                    sr.student_name,
                    sr.lrn,
                    p3.id as iep_p3_id,
                    p3.created_at as iep_created_at
                FROM student_records sr
                JOIN iep_p3_documents p3 ON sr.id = p3.student_id
                LEFT JOIN learner_iep li ON sr.id = li.student_id
                WHERE p3.status = 'signed_approved'
                AND li.id IS NULL
                ORDER BY p3.created_at DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get students ready for assignment: ' . $e->getMessage());
            return [];
        }
    }
}
