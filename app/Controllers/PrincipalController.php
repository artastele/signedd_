<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — Principal Controller

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/RoleRequestModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
if (file_exists(__DIR__ . '/../Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../Helpers/MailHelper.php';
}

class PrincipalController {
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
     * Staff role requests list (Principal approves: SPED Teacher, Guidance, Master Teacher)
     */
    public function staffRequests() {
        // Principal sees staff requests (not principal requests)
        $allRequests = $this->roleRequestModel->getAll();
        
        // Filter to show only staff requests (not principal)
        $requests = array_filter($allRequests, function($req) {
            return $req['requested_role'] !== 'principal';
        });
        
        require_once __DIR__ . '/../Views/principal/staff_requests.php';
    }

    /**
     * Approve staff role request
     */
    public function approveStaff($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        // Verify this is a staff request (not principal)
        if ($request['requested_role'] === 'principal') {
            $_SESSION['error'] = 'You cannot approve principal requests.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
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

        $_SESSION['success'] = 'Staff role request approved successfully!';
        header('Location: ' . $this->basePath . '/principal/staff-requests');
        exit;
    }

    /**
     * Reject staff role request
     */
    public function rejectStaff($requestId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        $request = $this->roleRequestModel->findById($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Role request not found.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
            exit;
        }

        // Verify this is a staff request (not principal)
        if ($request['requested_role'] === 'principal') {
            $_SESSION['error'] = 'You cannot reject principal requests.';
            header('Location: ' . $this->basePath . '/principal/staff-requests');
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

        $_SESSION['success'] = 'Staff role request rejected.';
        header('Location: ' . $this->basePath . '/principal/staff-requests');
        exit;
    }
}
