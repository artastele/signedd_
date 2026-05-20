<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — Activity Template Model

require_once __DIR__ . '/../../config/db.php';

class ActivityTemplateModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create activity template
     */
    public function create($materialId, $activityType, $activityData, $instructions = '', $totalPoints = 0, $timeLimit = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_templates (
                    material_id, activity_type, activity_data, instructions, total_points, time_limit_minutes
                ) VALUES (
                    :material_id, :activity_type, :activity_data, :instructions, :total_points, :time_limit
                )
            ");
            
            $stmt->execute([
                'material_id' => $materialId,
                'activity_type' => $activityType,
                'activity_data' => json_encode($activityData),
                'instructions' => $instructions,
                'total_points' => $totalPoints,
                'time_limit' => $timeLimit
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create activity template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activity by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_templates WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($activity) {
                $activity['activity_data'] = json_decode($activity['activity_data'], true);
            }
            
            return $activity;
        } catch (PDOException $e) {
            error_log('Failed to get activity by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get activity by material ID
     */
    public function getByMaterialId($materialId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_templates WHERE material_id = :material_id
            ");
            
            $stmt->execute(['material_id' => $materialId]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($activity) {
                $activity['activity_data'] = json_decode($activity['activity_data'], true);
            }
            
            return $activity;
        } catch (PDOException $e) {
            error_log('Failed to get activity by material: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update activity template
     */
    public function update($id, $activityData, $instructions = '', $totalPoints = 0) {
        try {
            $stmt = $this->db->prepare("
                UPDATE activity_templates 
                SET activity_data = :activity_data,
                    instructions = :instructions,
                    total_points = :total_points
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'activity_data' => json_encode($activityData),
                'instructions' => $instructions,
                'total_points' => $totalPoints
            ]);
        } catch (PDOException $e) {
            error_log('Failed to update activity template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete activity template
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM activity_templates WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log('Failed to delete activity template: ' . $e->getMessage());
            return false;
        }
    }
}
