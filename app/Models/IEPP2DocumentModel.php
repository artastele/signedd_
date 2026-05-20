<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P2 Document Model

require_once __DIR__ . '/../../config/db.php';

class IEPP2DocumentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create IEP P2 document
     */
    public function create($meetingId, $studentId, $iepData, $createdBy) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_p2_documents (
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
                throw new Exception("Failed to create IEP P2 document");
            }
            
            $documentId = $this->db->lastInsertId();
            $this->logAudit($documentId, $createdBy, 'created', 'IEP P2 document created');
            error_log("Created IEP P2 document ID: $documentId for meeting: $meetingId");
            
            return $documentId;
            
        } catch (Exception $e) {
            error_log("IEPP2DocumentModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get IEP P2 document by ID
     */
    public function findById($documentId) {
        $stmt = $this->db->prepare("
            SELECT p2.*, sr.student_name, sr.lrn, im.meeting_date
            FROM iep_p2_documents p2
            JOIN student_records sr ON p2.student_id = sr.id
            JOIN iep_meetings im ON p2.meeting_id = im.id
            WHERE p2.id = :id
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
     * Get IEP P2 by meeting ID
     */
    public function getByMeetingId($meetingId) {
        $stmt = $this->db->prepare("
            SELECT p2.*, sr.student_name, sr.lrn
            FROM iep_p2_documents p2
            JOIN student_records sr ON p2.student_id = sr.id
            WHERE p2.meeting_id = :meeting_id
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
                UPDATE iep_p2_documents
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
            error_log("Uploaded PDF for IEP P2 document ID: $documentId");
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPP2DocumentModel->uploadPDF() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send for review
     */
    public function sendForReview($documentId, $reviewerId) {
        try {
            // Check if already sent
            $stmt = $this->db->prepare("
                SELECT id FROM iep_p2_reviews
                WHERE iep_p2_id = :doc_id AND reviewer_id = :reviewer_id
            ");
            $stmt->execute(['doc_id' => $documentId, 'reviewer_id' => $reviewerId]);
            
            if ($stmt->fetch()) {
                throw new Exception("Already sent for review to this user");
            }
            
            // Update document status if not already pending
            $stmt = $this->db->prepare("
                UPDATE iep_p2_documents
                SET status = 'pending_review'
                WHERE id = :id AND status = 'draft'
            ");
            $stmt->execute(['id' => $documentId]);
            
            $this->logAudit($documentId, $_SESSION['user_id'] ?? null, 'sent_for_review', "Sent for review to user: $reviewerId");
            error_log("Sent IEP P2 document ID: $documentId for review to user: $reviewerId");
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPP2DocumentModel->sendForReview() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add review and signature
     */
    public function addReview($documentId, $reviewerId, $reviewerRole, $feedback, $signatureData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO iep_p2_reviews (
                    iep_p2_id, reviewer_id, reviewer_role, feedback, signature_data
                ) VALUES (
                    :doc_id, :reviewer_id, :reviewer_role, :feedback, :signature_data
                )
                ON DUPLICATE KEY UPDATE
                    feedback = :feedback,
                    signature_data = :signature_data,
                    reviewed_at = NOW()
            ");
            
            $result = $stmt->execute([
                'doc_id' => $documentId,
                'reviewer_id' => $reviewerId,
                'reviewer_role' => $reviewerRole,
                'feedback' => $feedback,
                'signature_data' => $signatureData
            ]);
            
            if (!$result) {
                throw new Exception("Failed to add review");
            }
            
            // Check if all required reviewers have signed
            $this->checkAllReviewsComplete($documentId);
            
            $this->logAudit($documentId, $reviewerId, 'reviewed_signed', "Reviewed and signed by: $reviewerRole");
            error_log("Added review for IEP P2 document ID: $documentId by user: $reviewerId");
            
            return true;
            
        } catch (Exception $e) {
            error_log("IEPP2DocumentModel->addReview() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get review status
     */
    public function getReviewStatus($documentId) {
        $stmt = $this->db->prepare("
            SELECT reviewer_role, reviewer_id, feedback, reviewed_at
            FROM iep_p2_reviews
            WHERE iep_p2_id = :doc_id
            ORDER BY reviewed_at DESC
        ");
        $stmt->execute(['doc_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if all reviews complete
     */
    private function checkAllReviewsComplete($documentId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT reviewer_role) as reviewed_count
            FROM iep_p2_reviews
            WHERE iep_p2_id = :doc_id
        ");
        $stmt->execute(['doc_id' => $documentId]);
        $result = $stmt->fetch();
        
        // If all required roles have reviewed (at least 2: guidance/principal + parent)
        if ($result['reviewed_count'] >= 2) {
            $stmt = $this->db->prepare("
                UPDATE iep_p2_documents
                SET status = 'reviewed_signed'
                WHERE id = :id
            ");
            $stmt->execute(['id' => $documentId]);
        }
    }

    /**
     * Get all IEP P2 documents
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT p2.*, sr.student_name, sr.lrn
            FROM iep_p2_documents p2
            JOIN student_records sr ON p2.student_id = sr.id
            ORDER BY p2.created_at DESC
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
                    'p2', :doc_id, :user_id, :action, :details, :ip_address
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
