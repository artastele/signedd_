<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 3
// Last modified: 2026-05-01
// Part of: SPED LMS — Notification Controller

require_once __DIR__ . '/../Models/NotificationModel.php';

class NotificationController {
    private $notificationModel;
    private $basePath;

    public function __construct() {
        $this->notificationModel = new NotificationModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * Get notifications (AJAX endpoint)
     */
    public function getNotifications() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getUnreadByUserId($userId, 20);
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
        exit;
    }

    /**
     * Mark notification as read (AJAX endpoint)
     */
    public function markAsRead($notificationId) {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $success = $this->notificationModel->markAsRead($notificationId, $userId);

        echo json_encode([
            'success' => $success,
            'unreadCount' => $this->notificationModel->getUnreadCount($userId)
        ]);
        exit;
    }

    /**
     * Mark all notifications as read (AJAX endpoint)
     */
    public function markAllAsRead() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $success = $this->notificationModel->markAllAsRead($userId);

        echo json_encode([
            'success' => $success,
            'unreadCount' => 0
        ]);
        exit;
    }

    /**
     * Delete notification (AJAX endpoint)
     */
    public function delete($notificationId) {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $success = $this->notificationModel->delete($notificationId, $userId);

        echo json_encode([
            'success' => $success,
            'unreadCount' => $this->notificationModel->getUnreadCount($userId)
        ]);
        exit;
    }
}
