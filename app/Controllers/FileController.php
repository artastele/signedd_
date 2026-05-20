<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3 (File Serving — All Processes)
// Last modified: 2026-05-08
// Part of: SPED LMS — Secure File Access with Decryption
// Changes: Added 'assessment' and 'pdsp_document' type support;
//          smart encrypt/plain detection (isEncrypted check)

require_once __DIR__ . '/../Helpers/FileEncryptionHelper.php';

class FileController {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/db.php';
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * View file inline (with decryption)
     */
    public function view($type, $id) {
        $this->serveFile($type, $id, 'inline');
    }
    
    /**
     * Download file (with decryption)
     */
    public function download($type, $id) {
        $this->serveFile($type, $id, 'attachment');
    }
    
    /**
     * Serve file with decryption and permission check
     */
    private function serveFile($type, $id, $disposition = 'inline') {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            die('Unauthorized');
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'user';
        
        // Get file info based on type
        $fileInfo = $this->getFileInfo($type, $id);
        
        if (!$fileInfo) {
            http_response_code(404);
            die('File not found');
        }
        
        // Check permissions
        if (!$this->checkPermission($type, $fileInfo, $userId, $userRole)) {
            http_response_code(403);
            die('Access denied');
        }
        
        // Get file path
        $filePath = __DIR__ . '/../../public/' . $fileInfo['file_path'];
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found on server. Path: ' . $fileInfo['file_path']);
        }
        
        // Determine if file is encrypted (enrollment docs are encrypted,
        // assessment/pdsp docs are stored plain)
        $isEncrypted = FileEncryptionHelper::isEncrypted($fileInfo['file_path']);
        
