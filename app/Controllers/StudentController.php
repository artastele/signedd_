<?php
// DO NOT ALTER WITHOUT APPROVAL — Student Records Management
// Last modified: 2026-05-06
// Part of: SPED LMS — Student Records

require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/EnrollmentModel.php';

class StudentController {
    private $studentModel;
    private $enrollmentModel;
    private $basePath;

    public function __construct() {
        $this->studentModel = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * List all students
     */
    public function index() {
        // Check if user is staff (not parent or user)
        if (!isset($_SESSION['user_id']) || in_array($_SESSION['role'], ['parent', 'user'])) {
            $_SESSION['error'] = 'Access denied. Staff only.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Get all student records
        $students = $this->studentModel->getAllStudents();

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/index.php';
    }

    /**
     * View single student record with all enrollments and documents
     */
    public function view($studentId) {
        // Check if user is staff
        if (!isset($_SESSION['user_id']) || in_array($_SESSION['role'], ['parent', 'user'])) {
            $_SESSION['error'] = 'Access denied. Staff only.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Get student record
        $student = $this->studentModel->findById($studentId);
        
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        // Get all enrollments for this student (by LRN)
        $enrollments = $this->studentModel->getEnrollmentsByLRN($student['lrn']);

        // Get all documents from all enrollments
        $allDocuments = [];
        foreach ($enrollments as $enrollment) {
            $docs = $this->enrollmentModel->getDocuments($enrollment['id']);
            foreach ($docs as $doc) {
                $doc['enrollment_year'] = $enrollment['school_year'];
                $doc['enrollment_type'] = $enrollment['enrollment_type'];
                $allDocuments[] = $doc;
            }
        }

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/view.php';
    }
}
