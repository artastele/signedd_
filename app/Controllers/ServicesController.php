<?php
// DO NOT ALTER WITHOUT APPROVAL — Services Information
// Last modified: 2026-05-01
// Part of: SPED LMS — General Information

class ServicesController {
    
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }

        $userName = $_SESSION['first_name'] ?? $_SESSION['user_name'] ?? 'User';
        
        require_once __DIR__ . '/../Views/services/index.php';
    }
}
