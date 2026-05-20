<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — PDSP Model

require_once __DIR__ . '/../../config/db.php';

class PDSPModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create PDSP record
     */
    public function create($meetingId, $studentId, $filledBy) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pdsp_records (
                    meeting_id, student_id, filled_by, status, created_at, updated_at
                ) VALUES (
                    :meeting_id, :student_id, :filled_by, 'draft', NOW(), NOW()
                )
            ");
            
            $result = $stmt->execute([
                'meeting_id' => $meetingId,
                'student_id' => $studentId,
                'filled_by' => $filledBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create PDSP record");
            }
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("PDSPModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get PDSP by meeting ID
     */
    public function getByMeetingId($meetingId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pdsp_records
            WHERE meeting_id = :meeting_id
            LIMIT 1
        ");
        $stmt->execute(['meeting_id' => $meetingId]);
        return $stmt->fetch();
    }

    /**
     * Get PDSP by ID
     */
    public function findById($pdspId) {
        $stmt = $this->db->prepare("
            SELECT pr.*, sr.student_name, sr.lrn, u.name as filled_by_name
            FROM pdsp_records pr
            JOIN student_records sr ON pr.student_id = sr.id
            JOIN users u ON pr.filled_by = u.id
            WHERE pr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $pdspId]);
        return $stmt->fetch();
    }

    /**
     * Save domain data
     */
    public function saveDomain($pdspId, $domainData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pdsp_domains (
                    pdsp_id, domain_name, sub_domain, skills_description,
                    mastered, educational_recommendation, q1_level, q2_level
                ) VALUES (
                    :pdsp_id, :domain_name, :sub_domain, :skills_description,
                    :mastered, :educational_recommendation, :q1_level, :q2_level
                )
            ");
            
            return $stmt->execute([
                'pdsp_id' => $pdspId,
                'domain_name' => $domainData['domain_name'],
                'sub_domain' => $domainData['sub_domain'] ?? null,
                'skills_description' => $domainData['skills_description'] ?? null,
                'mastered' => $domainData['mastered'] ?? false,
                'educational_recommendation' => $domainData['educational_recommendation'] ?? null,
                'q1_level' => $domainData['q1_level'] ?? null,
                'q2_level' => $domainData['q2_level'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("PDSPModel->saveDomain() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all domains for a PDSP
     */
    public function getDomains($pdspId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pdsp_domains
            WHERE pdsp_id = :pdsp_id
            ORDER BY id ASC
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete all domains for a PDSP (for re-saving)
     */
    public function deleteDomains($pdspId) {
        $stmt = $this->db->prepare("
            DELETE FROM pdsp_domains WHERE pdsp_id = :pdsp_id
        ");
        return $stmt->execute(['pdsp_id' => $pdspId]);
    }

    /**
     * Update PDSP record
     */
    public function update($pdspId, $data) {
        try {
            $fields = [];
            $params = ['id' => $pdspId];
            
            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params['status'] = $data['status'];
            }
            
            if (isset($data['signed_document_path'])) {
                $fields[] = "signed_document_path = :signed_document_path";
                $params['signed_document_path'] = $data['signed_document_path'];
            }
            
            if (isset($data['signatories'])) {
                $fields[] = "signatories = :signatories";
                $params['signatories'] = $data['signatories'];
            }
            
            $fields[] = "updated_at = NOW()";
            
            $sql = "UPDATE pdsp_records SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("PDSPModel->update() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload signed document
     */
    public function uploadSignedDocument($pdspId, $filePath) {
        try {
            $stmt = $this->db->prepare("
                UPDATE pdsp_records 
                SET signed_document_path = :file_path, updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $pdspId,
                'file_path' => $filePath
            ]);
            
        } catch (Exception $e) {
            error_log("PDSPModel->uploadSignedDocument() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark PDSP as signed with signatories
     */
    public function markAsSigned($pdspId, $signatories) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Update PDSP record status and set completed_at
            $stmt = $this->db->prepare("
                UPDATE pdsp_records 
                SET status = 'signed', 
                    signatories = :signatories,
                    completed_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $signatoriesJson = json_encode($signatories);
            $stmt->execute([
                'id' => $pdspId,
                'signatories' => $signatoriesJson
            ]);
            
            // Delete existing signatories (if any)
            $stmt = $this->db->prepare("DELETE FROM pdsp_signatories WHERE pdsp_id = :pdsp_id");
            $stmt->execute(['pdsp_id' => $pdspId]);
            
            // Insert new signatories into pdsp_signatories table
            $stmt = $this->db->prepare("
                INSERT INTO pdsp_signatories (pdsp_id, signatory_role, signatory_name)
                VALUES (:pdsp_id, :signatory_role, :signatory_name)
            ");
            
            foreach ($signatories as $signatory) {
                $stmt->execute([
                    'pdsp_id' => $pdspId,
                    'signatory_role' => $signatory['role'],
                    'signatory_name' => $signatory['name']
                ]);
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollBack();
            error_log("PDSPModel->markAsSigned() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get signatories as array from pdsp_signatories table
     */
    public function getSignatories($pdspId) {
        $stmt = $this->db->prepare("
            SELECT signatory_role as role, signatory_name as name, created_at
            FROM pdsp_signatories
            WHERE pdsp_id = :pdsp_id
            ORDER BY created_at ASC
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if signed document exists
     */
    public function hasSignedDocument($pdspId) {
        $pdsp = $this->findById($pdspId);
        return !empty($pdsp['signed_document_path']);
    }
}
