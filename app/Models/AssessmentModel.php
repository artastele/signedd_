<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Model

require_once __DIR__ . '/../../config/db.php';

class AssessmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create finalized assessment (Process 3 Section A + B complete)
     * Returns: assessment ID
     */
    public function createFinalized($studentId, $conductedBy, $sectionAData, $services, $screening, $sectionBData) {
        try {
            // Get current version for this student
            $stmt = $this->db->prepare("
                SELECT COALESCE(MAX(version), 0) + 1 as next_version
                FROM assessment_records
                WHERE student_id = :student_id
            ");
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch();
            $version = $result['next_version'];
            
            // Create assessment record
            $stmt = $this->db->prepare("
                INSERT INTO assessment_records (
                    student_id, assessed_by, conducted_by, status, version,
                    section_a_data, services_checked, screening_types,
                    created_at, updated_at
                ) VALUES (
                    :student_id, :assessed_by, :conducted_by, 'finalized', :version,
                    :section_a_data, :services_checked, :screening_types,
                    NOW(), NOW()
                )
            ");
            
            $result = $stmt->execute([
                'student_id' => $studentId,
                'assessed_by' => $conductedBy,
                'conducted_by' => $conductedBy,
                'version' => $version,
                'section_a_data' => json_encode($sectionAData),
                'services_checked' => json_encode($services),
                'screening_types' => json_encode($screening)
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create finalized assessment");
            }
            
            $assessmentId = $this->db->lastInsertId();
            
            // Save service checklist
            $this->saveServiceChecklist($assessmentId, $services);
            
            // Save MDT services (Section B)
            $this->saveAssessmentServices($assessmentId, $sectionBData);
            
            error_log("Created finalized assessment ID: $assessmentId (v$version) for student: $studentId");
            
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->createFinalized() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Finalize existing draft assessment
     * Returns: assessment ID
     */
    public function finalizeDraft($assessmentId, $sectionAData, $services, $screening, $sectionBData) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET section_a_data = :section_a_data,
                    services_checked = :services_checked,
                    screening_types = :screening_types,
                    status = 'finalized',
                    updated_at = NOW()
                WHERE id = :id AND status = 'draft'
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'section_a_data' => json_encode($sectionAData),
                'services_checked' => json_encode($services),
                'screening_types' => json_encode($screening)
            ]);
            
            if (!$result) {
                throw new Exception("Failed to finalize draft assessment");
            }
            
            // Update service checklist
            $this->saveServiceChecklist($assessmentId, $services);
            
            // Save MDT services (Section B)
            $this->saveAssessmentServices($assessmentId, $sectionBData);
            
            error_log("Finalized draft assessment ID: $assessmentId");
            
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->finalizeDraft() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save assessment services (Section B - MDT data)
     * Uses INSERT ... ON DUPLICATE KEY UPDATE to preserve existing service IDs
     * so that already-uploaded documents remain linked
     */
    private function saveAssessmentServices($assessmentId, $sectionBData) {
        try {
            if (empty($sectionBData)) {
                return;
            }

            // Get existing service names for this assessment
            $existingStmt = $this->db->prepare("
                SELECT id, service_name FROM assessment_services WHERE assessment_id = :assessment_id
            ");
            $existingStmt->execute(['assessment_id' => $assessmentId]);
            $existing = [];
            foreach ($existingStmt->fetchAll() as $row) {
                $existing[$row['service_name']] = $row['id'];
            }

            $updateStmt = $this->db->prepare("
                UPDATE assessment_services
                SET mdt_members = :mdt_members, date_of_assessment = :date_of_assessment
                WHERE id = :id
            ");
            $insertStmt = $this->db->prepare("
                INSERT INTO assessment_services (
                    assessment_id, service_name, mdt_members, date_of_assessment, created_at
                ) VALUES (
                    :assessment_id, :service_name, :mdt_members, :date_of_assessment, NOW()
                )
            ");

            foreach ($sectionBData as $serviceName => $data) {
                if (isset($existing[$serviceName])) {
                    // Update existing row — preserves the ID so documents stay linked
                    $updateStmt->execute([
                        'id' => $existing[$serviceName],
                        'mdt_members' => json_encode($data['members']),
                        'date_of_assessment' => $data['date']
                    ]);
                } else {
                    // Insert new row
                    $insertStmt->execute([
                        'assessment_id' => $assessmentId,
                        'service_name' => $serviceName,
                        'mdt_members' => json_encode($data['members']),
                        'date_of_assessment' => $data['date']
                    ]);
                }
            }
            
        } catch (Exception $e) {
            error_log("AssessmentModel->saveAssessmentServices() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save service document (single file - backward compatibility)
     */
    public function saveServiceDocument($assessmentId, $serviceName, $filePath, $fileType, $originalName) {
        try {
            // Get service ID
            $stmt = $this->db->prepare("
                SELECT id FROM assessment_services
                WHERE assessment_id = :assessment_id AND service_name = :service_name
                LIMIT 1
            ");
            $stmt->execute([
                'assessment_id' => $assessmentId,
                'service_name' => $serviceName
            ]);
            $service = $stmt->fetch();
            
            if (!$service) {
                throw new Exception("Service not found: $serviceName");
            }
            
            $serviceId = $service['id'];
            
            // Insert document
            $stmt = $this->db->prepare("
                INSERT INTO assessment_documents (
                    assessment_service_id, file_path, file_type, original_name, uploaded_at
                ) VALUES (
                    :service_id, :file_path, :file_type, :original_name, NOW()
                )
            ");
            
            $result = $stmt->execute([
                'service_id' => $serviceId,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'original_name' => $originalName
            ]);
            
            if (!$result) {
                throw new Exception("Failed to save document");
            }
            
            error_log("Saved document for service: $serviceName (assessment: $assessmentId)");
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("AssessmentModel->saveServiceDocument() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save multiple documents for a service
     */
    public function saveServiceDocuments($assessmentId, $serviceName, $files) {
        try {
            // Get service ID
            $stmt = $this->db->prepare("
                SELECT id FROM assessment_services
                WHERE assessment_id = :assessment_id AND service_name = :service_name
                LIMIT 1
            ");
            $stmt->execute([
                'assessment_id' => $assessmentId,
                'service_name' => $serviceName
            ]);
            $service = $stmt->fetch();
            
            if (!$service) {
                throw new Exception("Service not found: $serviceName");
            }
            
            $serviceId = $service['id'];
            $savedIds = [];
            
            // Insert each document
            $stmt = $this->db->prepare("
                INSERT INTO assessment_documents (
                    assessment_service_id, file_path, file_type, original_name, uploaded_at
                ) VALUES (
                    :service_id, :file_path, :file_type, :original_name, NOW()
                )
            ");
            
            foreach ($files as $file) {
                $result = $stmt->execute([
                    'service_id' => $serviceId,
                    'file_path' => $file['path'],
                    'file_type' => $file['type'],
                    'original_name' => $file['original_name']
                ]);
                
                if ($result) {
                    $savedIds[] = $this->db->lastInsertId();
                }
            }
            
            error_log("Saved " . count($savedIds) . " documents for service: $serviceName (assessment: $assessmentId)");
            
            return $savedIds;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->saveServiceDocuments() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific service document
     */
    public function deleteServiceDocument($documentId) {
        try {
            // Get document info first
            $stmt = $this->db->prepare("
                SELECT file_path FROM assessment_documents WHERE id = :id
            ");
            $stmt->execute(['id' => $documentId]);
            $document = $stmt->fetch();
            
            if (!$document) {
                return false;
            }
            
            // Delete from database
            $stmt = $this->db->prepare("
                DELETE FROM assessment_documents WHERE id = :id
            ");
            $result = $stmt->execute(['id' => $documentId]);
            
            // Delete physical file
            if ($result) {
                $filePath = __DIR__ . '/../../public/uploads/' . $document['file_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            error_log("Deleted document ID: $documentId");
            return $result;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->deleteServiceDocument() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create draft assessment (Process 3 Section A)
     * Returns: assessment ID
     */
    public function createDraft($studentId, $conductedBy, $sectionAData, $services, $screening) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO assessment_records (
                    student_id, conducted_by, status, section_a_data,
                    services_checked, screening_types, created_at, updated_at
                ) VALUES (
                    :student_id, :conducted_by, 'draft', :section_a_data,
                    :services_checked, :screening_types, NOW(), NOW()
                )
            ");
            
            $result = $stmt->execute([
                'student_id' => $studentId,
                'conducted_by' => $conductedBy,
                'section_a_data' => json_encode($sectionAData),
                'services_checked' => json_encode($services),
                'screening_types' => json_encode($screening)
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create draft assessment");
            }
            
            $assessmentId = $this->db->lastInsertId();
            
            // Save checked services to assessment_checklists table
            $this->saveServiceChecklist($assessmentId, $services);
            
            error_log("Created draft assessment ID: $assessmentId for student: $studentId");
            
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->createDraft() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update draft assessment (Process 3 Section A)
     * Returns: assessment ID
     */
    public function updateDraft($assessmentId, $sectionAData, $services, $screening) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET section_a_data = :section_a_data,
                    services_checked = :services_checked,
                    screening_types = :screening_types,
                    updated_at = NOW()
                WHERE id = :id AND status = 'draft'
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'section_a_data' => json_encode($sectionAData),
                'services_checked' => json_encode($services),
                'screening_types' => json_encode($screening)
            ]);
            
            if (!$result) {
                throw new Exception("Failed to update draft assessment");
            }
            
            // Update service checklist
            $this->saveServiceChecklist($assessmentId, $services);
            
            error_log("Updated draft assessment ID: $assessmentId");
            
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->updateDraft() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save service checklist to assessment_checklists table
     * Also ensures assessment_services rows exist for any checked services
     * so that file uploads during draft save can be linked correctly
     */
    private function saveServiceChecklist($assessmentId, $services) {
        try {
            // Delete existing checklist entries
            $stmt = $this->db->prepare("
                DELETE FROM assessment_checklists WHERE assessment_id = :assessment_id
            ");
            $stmt->execute(['assessment_id' => $assessmentId]);
            
            // Insert new checklist entries
            if (!empty($services)) {
                $stmt = $this->db->prepare("
                    INSERT INTO assessment_checklists (assessment_id, service_type, checked)
                    VALUES (:assessment_id, :service_type, TRUE)
                ");
                
                foreach ($services as $service) {
                    $stmt->execute([
                        'assessment_id' => $assessmentId,
                        'service_type' => $service
                    ]);
                }

                // Ensure assessment_services rows exist for each checked service
                // so file uploads during draft save can be linked
                $checkStmt = $this->db->prepare("
                    SELECT id FROM assessment_services
                    WHERE assessment_id = :assessment_id AND service_name = :service_name
                    LIMIT 1
                ");
                $insertStmt = $this->db->prepare("
                    INSERT INTO assessment_services (assessment_id, service_name, mdt_members, date_of_assessment, created_at)
                    VALUES (:assessment_id, :service_name, '[]', NULL, NOW())
                ");
                foreach ($services as $service) {
                    $checkStmt->execute(['assessment_id' => $assessmentId, 'service_name' => $service]);
                    if (!$checkStmt->fetch()) {
                        $insertStmt->execute(['assessment_id' => $assessmentId, 'service_name' => $service]);
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("AssessmentModel->saveServiceChecklist() FAILED: " . $e->getMessage());
            // Don't throw - checklist save failure shouldn't block draft save
        }
    }

    /**
     * Create assessment from parent submission
     * Returns: assessment ID
     */
    public function create($studentId, $educationHistory, $assessmentInfo, $submittedBy) {
        try {
            // Get current quarter (Q1 or Q2 based on month)
            $month = (int)date('m');
            $quarter = ($month >= 1 && $month <= 6) ? 'Q1' : 'Q2';
            
            // Check if assessment already exists for this quarter
            $existing = $this->getByQuarter($studentId, $quarter);
            if ($existing) {
                // Update existing assessment instead of creating new
                return $this->update($existing['id'], $educationHistory, $assessmentInfo);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO assessment_records (
                    student_id, parent_id, submitted_data, education_history,
                    assessment_info, status, quarter, submitted_at, assessed_by
                ) VALUES (
                    :student_id, :parent_id, :submitted_data, :education_history,
                    :assessment_info, 'pending', :quarter, NOW(), :assessed_by
                )
            ");
            
            $result = $stmt->execute([
                'student_id' => $studentId,
                'parent_id' => $submittedBy,
                'submitted_data' => json_encode([
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'submitted_by' => $submittedBy
                ]),
                'education_history' => json_encode($educationHistory),
                'assessment_info' => json_encode($assessmentInfo),
                'quarter' => $quarter,
                'assessed_by' => $submittedBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create assessment");
            }
            
            $assessmentId = $this->db->lastInsertId();
            error_log("Created assessment ID: $assessmentId for student: $studentId, quarter: $quarter");
            
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->create() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update assessment
     */
    private function update($assessmentId, $educationHistory, $assessmentInfo) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET education_history = :education_history,
                    assessment_info = :assessment_info,
                    submitted_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'education_history' => json_encode($educationHistory),
                'assessment_info' => json_encode($assessmentInfo)
            ]);
            
            if (!$result) {
                throw new Exception("Failed to update assessment");
            }
            
            error_log("Updated assessment ID: $assessmentId");
            return $assessmentId;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->update() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assessment by ID
     */
    public function findById($assessmentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn, sr.date_of_birth,
                   u.name as conducted_by_name
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            LEFT JOIN users u ON ar.conducted_by = u.id
            WHERE ar.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $assessmentId]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Decode JSON fields
            $result['section_a_data'] = json_decode($result['section_a_data'], true);
            $result['services_checked'] = json_decode($result['services_checked'], true);
            $result['screening_types'] = json_decode($result['screening_types'], true);
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
            $result['submitted_data'] = json_decode($result['submitted_data'], true);
        }
        
        return $result;
    }

    /**
     * Get all assessments for a student
     */
    public function getByStudentId($studentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id
            ORDER BY ar.quarter DESC, ar.submitted_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $results;
    }

    /**
     * Get latest assessment for a student
     */
    public function getLatest($studentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id
            ORDER BY ar.submitted_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $result;
    }

    /**
     * Get all assessments with student info (for history view)
     */
    public function getAllWithStudentInfo() {
        $stmt = $this->db->prepare("
            SELECT 
                ar.id,
                ar.student_id,
                ar.status,
                ar.version,
                ar.created_at,
                ar.updated_at,
                sr.student_name,
                sr.lrn,
                u.name as conducted_by_name
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            LEFT JOIN users u ON ar.conducted_by = u.id
            ORDER BY ar.created_at DESC
        ");
        $stmt->execute();
        $assessments = $stmt->fetchAll();
        
        // Get services and documents for each assessment
        foreach ($assessments as &$assessment) {
            $assessment['services'] = $this->getAssessmentServices($assessment['id']);
        }
        
        return $assessments;
    }

    /**
     * Get assessment by quarter
     */
    public function getByQuarter($studentId, $quarter) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id AND ar.quarter = :quarter
            ORDER BY ar.submitted_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId, 'quarter' => $quarter]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $result;
    }

    /**
     * Get all pending assessments for SPED teacher review
     */
    public function getPendingForReview() {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn, sr.date_of_birth,
                   u.name as parent_name, u.email as parent_email
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            JOIN users u ON ar.parent_id = u.id
            WHERE ar.status = 'pending'
            ORDER BY ar.submitted_at DESC
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $results;
    }

    /**
     * Approve assessment
     */
    public function approve($assessmentId, $approvedBy) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET status = 'approved',
                    reviewed_by = :reviewed_by,
                    reviewed_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'reviewed_by' => $approvedBy
            ]);
            
            if (!$result) {
                throw new Exception("Failed to approve assessment");
            }
            
            error_log("Approved assessment ID: $assessmentId by user: $approvedBy");
            return true;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->approve() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reject assessment
     */
    public function reject($assessmentId, $rejectedBy, $reason) {
        try {
            $stmt = $this->db->prepare("
                UPDATE assessment_records
                SET status = 'rejected',
                    reviewed_by = :reviewed_by,
                    review_note = :review_note,
                    reviewed_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                'id' => $assessmentId,
                'reviewed_by' => $rejectedBy,
                'review_note' => $reason
            ]);
            
            if (!$result) {
                throw new Exception("Failed to reject assessment");
            }
            
            error_log("Rejected assessment ID: $assessmentId by user: $rejectedBy");
            return true;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->reject() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get assessment history with full details for a student
     */
    public function getHistoryWithDetails($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ar.*,
                    sr.student_name,
                    sr.lrn,
                    u.name as conducted_by_name
                FROM assessment_records ar
                JOIN student_records sr ON ar.student_id = sr.id
                LEFT JOIN users u ON ar.conducted_by = u.id
                WHERE ar.student_id = :student_id
                ORDER BY ar.version DESC, ar.created_at DESC
            ");
            $stmt->execute(['student_id' => $studentId]);
            $assessments = $stmt->fetchAll();
            
            // Decode JSON fields and get related data for each assessment
            foreach ($assessments as &$assessment) {
                $assessment['section_a_data'] = json_decode($assessment['section_a_data'], true);
                $assessment['services_checked'] = json_decode($assessment['services_checked'], true);
                $assessment['screening_types'] = json_decode($assessment['screening_types'], true);
                
                // Get MDT services for this assessment
                $assessment['mdt_services'] = $this->getAssessmentServices($assessment['id']);
            }
            
            return $assessments;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->getHistoryWithDetails() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get assessment services (MDT data) for an assessment
     */
    public function getAssessmentServices($assessmentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    asv.*,
                    (SELECT COUNT(*) FROM assessment_documents WHERE assessment_service_id = asv.id) as document_count
                FROM assessment_services asv
                WHERE asv.assessment_id = :assessment_id
                ORDER BY asv.service_name
            ");
            $stmt->execute(['assessment_id' => $assessmentId]);
            $services = $stmt->fetchAll();
            
            // Decode MDT members JSON
            foreach ($services as &$service) {
                $service['mdt_members'] = json_decode($service['mdt_members'], true);
                
                // Get documents for this service
                $service['documents'] = $this->getServiceDocuments($service['id']);
            }
            
            return $services;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->getAssessmentServices() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get documents for a specific service
     */
    public function getServiceDocuments($serviceId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM assessment_documents
                WHERE assessment_service_id = :service_id
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute(['service_id' => $serviceId]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("AssessmentModel->getServiceDocuments() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get specific version details
     */
    public function getVersionDetails($assessmentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ar.*,
                    sr.student_name,
                    sr.lrn,
                    sr.date_of_birth,
                    u.name as conducted_by_name,
                    u.email as conducted_by_email
                FROM assessment_records ar
                JOIN student_records sr ON ar.student_id = sr.id
                LEFT JOIN users u ON ar.conducted_by = u.id
                WHERE ar.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $assessmentId]);
            $assessment = $stmt->fetch();
            
            if ($assessment) {
                $assessment['section_a_data'] = json_decode($assessment['section_a_data'], true);
                $assessment['services_checked'] = json_decode($assessment['services_checked'], true);
                $assessment['screening_types'] = json_decode($assessment['screening_types'], true);
                $assessment['mdt_services'] = $this->getAssessmentServices($assessment['id']);
            }
            
            return $assessment;
            
        } catch (Exception $e) {
            error_log("AssessmentModel->getVersionDetails() FAILED: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get assessment history for a student
     */
    public function getHistory($studentId) {
        $stmt = $this->db->prepare("
            SELECT ar.*, sr.student_name, sr.lrn
            FROM assessment_records ar
            JOIN student_records sr ON ar.student_id = sr.id
            WHERE ar.student_id = :student_id
            ORDER BY ar.quarter DESC, ar.submitted_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['education_history'] = json_decode($result['education_history'], true);
            $result['assessment_info'] = json_decode($result['assessment_info'], true);
        }
        
        return $results;
    }

    /**
     * Get students ready for assessment (verified but no assessment yet)
     */
    public function getStudentsReadyForAssessment() {
        $stmt = $this->db->prepare("
            SELECT sr.*, u.name as parent_name, u.email as parent_email
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            JOIN users u ON es.parent_id = u.id
            WHERE sr.id NOT IN (
                SELECT DISTINCT student_id FROM assessment_records
            )
            ORDER BY sr.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
