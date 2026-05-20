<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P3 Document Model

require_once __DIR__ . '/../../config/db.php';

class IEPP3DocumentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create IEP P3 document
     */
    public function create($meetingId, $studentId, $iepData, $createdBy) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_p3_documents (
                    meeting_id, student_id, iep_data, status, created_by
                ) VALUES (
                    :meeting_id, :student_id, :iep_data, 'draft', :created_by
                )
            ");
            
            $result = $stmt->execute([
                'meeting_id' => $meetingId,
                'student_id' => $studentId,
                'iep_data' => json_encode($iepData),
                'created_by' => $createdBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create IEP P3 document");
            }
            
            $documentId = $this->db->lastInsertId();
            $this->logAudit($documentId, $createdBy, 'created', 'IEP P3 document created');
            error_log("Created IEP P3 document ID: $documentId for meeting: $meetingId");
            
            return $documentId;
            
        } catch (Exception $e) {
            error_log("IEPP3DocumentModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get IEP P3 document by ID
     */
    public function findById($documentId) {
        $stmt = $this->db->prepare("
            SELECT p3.*, sr.student_name, sr.lrn, im.meeting_date
            FROM iep_p3_documents p3
            JOIN student_records sr ON p3.student_id = sr.id
            JOIN iep_meetings im ON p3.meeting_id = im.id
            WHERE p3.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $documentId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['iep_data'] = json_decode($result['iep_data'], true);
        }
        
        return $result;
    }

    /**
     * Get IEP P3 by meeting ID
     */
    public function getByMeetingId($meetingId) {
        $stmt = $this->db->prepare("
            SELECT p3.*, sr.student_name, sr.lrn
            FROM iep_p3_documents p3
            JOIN student_records sr ON p3.student_id = sr.id
            WHERE p3.meeting_id = :meeting_id
            LIMIT 1
        ");
        $stmt->execute(['meeting_id' => $meetingId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['iep_data'] = json_decode($result['iep_data'], true);
        }
        
        return $result;
    }

    /**
     * Upload PDF
     */
    public function uploadPDF($documentId, $filePath) {
        try {
            $stmt = $this->db->prepare("
                UPDATE iep_p3_documents
                SET pdf_path = :pdf_path, updated_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $documentId,
                'pdf_path' => $filePath
            ]);
            
            if (!$result) {
                throw new Exception("Failed to upload PDF");
            }
            
            $this->logAudit($documentId, $_SESSION['user_id'] ?? null, 'pdf_uploaded', 'PDF uploaded');
            error_log("Uploaded PDF for IEP P3 document ID: $documentId");
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPP3DocumentModel->uploadPDF() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send for signature
     */
    public function sendForSignature($documentId, $signerId, $signerRole) {
        try {
            // Check if already sent
            $stmt = $this->db->prepare("
                SELECT id FROM iep_p3_signatures
                WHERE iep_p3_id = :doc_id AND signer_role = :role
            ");
            $stmt->execute(['doc_id' => $documentId, 'role' => $signerRole]);
            
            if ($stmt->fetch()) {
                throw new Exception("Already sent for signature to this role");
            }
            
            // Update document status if not already pending
            $stmt = $this->db->prepare("
                UPDATE iep_p3_documents
                SET status = 'pending_signatures'
                WHERE id = :id AND status = 'draft'
            ");
            $stmt->execute(['id' => $documentId]);
            
            $this->logAudit($documentId, $_SESSION['user_id'] ?? null, 'sent_for_signature', "Sent for signature to: $signerRole");
            error_log("Sent IEP P3 document ID: $documentId for signature to role: $signerRole");
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPP3DocumentModel->sendForSignature() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send IEP to all required signatories at once
     * Sets status to pending_signatures and returns list of users to notify
     */
    public function sendToAllSignatories($documentId) {
        try {
            // Mark document as pending signatures
            $stmt = $this->db->prepare("
                UPDATE iep_p3_documents SET status = 'pending_signatures', updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute(['id' => $documentId]);

            $this->logAudit($documentId, $_SESSION['user_id'] ?? null, 'sent_to_all_signatories', 'IEP sent to all signatories');
            error_log("IEP P3 document ID: $documentId sent to all signatories");

            return true;
        } catch (Exception $e) {
            error_log("IEPP3DocumentModel->sendToAllSignatories() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add signature
     */
    public function addSignature($documentId, $signerId, $signerRole, $signatureData, $remarks = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_p3_signatures (
                    iep_p3_id, signer_id, signer_role, signature_data, remarks
                ) VALUES (
                    :doc_id, :signer_id, :signer_role, :signature_data, :remarks
                )
                ON DUPLICATE KEY UPDATE
                    signature_data = :signature_data,
                    remarks = :remarks,
                    signed_at = NOW()
            ");
            
            $result = $stmt->execute([
                'doc_id' => $documentId,
                'signer_id' => $signerId,
                'signer_role' => $signerRole,
                'signature_data' => $signatureData,
                'remarks' => $remarks
            ]);
            
            if (!$result) {
                throw new Exception("Failed to add signature");
            }
            
            // Check if all required signers have signed
            $this->checkAllSignaturesComplete($documentId);
            
            $this->logAudit($documentId, $signerId, 'signed', "Signed by: $signerRole");
            error_log("Added signature for IEP P3 document ID: $documentId by user: $signerId");
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPP3DocumentModel->addSignature() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get signature status
     */
    public function getSignatureStatus($documentId) {
        $stmt = $this->db->prepare("
            SELECT signer_role, signer_id, remarks, signed_at
            FROM iep_p3_signatures
            WHERE iep_p3_id = :doc_id
            ORDER BY signed_at DESC
        ");
        $stmt->execute(['doc_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if all signatures complete
     * Required signers: sped_teacher, parent, guidance, principal
     */
    private function checkAllSignaturesComplete($documentId) {
        $requiredRoles = ['sped_teacher', 'parent', 'guidance', 'principal'];
        
        $stmt = $this->db->prepare("
            SELECT DISTINCT signer_role FROM iep_p3_signatures
            WHERE iep_p3_id = :doc_id
        ");
        $stmt->execute(['doc_id' => $documentId]);
        $signedRoles = array_map(fn($row) => $row['signer_role'], $stmt->fetchAll());
        
        if (count(array_intersect($requiredRoles, $signedRoles)) === count($requiredRoles)) {
            $stmt = $this->db->prepare("
                UPDATE iep_p3_documents SET status = 'signed_approved' WHERE id = :id
            ");
            $stmt->execute(['id' => $documentId]);
            error_log("IEP P3 document ID: $documentId — all signatures complete, status set to signed_approved");
        }
    }

    /**
     * Get all IEP P3 documents
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT p3.*, sr.student_name, sr.lrn
            FROM iep_p3_documents p3
            JOIN student_records sr ON p3.student_id = sr.id
            ORDER BY p3.created_at DESC
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['iep_data'] = json_decode($result['iep_data'], true);
        }
        
        return $results;
    }

    /**
     * Log audit trail
     */
    private function logAudit($documentId, $userId, $action, $details) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_audit_log (
                    document_type, document_id, user_id, action, details, ip_address
                ) VALUES (
                    'p3', :doc_id, :user_id, :action, :details, :ip_address
                )
            ");
            
            $stmt->execute([
                'doc_id' => $documentId,
                'user_id' => $userId,
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Failed to log audit: " . $e->getMessage());
        }
    }
}
