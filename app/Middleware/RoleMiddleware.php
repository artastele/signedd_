<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 2
// Last modified: 2026-05-01
// Part of: SPED LMS — RBAC Enforcement Middleware

class RoleMiddleware {
    private static $permissions;

    public static function init() {
        self::$permissions = require __DIR__ . '/../../config/permissions.php';
    }

    public static function check($requiredPermission) {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            http_response_code(401);
            header('Location: /login');
            exit;
        }

        $userRole = $_SESSION['role'];

        // Admin has full access
        if ($userRole === 'admin') {
            return true;
        }

        // Check if role has the required permission
        if (!isset(self::$permissions[$userRole])) {
            self::forbidden();
        }

        $rolePermissions = self::$permissions[$userRole];

        // Check for wildcard or specific permission
        if (in_array('*', $rolePermissions) || in_array($requiredPermission, $rolePermissions)) {
            return true;
        }

        self::forbidden();
    }

    public static function checkRole($allowedRoles) {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            http_response_code(401);
            header('Location: /login');
            exit;
        }

        $userRole = $_SESSION['role'];

        if (!in_array($userRole, $allowedRoles)) {
            self::forbidden();
        }

        return true;
    }

    private static function forbidden() {
        http_response_code(403);
        die('403 Forbidden: You do not have permission to access this resource.');
    }

    public static function hasPermission($permission) {
        if (!isset($_SESSION['role'])) {
            return false;
        }

        $userRole = $_SESSION['role'];

        if ($userRole === 'admin') {
            return true;
        }

        if (!isset(self::$permissions[$userRole])) {
            return false;
        }

        $rolePermissions = self::$permissions[$userRole];

        return in_array('*', $rolePermissions) || in_array($permission, $rolePermissions);
    }
}

// Initialize permissions on load
RoleMiddleware::init();
