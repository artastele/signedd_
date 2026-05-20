<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-07
// Part of: SPED LMS — Assessment Service Model

require_once __DIR__ . '/../../config/db.php';

class AssessmentServiceModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Save multiple documents for a service
     */
    public function saveDocuments($assessmentServiceId, $files) {
        try {
            $savedFiles = [];
            
            foreach ($files as $file) {
                $stmt = $this->db->prepare("
                    INSERT INTO assessment_documents (
                        assessment_service_id, file_path, file_type, original_name, uploaded_at
                    ) VALUES (
                        :service_id, :file_path, :file_type, :original_name, NOW()
                    )
                ");
                
                $result = $stmt->execute([
                    'service_id' => $assessmentServiceId,
                    'file_path' => $file['path'],
                    'file_type' => $file['type'],
                    'original_name' => $file['original_name']
                ]);
                
                if ($result) {
                    $savedFiles[] = $this->db->lastInsertId();
                }
            }
            
            return $savedFiles;
            
        } catch (Exception $e) {
            error_log("AssessmentServiceModel->saveDocuments() FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all documents for a service
     */
    public function getDocuments($assessmentServiceId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM assessment_documents
                WHERE assessment_service_id = :service_id
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute(['service_id' => $assessmentServiceId]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("AssessmentServiceModel->getDocuments() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete a specific document
     */
    public function deleteDocument($documentId) {
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
            
            return $result;
            
        } catch (Exception $e) {
            error_log("AssessmentServiceModel->deleteDocument() FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all services for an assessment with their documents
     */
    public function getByAssessmentId($assessmentId) {
        try {
            // Get all services for this assessment
            $stmt = $this->db->prepare("
                SELECT * FROM assessment_services
                WHERE assessment_id = :assessment_id
                ORDER BY created_at ASC
            ");
            $stmt->execute(['assessment_id' => $assessmentId]);
            $services = $stmt->fetchAll();
            
            // Load documents for each service
            foreach ($services as &$service) {
                $service['documents'] = $this->getDocuments($service['id']);
            }
            
            return $services;
            
        } catch (Exception $e) {
            error_log("AssessmentServiceModel->getByAssessmentId() FAILED: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get service by ID
     */
    public function findById($serviceId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM assessment_services WHERE id = :id LIMIT 1
            ");
            $stmt->execute(['id' => $serviceId]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("AssessmentServiceModel->findById() FAILED: " . $e->getMessage());
            return null;
        }
    }
}