        if ($isEncrypted) {
            // Decrypt and serve
            try {
                $fileContent = FileEncryptionHelper::decryptFile($filePath);
                if ($fileContent === false) {
                    http_response_code(500);
                    die('Failed to decrypt file');
                }
            } catch (Exception $e) {
                error_log('File decryption error: ' . $e->getMessage());
                http_response_code(500);
                die('Failed to decrypt file');
            }
        } else {
            // Serve plain file directly
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                http_response_code(500);
                die('Failed to read file');
            }
        }
        
        // Log file access
        $this->logFileAccess($type, $id, $userId, $disposition);
        
        // Get original filename and MIME type
        $originalName = $fileInfo['original_name'] ?? 'file';
        $mimeType = $this->getMimeType($originalName);
        
        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . strlen($fileContent));
        header('Content-Disposition: ' . $disposition . '; filename="' . $originalName . '"');
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        
        // Output file content
        echo $fileContent;
        exit;
    }
    
    /**
     * Get file info from database based on type
     */
    private function getFileInfo($type, $id) {
        switch ($type) {
            case 'enrollment_document':
                $stmt = $this->db->prepare("
                    SELECT ed.*, es.parent_id, ed.file_path,
                           SUBSTRING_INDEX(ed.file_path, '/', -1) as original_name
                    FROM enrollment_documents ed
                    JOIN enrollment_submissions es ON ed.enrollment_id = es.id
                    WHERE ed.id = :id
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();
                
            case 'role_document':
                $stmt = $this->db->prepare("
                    SELECT rd.*, rr.user_id, rd.file_path,
                           SUBSTRING_INDEX(rd.file_path, '/', -1) as original_name
                    FROM role_documents rd
                    JOIN role_requests rr ON rd.role_request_id = rr.id
                    WHERE rd.id = :id
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();
                
            case 'lesson_material':
                // New Process 6/7 lesson materials (lesson_materials table)
                $stmt = $this->db->prepare("
                    SELECT lm.id,
                           lm.file_path,
                           SUBSTRING_INDEX(lm.file_path, '/', -1) AS original_name,
                           lm.lesson_plan_id,
                           lp.created_by AS teacher_id,
                           lp.student_id
                    FROM lesson_materials lm
                    JOIN lesson_plans lp ON lm.lesson_plan_id = lp.id
                    WHERE lm.id = :id
                      AND lm.material_type = 'file'
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();

            case 'learning_material':
                $stmt = $this->db->prepare("
                    SELECT lm.*, li.teacher_id, lm.file_path, lm.material_name as original_name
                    FROM learning_materials lm
                    JOIN learner_iep li ON lm.learner_iep_id = li.id
                    WHERE lm.id = :id
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();
                
            case 'assignment_submission':
                $stmt = $this->db->prepare("
                    SELECT asub.*, asub.file_path, sr.id as student_record_id,
                           SUBSTRING_INDEX(asub.file_path, '/', -1) as original_name
                    FROM assignment_submissions asub
                    JOIN student_records sr ON asub.student_id = sr.id
                    WHERE asub.id = :id
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();
                
            case 'assessment':
                // Assessment service documents (uploaded during Process 3)
                // DB stores path as 'assessments/filename.pdf' (missing uploads/ prefix)
                // Files are plain (not encrypted) in public/uploads/assessments/
                $stmt = $this->db->prepare("
                    SELECT ad.id,
                           CASE
                               WHEN ad.file_path LIKE 'uploads/%' THEN ad.file_path
                               ELSE CONCAT('uploads/', ad.file_path)
                           END as file_path,
                           ad.original_name, ad.file_type,
                           ar.student_id, ar.assessed_by,
                           asv.assessment_id
                    FROM assessment_documents ad
                    JOIN assessment_services asv ON ad.assessment_service_id = asv.id
                    JOIN assessment_records ar ON asv.assessment_id = ar.id
                    WHERE ad.id = :id
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();

            case 'pdsp_document':
                // PDSP signed document (uploaded during Process 4).
                // Paths in DB may already start with "uploads/"; avoid "uploads/uploads/".
                $stmt = $this->db->prepare("
                    SELECT pr.id,
                           CASE
                               WHEN pr.signed_document_path IS NULL OR pr.signed_document_path = '' THEN ''
                               WHEN pr.signed_document_path LIKE 'uploads/%' THEN pr.signed_document_path
                               ELSE CONCAT('uploads/', pr.signed_document_path)
                           END AS file_path,
                           pr.student_id,
                           CONCAT('PDSP_Meeting_', pr.meeting_id, '.pdf') as original_name
                    FROM pdsp_records pr
                    WHERE pr.id = :id
                ");
                $stmt->execute(['id' => $id]);
                return $stmt->fetch();

            case 'iep_document':
                $stmt = $this->db->prepare("
                    SELECT id.*, im.student_id, id.pdf_path as file_path,
                           CONCAT('IEP_P2_', im.id, '.pdf') as original_name
                    FROM iep_p2_documents id
                    JOIN iep_meetings im ON id.meeting_id = im.id
                    WHERE id.id = :id
                ");
                $stmt->execute(['id' => $id]);
                $result = $stmt->fetch();
                
                if (!$result) {
                    // Try P3 documents
                    $stmt = $this->db->prepare("
                        SELECT id.*, im.student_id, id.pdf_path as file_path,
                               CONCAT('IEP_P3_', im.id, '.pdf') as original_name
                        FROM iep_p3_documents id
                        JOIN iep_meetings im ON id.meeting_id = im.id
                        WHERE id.id = :id
                    ");
                    $stmt->execute(['id' => $id]);
                    $result = $stmt->fetch();
                }
                
                return $result;
                
            default:
                return false;
        }
    }
    
    /**
     * Check if user has permission to access file
     */
    private function checkPermission($type, $fileInfo, $userId, $userRole) {
        // Admin can access everything
        if ($userRole === 'admin') {
            return true;
        }
        
        switch ($type) {
            case 'enrollment_document':
                // Parent (owner), SPED Teacher, Admin
                if ($userRole === 'parent' && $fileInfo['parent_id'] == $userId) {
                    return true;
                }
                if ($userRole === 'sped_teacher') {
                    return true;
                }
                return false;
                
            case 'role_document':
                // Applicant (owner), Approver (Admin/Principal), Admin
                if ($fileInfo['user_id'] == $userId) {
                    return true;
                }
                if ($userRole === 'principal') {
                    return true;
                }
                return false;
                
            case 'lesson_material':
                // Teacher who created the lesson plan, assigned learner, parent of learner, admin
                if ($userRole === 'sped_teacher' && $fileInfo['teacher_id'] == $userId) {
                    return true;
                }
                if (in_array($userRole, ['learner', 'parent'])) {
                    // Verify the student is assigned to this lesson plan
                    $stmt = $this->db->prepare("
                        SELECT la.id
                        FROM lesson_assignments la
                        JOIN student_records sr ON la.student_id = sr.id
                        JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                        WHERE la.lesson_plan_id = :lp_id
                          AND (
                              es.parent_id = :user_id
                              OR EXISTS (
                                  SELECT 1 FROM users u
                                  WHERE u.id = :user_id2
                                    AND u.email = CONCAT('learner_', sr.lrn, '@spedlms.local')
                              )
                          )
                    ");
                    $stmt->execute([
                        'lp_id'    => $fileInfo['lesson_plan_id'],
                        'user_id'  => $userId,
                        'user_id2' => $userId,
                    ]);
                    return $stmt->fetch() !== false;
                }
                return false;

            case 'learning_material':
                // Teacher (uploader), Assigned learner, Admin
                if ($userRole === 'sped_teacher' && $fileInfo['teacher_id'] == $userId) {
                    return true;
                }
                if ($userRole === 'learner') {
                    // Check if learner is assigned to this IEP
                    $stmt = $this->db->prepare("
                        SELECT li.student_id, sr.enrollment_id, es.parent_id, u.id as learner_user_id
                        FROM learner_iep li
                        JOIN student_records sr ON li.student_id = sr.id
                        JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                        JOIN users u ON u.email = CONCAT(sr.lrn, '@learner.spedlms.local')
                        WHERE li.id = :learner_iep_id AND u.id = :user_id
                    ");
                    $stmt->execute([
                        'learner_iep_id' => $fileInfo['learner_iep_id'],
                        'user_id' => $userId
                    ]);
                    return $stmt->fetch() !== false;
                }
                return false;
                
            case 'assignment_submission':
                // Student (owner), Teacher, Admin
                if ($userRole === 'learner') {
                    // Check if this is the student's submission
                    $stmt = $this->db->prepare("
                        SELECT sr.id
                        FROM student_records sr
                        JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                        JOIN users u ON u.email = CONCAT(sr.lrn, '@learner.spedlms.local')
                        WHERE sr.id = :student_id AND u.id = :user_id
                    ");
                    $stmt->execute([
                        'student_id' => $fileInfo['student_id'],
                        'user_id' => $userId
                    ]);
                    return $stmt->fetch() !== false;
                }
                if ($userRole === 'sped_teacher') {
                    return true;
                }
                return false;

            case 'assessment':
                // SPED Teacher, Guidance, Principal can view assessment documents
                return in_array($userRole, ['sped_teacher', 'guidance', 'principal']);

            case 'pdsp_document':
                // SPED Teacher, Guidance, Principal, Parent (read-only view)
                return in_array($userRole, ['sped_teacher', 'guidance', 'principal', 'parent']);

            case 'iep_document':
                // Parent, SPED Teacher, Guidance, Principal, Admin
                if (in_array($userRole, ['parent', 'sped_teacher', 'guidance', 'principal'])) {
                    // Check if user is involved in this IEP
                    $stmt = $this->db->prepare("
                        SELECT im.id
                        FROM iep_meetings im
                        JOIN student_records sr ON im.student_id = sr.id
                        JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                        WHERE im.student_id = :student_id
                        AND (
                            es.parent_id = :user_id
                            OR im.guidance_id = :user_id
                            OR im.principal_id = :user_id
                            OR im.scheduled_by = :user_id
                        )
                    ");
                    $stmt->execute([
                        'student_id' => $fileInfo['student_id'],
                        'user_id' => $userId
                    ]);
                    return $stmt->fetch() !== false;
                }
                return false;
                
            default:
                return false;
        }
    }
    
    /**
     * Log file access for audit trail
     */
    private function logFileAccess($type, $id, $userId, $action) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address)
                VALUES (:user_id, :action_type, :table, :record_id, :details, :ip)
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'action_type' => 'file_access',
                'table' => $type,
                'record_id' => $id,
                'details' => json_encode(['action' => $action, 'type' => $type]),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log('Failed to log file access: ' . $e->getMessage());
        }
    }
    
    /**
     * Get MIME type from filename
     */
    private function getMimeType($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
