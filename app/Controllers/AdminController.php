<?php
// DO NOT ALTER WITHOUT APPROVAL — Admin Features
// Last modified: 2026-05-01
// Part of: SPED LMS — Admin Controller

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/RoleRequestModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
if (file_exists(__DIR__ . '/../Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../Helpers/MailHelper.php';
}

class AdminController {
    private $userModel;
    private $roleRequestModel;
    private $notificationModel;
    private $basePath;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->roleRequestModel = new RoleRequestModel();
        $this->notificationModel = new NotificationModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Admin dashboard
     */
    public function index() {
        $userName = $_SESSION['user_name'];
        require_once __DIR__ . '/../Views/dashboard/admin.php';
    }

    /**
     * Manage users
     */
    public function users() {
        $users = $this->userModel->getAllUsers();
        require_once __DIR__ . '/../Views/admin/users.php';
    }

    /**
     * Role requests list (Admin - only Principal requests)
     */
    public function roleRequests() {
        // Admin only sees Principal role requests
        $requests = $this->roleRequestModel->getPendingByApprover('admin');
        $allRequests = $this->roleRequestModel->getAll();
        
        // Filter to show only principal requests
        $requests = array_filter($allRequests, function($req) {
            return $req['requested_role'] === 'principal';
        });
        
        require_once __DIR__ . '/../Views/admin/role_requests.php';
    }

    /**
     * Approve role request
     */
    public function approveRole($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $reviewNote = trim($_POST['review_note'] ?? '');

        // Update role request status
        $this->roleRequestModel->updateStatus(
            $requestId,
            'approved',
            $_SESSION['user_id'],
            $reviewNote
        );

        // Update user role
        $this->userModel->updateRole($request['user_id'], $request['requested_role']);

        // Create notification
        $this->notificationModel->create(
            $request['user_id'],
            'role_approved',
            'Application Approved',
            'Your application for ' . ucwords(str_replace('_', ' ', $request['requested_role'])) . ' has been approved!',
            ['role' => $request['requested_role'], 'request_id' => $requestId]
        );

        // Send approval email
        if (class_exists('MailHelper')) {
            MailHelper::sendRoleApprovalNotification(
                $request['user_email'],
                $request['user_name'],
                $request['requested_role']
            );
        }

        $_SESSION['success'] = 'Role request approved successfully!';
        header('Location: ' . $this->basePath . '/admin/role-requests');
        exit;
    }

    /**
     * Reject role request
     */
    public function rejectRole($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/admin/role-requests');
            exit;
        }

        $reviewNote = trim($_POST['review_note'] ?? 'Your application was rejected.');

        // Update role request status
        $this->roleRequestModel->updateStatus(
            $requestId,
            'rejected',
            $_SESSION['user_id'],
            $reviewNote
        );

        // Create notification
        $this->notificationModel->create(
            $request['user_id'],
            'role_rejected',
            'Application Rejected',
            'Your application for ' . ucwords(str_replace('_', ' ', $request['requested_role'])) . ' was not approved.',
            [
                'role' => $request['requested_role'], 
                'request_id' => $requestId,
                'reason' => $reviewNote
            ]
        );

        // Send rejection email
        if (class_exists('MailHelper')) {
            MailHelper::sendRoleRejectionNotification(
                $request['user_email'],
                $request['user_name'],
                $request['requested_role'],
                $reviewNote
            );
        }

        $_SESSION['success'] = 'Role request rejected.';
        header('Location: ' . $this->basePath . '/admin/role-requests');
        exit;
    }

    /**
     * View login attempt logs
     */
    public function loginLogs() {
        // Get filter parameters
        $result = $_GET['result'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 50);
        $search = trim($_GET['search'] ?? '');

        // Build query with user information
        $sql = "
            SELECT ll.*, u.name as user_name, u.role as user_role
            FROM login_log ll
            LEFT JOIN users u ON ll.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($result !== 'all') {
            $sql .= " AND ll.result = :result";
            $params['result'] = $result;
        }

        if (!empty($search)) {
            $sql .= " AND (ll.email LIKE :search OR u.name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY ll.attempted_at DESC LIMIT :limit";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll();

        // Get statistics
        $statsStmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN result = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN result = 'failure' THEN 1 ELSE 0 END) as failed
            FROM login_log
            WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stats = $statsStmt->fetch();

        require_once __DIR__ . '/../Views/admin/login_logs.php';
    }

    /**
     * View activity logs
     */
    public function activityLogs() {
        // Get filter parameters
        $userId = $_GET['user_id'] ?? null;
        $actionType = $_GET['action_type'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 50);
        $search = trim($_GET['search'] ?? '');

        // Build query
        $sql = "
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM activity_log al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($userId) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $userId;
        }

        if ($actionType !== 'all') {
            $sql .= " AND al.action_type = :action_type";
            $params['action_type'] = $actionType;
        }

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR al.details LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll();

        // Get unique action types for filter
        $actionsStmt = $db->query("SELECT DISTINCT action_type FROM activity_log ORDER BY action_type");
        $actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

        require_once __DIR__ . '/../Views/admin/activity_logs.php';
    }

    /**
     * System Settings Page
     */
    public function settings() {
        require_once __DIR__ . '/../Models/SystemSettingsModel.php';
        $settingsModel = new SystemSettingsModel();
        
        $settings = $settingsModel->getAllSettings();
        
        require_once __DIR__ . '/../Views/admin/settings.php';
    }

    /**
     * Update System Settings
     */
    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/admin/settings');
            exit;
        }

        require_once __DIR__ . '/../Models/SystemSettingsModel.php';
        $settingsModel = new SystemSettingsModel();

        // Get settings from POST
        $settingsToUpdate = [
            'session_timeout' => (int)($_POST['session_timeout'] ?? 15),
            'max_login_attempts' => (int)($_POST['max_login_attempts'] ?? 5),
            'lockout_duration' => (int)($_POST['lockout_duration'] ?? 15),
            'otp_expiration' => (int)($_POST['otp_expiration'] ?? 10),
            'logout_warning' => (int)($_POST['logout_warning'] ?? 2)
        ];

        // Validate
        if ($settingsToUpdate['session_timeout'] < 5 || $settingsToUpdate['session_timeout'] > 60) {
            $_SESSION['error'] = 'Session timeout must be between 5 and 60 minutes.';
            header('Location: ' . $this->basePath . '/admin/settings');
            exit;
        }

        if ($settingsToUpdate['max_login_attempts'] < 3 || $settingsToUpdate['max_login_attempts'] > 10) {
            $_SESSION['error'] = 'Max login attempts must be between 3 and 10.';
            header('Location: ' . $this->basePath . '/admin/settings');
            exit;
        }

        // Update settings
        $success = $settingsModel->updateMultipleSettings($settingsToUpdate);

        if ($success) {
            // Log activity
            $this->logActivity(
                $_SESSION['user_id'],
                'settings_updated',
                'system_settings',
                null,
                'Updated system settings: ' . json_encode($settingsToUpdate)
            );

            $_SESSION['success'] = 'System settings updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update settings. Please try again.';
        }

        header('Location: ' . $this->basePath . '/admin/settings');
        exit;
    }

    /**
     * User Management Page
     */
    public function manageUsers() {
        // Get filters
        $filters = [
            'role' => $_GET['role'] ?? 'all',
            'status' => $_GET['status'] ?? 'all',
            'search' => $_GET['search'] ?? ''
        ];

        $users = $this->userModel->getAllUsersWithStats($filters);
        $stats = $this->userModel->getUserStats();

        require_once __DIR__ . '/../Views/admin/manage_users.php';
    }

    /**
     * Change User Role
     */
    public function changeUserRole() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? '';

        // Validation
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        // Cannot change own role
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot change your own role']);
            exit;
        }

        // Validate role
        $validRoles = ['user', 'parent', 'sped_teacher', 'guidance', 'principal', 'master_teacher', 'learner', 'admin'];
        if (!in_array($newRole, $validRoles)) {
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            exit;
        }

        // Get user details
        $user = $this->userModel->findById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $oldRole = $user['role'];

        // Update role
        $success = $this->userModel->updateRole($userId, $newRole);

        if ($success) {
            // Log activity
            $this->logActivity(
                $_SESSION['user_id'],
                'role_changed',
                'users',
                $userId,
                "Changed user #{$userId} ({$user['name']}) role from '{$oldRole}' to '{$newRole}'"
            );

            // Create notification for user
            $this->notificationModel->create(
                $userId,
                'role_changed',
                'Role Updated',
                "Your role has been changed to " . ucwords(str_replace('_', ' ', $newRole)) . " by an administrator.",
                ['old_role' => $oldRole, 'new_role' => $newRole]
            );

            echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update role']);
        }
        exit;
    }

    /**
     * Toggle User Status (Activate/Deactivate)
     */
    public function toggleUserStatus() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        // Validation
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        // Cannot deactivate own account
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account']);
            exit;
        }

        // Get user details
        $user = $this->userModel->findById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        // Toggle status
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $success = $this->userModel->updateStatus($userId, $newStatus);

        if ($success) {
            // Log activity
            $action = $newStatus === 'active' ? 'user_activated' : 'user_deactivated';
            $this->logActivity(
                $_SESSION['user_id'],
                $action,
                'users',
                $userId,
                ucfirst($action) . " user #{$userId} ({$user['name']})"
            );

            // Create notification for user
            $message = $newStatus === 'active' 
                ? 'Your account has been activated by an administrator.' 
                : 'Your account has been deactivated by an administrator.';
            
            $this->notificationModel->create(
                $userId,
                'status_changed',
                'Account Status Changed',
                $message,
                ['new_status' => $newStatus]
            );

            echo json_encode([
                'success' => true, 
                'message' => 'User status updated successfully',
                'new_status' => $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        exit;
    }

    /**
     * Delete User (Soft Delete)
     */
    public function deleteUser() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        // Validation
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        // Cannot delete own account
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
            exit;
        }

        // Get user details
        $user = $this->userModel->findById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        // Soft delete
        $success = $this->userModel->softDelete($userId);

        if ($success) {
            // Log activity
            $this->logActivity(
                $_SESSION['user_id'],
                'user_deleted',
                'users',
                $userId,
                "Deleted user #{$userId} ({$user['name']}, {$user['email']})"
            );

            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        exit;
    }

    /**
     * Get User Details (AJAX)
     */
    public function getUserDetails($userId) {
        header('Content-Type: application/json');

        $user = $this->userModel->getUserDetails($userId);

        if ($user) {
            // Remove sensitive data
            unset($user['password_hash']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        exit;
    }

    /**
     * Export Activity Logs to CSV
     */
    public function exportActivityLogs() {
        // Get filters
        $actionType = $_GET['action_type'] ?? 'all';
        $userId = $_GET['user_id'] ?? null;
        $search = trim($_GET['search'] ?? '');

        // Build query
        $sql = "
            SELECT al.*, u.name as user_name, u.email as user_email, u.role
            FROM activity_log al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($userId) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $userId;
        }

        if ($actionType !== 'all') {
            $sql .= " AND al.action_type = :action_type";
            $params['action_type'] = $actionType;
        }

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR al.details LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT 1000";

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        $logs = $stmt->fetchAll();

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d_His') . '.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Write CSV header
        fputcsv($output, ['ID', 'User Name', 'Email', 'Role', 'Action Type', 'Details', 'IP Address', 'Date/Time']);

        // Write data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['user_name'] ?? 'Unknown',
                $log['user_email'] ?? 'N/A',
                $log['role'] ?? 'N/A',
                $log['action_type'],
                $log['details'],
                $log['ip_address'] ?? 'N/A',
                $log['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Helper: Log activity
     */
    private function logActivity($userId, $actionType, $affectedTable, $affectedRecordId, $details) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, action_type, affected_table, affected_record_id, details, ip_address)
            VALUES (:user_id, :action_type, :affected_table, :affected_record_id, :details, :ip_address)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'action_type' => $actionType,
            'affected_table' => $affectedTable,
            'affected_record_id' => $affectedRecordId,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
