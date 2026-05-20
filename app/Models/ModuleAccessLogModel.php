<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Module Access Log Model

require_once __DIR__ . '/../../config/db.php';

class ModuleAccessLogModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Log module access
     */
    public function logAccess($studentId, $moduleName, $durationMinutes = 0) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO module_access_logs (student_id, module_name, duration_minutes)
                VALUES (:student_id, :module_name, :duration_minutes)
            ");
            
            return $stmt->execute([
                'student_id' => $studentId,
                'module_name' => $moduleName,
                'duration_minutes' => $durationMinutes
            ]);
        } catch (PDOException $e) {
            error_log('Failed to log module access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get access logs by student
     */
    public function getByStudentId($studentId, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM module_access_logs
                WHERE student_id = :student_id
                ORDER BY accessed_at DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue('student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get access logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total time spent by student
     */
    public function getTotalTimeSpent($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT SUM(duration_minutes) as total_minutes
                FROM module_access_logs
                WHERE student_id = :student_id
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_minutes'] ?? 0;
        } catch (PDOException $e) {
            error_log('Failed to get total time spent: ' . $e->getMessage());
            return 0;
        }
    }
}
