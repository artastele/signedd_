<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Model

require_once __DIR__ . '/../../config/db.php';

class EnrollmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new enrollment (draft or submission)
     * SIMPLE BASIC SQL - NO COMPLEXITY
     */
    public function create($data) {
        // Validate required parameters
        $required = ['parent_id', 'last_name', 'first_name', 'birth_date', 'sex', 'grade_level_to_enroll', 'is_draft', 'status'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new Exception("Missing required field: $field");
            }
        }

        try {
            error_log("=== ENROLLMENT CREATE START ===");
            error_log("Parent ID: {$data['parent_id']}");
            error_log("Student: {$data['first_name']} {$data['last_name']}");
            
            // Build simple SQL with basic escaping
            $parent_id = (int)$data['parent_id'];
            $enrollment_type = $this->db->quote($data['enrollment_type'] ?? 'new');
            $school_year = $this->db->quote($data['school_year'] ?? '2026-2027');
            $is_draft = (int)($data['is_draft'] ? 1 : 0);
            $status = $this->db->quote($data['status']);
            $lrn = $this->db->quote($data['lrn'] ?? '');
            $last_name = $this->db->quote($data['last_name']);
            $first_name = $this->db->quote($data['first_name']);
            $middle_name = $this->db->quote($data['middle_name'] ?? '');
            $extension_name = $this->db->quote($data['extension_name'] ?? '');
            $birth_date = $this->db->quote($data['birth_date']);
            $sex = $this->db->quote($data['sex']);
            $age = isset($data['age']) ? (int)$data['age'] : 'NULL';
            $birth_place = $this->db->quote($data['birth_place'] ?? '');
            $mother_tongue = $this->db->quote($data['mother_tongue'] ?? '');
            $is_indigenous_people = (int)($data['is_indigenous_people'] ?? 0);
            $indigenous_group = $this->db->quote($data['indigenous_group'] ?? '');
            $is_4ps_beneficiary = (int)($data['is_4ps_beneficiary'] ?? 0);
            $fourps_household_id = $this->db->quote($data['fourps_household_id'] ?? '');
            $disability_visual = (int)($data['disability_visual'] ?? 0);
            $disability_hearing = (int)($data['disability_hearing'] ?? 0);
            $disability_learning = (int)($data['disability_learning'] ?? 0);
            $disability_speech = (int)($data['disability_speech'] ?? 0);
            $disability_intellectual = (int)($data['disability_intellectual'] ?? 0);
            $disability_physical = (int)($data['disability_physical'] ?? 0);
            $disability_emotional = (int)($data['disability_emotional'] ?? 0);
            $disability_chronic_illness = (int)($data['disability_chronic_illness'] ?? 0);
            $disability_others = (int)($data['disability_others'] ?? 0);
            $disability_others_specify = $this->db->quote($data['disability_others_specify'] ?? '');
            $current_house_no = $this->db->quote($data['current_house_no'] ?? '');
            $current_barangay = $this->db->quote($data['current_barangay'] ?? '');
            $current_city = $this->db->quote($data['current_city'] ?? '');
            $current_province = $this->db->quote($data['current_province'] ?? '');
            $current_zip_code = $this->db->quote($data['current_zip_code'] ?? '');
            $same_as_current_address = (int)($data['same_as_current_address'] ?? 0);
            $permanent_house_no = $this->db->quote($data['permanent_house_no'] ?? '');
            $permanent_barangay = $this->db->quote($data['permanent_barangay'] ?? '');
            $permanent_city = $this->db->quote($data['permanent_city'] ?? '');
            $permanent_province = $this->db->quote($data['permanent_province'] ?? '');
            $permanent_zip_code = $this->db->quote($data['permanent_zip_code'] ?? '');
            $father_last_name = $this->db->quote($data['father_last_name'] ?? '');
            $father_first_name = $this->db->quote($data['father_first_name'] ?? '');
            $father_middle_name = $this->db->quote($data['father_middle_name'] ?? '');
            $father_contact_number = $this->db->quote($data['father_contact_number'] ?? '');
            $mother_maiden_last_name = $this->db->quote($data['mother_maiden_last_name'] ?? '');
            $mother_first_name = $this->db->quote($data['mother_first_name'] ?? '');
            $mother_middle_name = $this->db->quote($data['mother_middle_name'] ?? '');
            $mother_contact_number = $this->db->quote($data['mother_contact_number'] ?? '');
            $guardian_last_name = $this->db->quote($data['guardian_last_name'] ?? '');
            $guardian_first_name = $this->db->quote($data['guardian_first_name'] ?? '');
            $guardian_middle_name = $this->db->quote($data['guardian_middle_name'] ?? '');
            $guardian_contact_number = $this->db->quote($data['guardian_contact_number'] ?? '');
            $previous_school_id = isset($data['previous_school_id']) ? (int)$data['previous_school_id'] : 'NULL';
            $previous_school_name = $this->db->quote($data['previous_school_name'] ?? '');
            $previous_school_address = $this->db->quote($data['previous_school_address'] ?? '');
            $previous_grade_level = $this->db->quote($data['previous_grade_level'] ?? '');
            $previous_school_year = $this->db->quote($data['previous_school_year'] ?? '');
            $previous_school_type = $this->db->quote($data['previous_school_type'] ?? '');
            $grade_level_to_enroll = $this->db->quote($data['grade_level_to_enroll']);
            $is_balik_aral = (int)($data['is_balik_aral'] ?? 0);
            $is_pept_passer = (int)($data['is_pept_passer'] ?? 0);
            $pept_rating = $this->db->quote($data['pept_rating'] ?? '');
            $is_als_passer = (int)($data['is_als_passer'] ?? 0);
            $als_rating = $this->db->quote($data['als_rating'] ?? '');
            $shs_track = $this->db->quote($data['shs_track'] ?? '');
            $shs_strand = $this->db->quote($data['shs_strand'] ?? '');
            $shs_semester = $this->db->quote($data['shs_semester'] ?? '');
            $modality_modular_print = (int)($data['modality_modular_print'] ?? 0);
            $modality_modular_digital = (int)($data['modality_modular_digital'] ?? 0);
            $modality_online = (int)($data['modality_online'] ?? 0);
            $modality_educational_tv = (int)($data['modality_educational_tv'] ?? 0);
            $modality_radio = (int)($data['modality_radio'] ?? 0);
            $modality_blended = (int)($data['modality_blended'] ?? 0);
            $modality_face_to_face = (int)($data['modality_face_to_face'] ?? 0);
            $preferred_distance_modality = $this->db->quote($data['preferred_distance_modality'] ?? '');
            $signature_data = $this->db->quote($data['signature_data'] ?? '');
            $date_signed = $this->db->quote($data['date_signed'] ?? date('Y-m-d'));
            $draft_saved_at = isset($data['draft_saved_at']) ? $this->db->quote($data['draft_saved_at']) : 'NULL';
            $submitted_at = isset($data['submitted_at']) ? $this->db->quote($data['submitted_at']) : 'NULL';
            $verified_by = isset($data['verified_by']) ? (int)$data['verified_by'] : 'NULL';
            $verified_at = isset($data['verified_at']) ? $this->db->quote($data['verified_at']) : 'NULL';

            // Build SQL
            $sql = "INSERT INTO enrollment_submissions (
                parent_id, enrollment_type, school_year, is_draft, status,
                lrn, last_name, first_name, middle_name, extension_name, birth_date, sex, age,
                birth_place, mother_tongue, is_indigenous_people, indigenous_group, is_4ps_beneficiary, fourps_household_id,
                disability_visual, disability_hearing, disability_learning, disability_speech,
                disability_intellectual, disability_physical, disability_emotional,
                disability_chronic_illness, disability_others, disability_others_specify,
                current_house_no, current_barangay, current_city, current_province, current_zip_code,
                same_as_current_address, permanent_house_no, permanent_barangay, permanent_city,
                permanent_province, permanent_zip_code,
                father_last_name, father_first_name, father_middle_name, father_contact_number,
                mother_maiden_last_name, mother_first_name, mother_middle_name, mother_contact_number,
                guardian_last_name, guardian_first_name, guardian_middle_name, guardian_contact_number,
                previous_school_id, previous_school_name, previous_school_address,
                previous_grade_level, previous_school_year, previous_school_type,
                grade_level_to_enroll, is_balik_aral, is_pept_passer, pept_rating, is_als_passer, als_rating,
                shs_track, shs_strand, shs_semester,
                modality_modular_print, modality_modular_digital, modality_online,
                modality_educational_tv, modality_radio, modality_blended, modality_face_to_face,
                preferred_distance_modality, signature_data, date_signed,
                draft_saved_at, submitted_at, verified_by, verified_at
            ) VALUES (
                $parent_id, $enrollment_type, $school_year, $is_draft, $status,
                $lrn, $last_name, $first_name, $middle_name, $extension_name, $birth_date, $sex, $age,
                $birth_place, $mother_tongue, $is_indigenous_people, $indigenous_group, $is_4ps_beneficiary, $fourps_household_id,
                $disability_visual, $disability_hearing, $disability_learning, $disability_speech,
                $disability_intellectual, $disability_physical, $disability_emotional,
                $disability_chronic_illness, $disability_others, $disability_others_specify,
                $current_house_no, $current_barangay, $current_city, $current_province, $current_zip_code,
                $same_as_current_address, $permanent_house_no, $permanent_barangay, $permanent_city,
                $permanent_province, $permanent_zip_code,
                $father_last_name, $father_first_name, $father_middle_name, $father_contact_number,
                $mother_maiden_last_name, $mother_first_name, $mother_middle_name, $mother_contact_number,
                $guardian_last_name, $guardian_first_name, $guardian_middle_name, $guardian_contact_number,
                $previous_school_id, $previous_school_name, $previous_school_address,
                $previous_grade_level, $previous_school_year, $previous_school_type,
                $grade_level_to_enroll, $is_balik_aral, $is_pept_passer, $pept_rating, $is_als_passer, $als_rating,
                $shs_track, $shs_strand, $shs_semester,
                $modality_modular_print, $modality_modular_digital, $modality_online,
                $modality_educational_tv, $modality_radio, $modality_blended, $modality_face_to_face,
                $preferred_distance_modality, $signature_data, $date_signed,
                $draft_saved_at, $submitted_at, $verified_by, $verified_at
            )";

            error_log("SQL: " . substr($sql, 0, 200) . "...");
            
            // Execute
            $result = $this->db->exec($sql);
            error_log("Exec result: $result");
            
            $insertId = $this->db->lastInsertId();
            error_log("Insert ID: $insertId");
            
            if (!$insertId) {
                throw new Exception("No insert ID returned");
            }
            
            error_log("=== ENROLLMENT CREATE SUCCESS ===");
            return $insertId;
            
        } catch (Exception $e) {
            error_log("=== ENROLLMENT CREATE FAILED ===");
            error_log("Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update existing enrollment (handles partial data safely)
     */
    public function update($enrollmentId, $data) {
        // Build SET clause only for fields that exist in $data
        $fields = [];
        $params = [];
        
        // List of all valid columns (excluding id, created_at)
        $validColumns = [
            'parent_id', 'enrollment_type', 'school_year', 'previous_enrollment_id', 'is_draft', 'status',
            'lrn', 'last_name', 'first_name', 'middle_name', 'extension_name', 'birth_date', 'sex', 'age',
            'birth_place', 'mother_tongue', 'is_indigenous_people', 'indigenous_group', 'is_4ps_beneficiary', 
            'fourps_household_id', 'disability_visual', 'disability_hearing', 'disability_learning', 
            'disability_speech', 'disability_intellectual', 'disability_physical', 'disability_emotional',
            'disability_chronic_illness', 'disability_others', 'disability_others_specify',
            'current_house_no', 'current_barangay', 'current_city', 'current_province', 'current_zip_code',
            'same_as_current_address', 'permanent_house_no', 'permanent_barangay', 'permanent_city',
            'permanent_province', 'permanent_zip_code', 'father_last_name', 'father_first_name', 
            'father_middle_name', 'father_contact_number', 'mother_maiden_last_name', 'mother_first_name', 
            'mother_middle_name', 'mother_contact_number', 'guardian_last_name', 'guardian_first_name', 
            'guardian_middle_name', 'guardian_contact_number', 'previous_school_id', 'previous_school_name', 
            'previous_school_address', 'previous_grade_level', 'previous_school_year', 'previous_school_type',
            'grade_level_to_enroll', 'is_balik_aral', 'is_pept_passer', 'pept_rating', 'is_als_passer', 
            'als_rating', 'shs_track', 'shs_strand', 'shs_semester', 'modality_modular_print', 
            'modality_modular_digital', 'modality_online', 'modality_educational_tv', 'modality_radio', 
            'modality_blended', 'modality_face_to_face', 'preferred_distance_modality', 'signature_data', 
            'date_signed', 'draft_saved_at', 'submitted_at', 'verified_by', 'verified_at', 'last_activity',
            'learner_account_created', 'lrn'
        ];
        
        // Only include fields that exist in $data and are valid columns
        foreach ($data as $key => $value) {
            if (in_array($key, $validColumns)) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            error_log("EnrollmentModel->update() - No valid fields to update");
            return false;
        }
        
        // Always update last_activity
        $fields[] = "last_activity = NOW()";
        
        $sql = "UPDATE enrollment_submissions SET " . implode(', ', $fields) . " WHERE id = :id";
        $params['id'] = $enrollmentId;
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("EnrollmentModel->update() FAILED for ID: $enrollmentId");
                error_log("Error: " . json_encode($stmt->errorInfo()));
            } else {
                error_log("EnrollmentModel->update() SUCCESS for ID: $enrollmentId");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("EnrollmentModel->update() EXCEPTION: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save draft (create or update)
     */
    public function saveDraft($parentId, $data) {
        // Check if draft exists
        $draft = $this->getDraftByParentId($parentId);
        
        // Ensure parent_id is set correctly
        $data['parent_id'] = $parentId;
        $data['is_draft'] = true;
        $data['status'] = 'draft';
        $data['draft_saved_at'] = date('Y-m-d H:i:s');
        
        if ($draft) {
            // Update existing draft
            $this->update($draft['id'], $data);
            return $draft['id'];
        } else {
            // Create new draft
            return $this->create($data);
        }
    }

    /**
     * Get draft by parent ID
     */
    public function getDraftByParentId($parentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM enrollment_submissions
            WHERE parent_id = :parent_id AND is_draft = TRUE
            ORDER BY draft_saved_at DESC
            LIMIT 1
        ");
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetch();
    }

    /**
     * Get latest enrollment by parent ID (for returning students)
     */
    public function getLatestByParentId($parentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM enrollment_submissions
            WHERE parent_id = :parent_id AND is_draft = FALSE
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetch();
    }

    /**
     * Find enrollment by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT es.*, u.name as parent_name, u.email as parent_email,
                   verifier.name as verifier_name
            FROM enrollment_submissions es
            JOIN users u ON es.parent_id = u.id
            LEFT JOIN users verifier ON es.verified_by = verifier.id
            WHERE es.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all enrollments by parent ID
     */
    public function getByParentId($parentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM enrollment_submissions
            WHERE parent_id = :parent_id AND is_draft = FALSE
            ORDER BY created_at DESC
        ");
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all pending enrollments (for SPED teacher)
     */
    public function getPending() {
        $stmt = $this->db->query("
            SELECT es.*, u.name as parent_name, u.email as parent_email
            FROM enrollment_submissions es
            JOIN users u ON es.parent_id = u.id
            WHERE es.status = 'pending' AND es.is_draft = FALSE
            ORDER BY es.submitted_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all enrollments (for SPED teacher/admin)
     */
    public function getAll($limit = 100) {
        $stmt = $this->db->prepare("
            SELECT es.*, u.name as parent_name, u.email as parent_email,
                   verifier.name as verifier_name
            FROM enrollment_submissions es
            JOIN users u ON es.parent_id = u.id
            LEFT JOIN users verifier ON es.verified_by = verifier.id
            WHERE es.is_draft = FALSE
            ORDER BY 
                CASE es.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'verified' THEN 2 
                    WHEN 'rejected' THEN 3 
                END,
                es.submitted_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Submit enrollment (convert draft to pending)
     */
    public function submit($enrollmentId, $parentId) {
        $stmt = $this->db->prepare("
            UPDATE enrollment_submissions
            SET is_draft = FALSE,
                status = 'pending',
                submitted_at = NOW()
            WHERE id = :id AND parent_id = :parent_id
        ");
        return $stmt->execute([
            'id' => $enrollmentId,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Update enrollment status
     */
    public function updateStatus($enrollmentId, $status, $verifiedBy = null, $reviewNote = null) {
        $sql = "UPDATE enrollment_submissions
                SET status = :status,
                    verified_by = :verified_by,
                    verified_at = NOW(),
                    review_note = :review_note
                WHERE id = :id";
        
        return $this->db->prepare($sql)->execute([
            'status' => $status,
            'verified_by' => $verifiedBy,
            'review_note' => $reviewNote,
            'id' => $enrollmentId
        ]);
    }

    /**
     * Mark learner account as created
     */
    public function markLearnerAccountCreated($enrollmentId) {
        $stmt = $this->db->prepare("
            UPDATE enrollment_submissions
            SET learner_account_created = TRUE
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $enrollmentId]);
    }

    // ============================================
    // DOCUMENT MANAGEMENT
    // ============================================

    /**
     * Add document to enrollment
     */
    public function addDocument($enrollmentId, $documentType, $filePath) {
        $stmt = $this->db->prepare("
            INSERT INTO enrollment_documents (enrollment_id, document_type, file_path, status)
            VALUES (:enrollment_id, :document_type, :file_path, 'pending')
        ");
        return $stmt->execute([
            'enrollment_id' => $enrollmentId,
            'document_type' => $documentType,
            'file_path' => $filePath
        ]);
    }

    /**
     * Get documents for enrollment
     */
    public function getDocuments($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT ed.*, u.name as reviewer_name
            FROM enrollment_documents ed
            LEFT JOIN users u ON ed.reviewed_by = u.id
            WHERE ed.enrollment_id = :enrollment_id
            ORDER BY ed.uploaded_at DESC
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        return $stmt->fetchAll();
    }

    /**
     * Update document status (approve/reject individual document)
     */
    public function updateDocumentStatus($documentId, $status, $reviewedBy, $reviewNote = null) {
        $stmt = $this->db->prepare("
            UPDATE enrollment_documents
            SET status = :status,
                reviewed_by = :reviewed_by,
                review_note = :review_note,
                reviewed_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'review_note' => $reviewNote,
            'id' => $documentId
        ]);
    }

    /**
     * Check if all documents are approved
     */
    public function areAllDocumentsApproved($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
            FROM enrollment_documents
            WHERE enrollment_id = :enrollment_id
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        $result = $stmt->fetch();
        
        return $result['total'] > 0 && $result['total'] == $result['approved'];
    }

    /**
     * Get rejected documents for enrollment
     */
    public function getRejectedDocuments($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM enrollment_documents
            WHERE enrollment_id = :enrollment_id AND status = 'rejected'
            ORDER BY reviewed_at DESC
        ");
        $stmt->execute(['enrollment_id' => $enrollmentId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete enrollment (and cascade documents)
     */
    public function delete($enrollmentId, $parentId) {
        $stmt = $this->db->prepare("
            DELETE FROM enrollment_submissions
            WHERE id = :id AND parent_id = :parent_id
        ");
        return $stmt->execute([
            'id' => $enrollmentId,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Get enrollments with document counts for parent dashboard
     */
    public function getEnrollmentsWithStats($parentId) {
        $stmt = $this->db->prepare("
            SELECT 
                es.*,
                COUNT(ed.id) as total_documents,
                SUM(CASE WHEN ed.status = 'approved' THEN 1 ELSE 0 END) as approved_documents,
                SUM(CASE WHEN ed.status = 'rejected' THEN 1 ELSE 0 END) as rejected_documents,
                SUM(CASE WHEN ed.status = 'pending' THEN 1 ELSE 0 END) as pending_documents
            FROM enrollment_submissions es
            LEFT JOIN enrollment_documents ed ON es.id = ed.enrollment_id
            WHERE es.parent_id = :parent_id AND es.is_draft = FALSE
            GROUP BY es.id
            ORDER BY es.submitted_at DESC
        ");
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get enrollment statistics for parent
     */
    public function getParentStats($parentId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM enrollment_submissions
            WHERE parent_id = :parent_id AND is_draft = FALSE
        ");
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetch();
    }

    /**
     * Clean up old drafts (older than 7 days)
     */
    public function cleanupOldDrafts() {
        $stmt = $this->db->prepare("
            DELETE FROM enrollment_submissions
            WHERE is_draft = TRUE 
            AND last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $result = $stmt->execute();
        $deletedCount = $stmt->rowCount();
        
        if ($deletedCount > 0) {
            error_log("Cleaned up $deletedCount old draft(s)");
        }
        
        return $deletedCount;
    }

    /**
     * Delete draft by parent ID
     */
    public function deleteDraftByParentId($parentId) {
        $stmt = $this->db->prepare("
            DELETE FROM enrollment_submissions
            WHERE parent_id = :parent_id AND is_draft = TRUE
        ");
        return $stmt->execute(['parent_id' => $parentId]);
    }

    /**
     * Search student by LRN for returning enrollment
     * Optionally filter by school year
     */
    public function searchByLRN($lrn, $schoolYear = null) {
        $sql = "
            SELECT * FROM enrollment_submissions
            WHERE lrn = :lrn 
            AND is_draft = FALSE
            AND status IN ('verified', 'pending')
        ";
        
        $params = ['lrn' => $lrn];
        
        if ($schoolYear) {
            $sql .= " AND school_year = :school_year";
            $params['school_year'] = $schoolYear;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Search students by name for returning enrollment
     * Returns array of matching enrollments
     * Optionally filter by school year
     */
    public function searchByName($lastName, $firstName, $middleName = '', $suffix = '', $schoolYear = null) {
        $sql = "
            SELECT * FROM enrollment_submissions
            WHERE last_name = :last_name
            AND first_name = :first_name
            AND is_draft = FALSE
            AND status IN ('verified', 'pending')
        ";
        
        $params = [
            'last_name' => $lastName,
            'first_name' => $firstName
        ];
        
        // Add optional filters
        if (!empty($middleName)) {
            $sql .= " AND middle_name = :middle_name";
            $params['middle_name'] = $middleName;
        }
        
        if (!empty($suffix)) {
            $sql .= " AND extension_name = :suffix";
            $params['suffix'] = $suffix;
        }
        
        if ($schoolYear) {
            $sql .= " AND school_year = :school_year";
            $params['school_year'] = $schoolYear;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
