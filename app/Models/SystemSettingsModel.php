<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 4
// Last modified: 2026-05-04
// Part of: SPED LMS — System Settings Model

require_once __DIR__ . '/../../config/db.php';

class SystemSettingsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get single setting value
     */
    public function getSetting($key, $default = null) {
        $stmt = $this->db->prepare("
            SELECT setting_value 
            FROM system_settings 
            WHERE setting_key = :key
            LIMIT 1
        ");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Get all settings as associative array
     */
    public function getAllSettings() {
        $stmt = $this->db->query("
            SELECT setting_key, setting_value, category, description
            FROM system_settings
            ORDER BY category, setting_key
        ");
        $results = $stmt->fetchAll();
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'category' => $row['category'],
                'description' => $row['description']
            ];
        }
        
        return $settings;
    }

    /**
     * Get settings by category
     */
    public function getSettingsByCategory($category) {
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value, description
            FROM system_settings
            WHERE category = :category
            ORDER BY setting_key
        ");
        $stmt->execute(['category' => $category]);
        $results = $stmt->fetchAll();
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'description' => $row['description']
            ];
        }
        
        return $settings;
    }

    /**
     * Update setting value
     */
    public function updateSetting($key, $value) {
        $stmt = $this->db->prepare("
            UPDATE system_settings
            SET setting_value = :value,
                updated_at = CURRENT_TIMESTAMP
            WHERE setting_key = :key
        ");
        
        return $stmt->execute([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Update multiple settings at once
     */
    public function updateMultipleSettings($settings) {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->updateSetting($key, $value);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Failed to update settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new setting
     */
    public function createSetting($key, $value, $category, $description = '') {
        $stmt = $this->db->prepare("
            INSERT INTO system_settings (setting_key, setting_value, category, description)
            VALUES (:key, :value, :category, :description)
        ");
        
        return $stmt->execute([
            'key' => $key,
            'value' => $value,
            'category' => $category,
            'description' => $description
        ]);
    }

    /**
     * Check if setting exists
     */
    public function settingExists($key) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM system_settings
            WHERE setting_key = :key
        ");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}
