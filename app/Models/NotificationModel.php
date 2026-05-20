<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 3
// Last modified: 2026-05-01
// Part of: SPED LMS — Notification System

require_once __DIR__ . '/../../config/db.php';

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new notification
     */
    public function create($userId, $type, $title, $message, $data = null) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, title, message, data)
            VALUES (:user_id, :type, :title, :message, :data)
        ");

        return $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null
        ]);
    }

    /**
     * Get all notifications for a user
     */
    public function getByUserId($userId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadByUserId($userId, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = :user_id AND is_read = FALSE
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = :user_id AND is_read = FALSE
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = TRUE
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = TRUE
            WHERE user_id = :user_id AND is_read = FALSE
        ");
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Delete notification
     */
    public function delete($notificationId, $userId) {
        $stmt = $this->db->prepare("
            DELETE FROM notifications
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    /**
     * Delete old read notifications (cleanup)
     */
    public function deleteOldRead($userId, $daysOld = 30) {
        $stmt = $this->db->prepare("
            DELETE FROM notifications
            WHERE user_id = :user_id 
            AND is_read = TRUE 
            AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'days' => $daysOld
        ]);
    }
}
