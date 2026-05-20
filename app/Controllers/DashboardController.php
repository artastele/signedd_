<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Dashboard Controller

class DashboardController {
    public function index() {
        // User must be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $role = $_SESSION['role'];
        $userName = $_SESSION['user_name'];
        $userEmail = $_SESSION['user_email'];
        $userId = $_SESSION['user_id'];

        // Check for pending and rejected role requests
        require_once __DIR__ . '/../Models/RoleRequestModel.php';
        $roleRequestModel = new RoleRequestModel();
        $pendingRequest = $roleRequestModel->getPendingByUserId($userId);
        $rejectedRequest = $roleRequestModel->getLatestRejectedByUserId($userId);
        $applicationHistory = $roleRequestModel->getAllByUserId($userId);

        // Route to appropriate dashboard based on role
        switch ($role) {
            case 'admin':
                require_once __DIR__ . '/../Views/dashboard/admin.php';
                break;
            case 'parent':
                // Fetch enrollment data for parent
                require_once __DIR__ . '/../Models/EnrollmentModel.php';
                $enrollmentModel = new EnrollmentModel();
                $enrollments = $enrollmentModel->getEnrollmentsWithStats($userId);
                $stats = $enrollmentModel->getParentStats($userId);
                
                require_once __DIR__ . '/../Views/dashboard/parent.php';
                break;
            case 'learner':
                // Redirect to the LMS learner dashboard (Process 7)
                header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/learning/dashboard');
                exit;
            case 'sped_teacher':
                // Fetch pending enrollments for SPED teacher
                require_once __DIR__ . '/../Models/EnrollmentModel.php';
                $enrollmentModel = new EnrollmentModel();
                $pendingEnrollments = $enrollmentModel->getPending();
                $pendingCount = count($pendingEnrollments);
                
                // Fetch learners for progress tracker widget (Process 6/7 tables may not exist yet)
                $learners = [];
                $draftsCount = 0;
                try {
                    require_once __DIR__ . '/../Models/LessonPlanModel.php';
                    $lpModel = new LessonPlanModel();
                    $learners = $lpModel->getLearnersForTeacher($userId);
                    $draftsCount = $lpModel->countDraftForTeacher($userId);
                } catch (PDOException $e) {
                    error_log('DashboardController: LessonPlanModel tables not ready — ' . $e->getMessage());
                } catch (Exception $e) {
                    error_log('DashboardController: LessonPlanModel error — ' . $e->getMessage());
                }
                
                require_once __DIR__ . '/../Views/dashboard/teacher.php';
                break;
            case 'guidance':
                require_once __DIR__ . '/../Views/dashboard/guidance.php';
                break;
            case 'principal':
                require_once __DIR__ . '/../Views/dashboard/principal.php';
                break;
            case 'master_teacher':
                require_once __DIR__ . '/../Views/dashboard/master_teacher.php';
                break;
            case 'general':
            case 'user':
            default:
                require_once __DIR__ . '/../Views/dashboard/general.php';
                break;
        }
    }

    /**
     * Dismiss LRN notification (AJAX endpoint)
     */
    public function dismissLrnNotification() {
        header('Content-Type: application/json');
        
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Get enrollment ID from request
        $input = json_decode(file_get_contents('php://input'), true);
        $enrollmentId = $input['enrollment_id'] ?? null;
        
        if (!$enrollmentId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Enrollment ID required']);
            exit;
        }
        
        // Store dismissal in session
        $_SESSION['lrn_dismissed_' . $enrollmentId] = true;
        
        echo json_encode(['success' => true, 'message' => 'Notification dismissed']);
        exit;
    }
}
