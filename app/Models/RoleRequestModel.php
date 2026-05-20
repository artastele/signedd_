<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Role Request Model

require_once __DIR__ . '/../../config/db.php';

class RoleRequestModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new role request
     */
    public function create($userId, $requestedRole, $submittedDocs = null) {
        // Determine approver based on requested role
        $approverRole = ($requestedRole === 'principal') ? 'admin' : 'principal';
        
        $stmt = $this->db->prepare("
            INSERT INTO role_requests (user_id, requested_role, status, approver_role, submitted_docs)
            VALUES (:user_id, :requested_role, 'pending', :approver_role, :submitted_docs)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'requested_role' => $requestedRole,
            'approver_role' => $approverRole,
            'submitted_docs' => $submittedDocs ? json_encode($submittedDocs) : null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find role request by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT rr.*, u.name as user_name, u.email as user_email,
                   reviewer.name as reviewer_name
            FROM role_requests rr
            JOIN users u ON rr.user_id = u.id
            LEFT JOIN users reviewer ON rr.reviewed_by = reviewer.id
            WHERE rr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all pending role requests
     */
    public function getPending() {
        $stmt = $this->db->query("
            SELECT rr.*, u.name as user_name, u.email as user_email
            FROM role_requests rr
            JOIN users u ON rr.user_id = u.id
            WHERE rr.status = 'pending'
            ORDER BY rr.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get pending requests by approver role
     */
    public function getPendingByApprover($approverRole) {
        $stmt = $this->db->prepare("
            SELECT rr.*, u.name as user_name, u.email as user_email
            FROM role_requests rr
            JOIN users u ON rr.user_id = u.id
            WHERE rr.status = 'pending' AND rr.approver_role = :approver_role
            ORDER BY rr.created_at DESC
        ");
        $stmt->execute(['approver_role' => $approverRole]);
        return $stmt->fetchAll();
    }

    /**
     * Get role requests by user ID
     */
    public function getByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM role_requests
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get pending request for user
     */
    public function getPendingByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM role_requests
            WHERE user_id = :user_id AND status = 'pending'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Update role request status
     */
    public function updateStatus($requestId, $status, $reviewedBy, $reviewNote = null) {
        $stmt = $this->db->prepare("
            UPDATE role_requests
            SET status = :status,
                reviewed_by = :reviewed_by,
                review_note = :review_note,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'review_note' => $reviewNote,
            'id' => $requestId
        ]);
    }

    /**
     * Save uploaded document
     */
    public function saveDocument($roleRequestId, $filePath, $fileType) {
        $stmt = $this->db->prepare("
            INSERT INTO role_documents (role_request_id, file_path, file_type)
            VALUES (:role_request_id, :file_path, :file_type)
        ");

        return $stmt->execute([
            'role_request_id' => $roleRequestId,
            'file_path' => $filePath,
            'file_type' => $fileType
        ]);
    }

    /**
     * Get documents for role request
     */
    public function getDocuments($roleRequestId) {
        $stmt = $this->db->prepare("
            SELECT * FROM role_documents
            WHERE role_request_id = :role_request_id
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute(['role_request_id' => $roleRequestId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if user has pending request
     */
    public function hasPendingRequest($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM role_requests
            WHERE user_id = :user_id AND status = 'pending'
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get all role requests (admin view)
     */
    public function getAll($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT rr.*, u.name as user_name, u.email as user_email,
                   reviewer.name as reviewer_name
            FROM role_requests rr
            JOIN users u ON rr.user_id = u.id
            LEFT JOIN users reviewer ON rr.reviewed_by = reviewer.id
            ORDER BY 
                CASE rr.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'approved' THEN 2 
                    WHEN 'rejected' THEN 3 
                END,
                rr.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get latest rejected request for user
     */
    public function getLatestRejectedByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT rr.*, reviewer.name as reviewer_name
            FROM role_requests rr
            LEFT JOIN users reviewer ON rr.reviewed_by = reviewer.id
            WHERE rr.user_id = :user_id AND rr.status = 'rejected'
            ORDER BY rr.updated_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Get all requests for user (application history)
     */
    public function getAllByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT rr.*, reviewer.name as reviewer_name
            FROM role_requests rr
            LEFT JOIN users reviewer ON rr.reviewed_by = reviewer.id
            WHERE rr.user_id = :user_id
            ORDER BY rr.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
