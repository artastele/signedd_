<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — PDSP Signatory Model

require_once __DIR__ . '/../../config/db.php';

class PDSPSignatoryModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create signatory record
     */
    public function create($pdspId, $signatoryRole, $signatoryName) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pdsp_signatories (pdsp_id, signatory_role, signatory_name)
                VALUES (:pdsp_id, :signatory_role, :signatory_name)
            ");
            
            return $stmt->execute([
                'pdsp_id' => $pdspId,
                'signatory_role' => $signatoryRole,
                'signatory_name' => $signatoryName
            ]);
            
        } catch (Exception $e) {
            error_log("PDSPSignatoryModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all signatories for a PDSP
     */
    public function getByPdspId($pdspId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pdsp_signatories
            WHERE pdsp_id = :pdsp_id
            ORDER BY created_at ASC
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete all signatories for a PDSP
     */
    public function deleteByPdspId($pdspId) {
        $stmt = $this->db->prepare("
            DELETE FROM pdsp_signatories WHERE pdsp_id = :pdsp_id
        ");
        return $stmt->execute(['pdsp_id' => $pdspId]);
    }

    /**
     * Count signatories for a PDSP
     */
    public function countByPdspId($pdspId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM pdsp_signatories
            WHERE pdsp_id = :pdsp_id
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
