<?php
// DO NOT ALTER WITHOUT APPROVAL — Route Definitions
// Last modified: 2026-05-01
// Part of: SPED LMS — Routing

// Simple routing function
function route($method, $pattern, $controller, $action, $permission = null) {
    global $path, $requestMethod;
    
    if ($requestMethod !== $method) {
        return false;
    }
    
    // Convert pattern to regex
    $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $pattern);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $path, $matches)) {
        array_shift($matches); // Remove full match
        
        // Check permission if required
        if ($permission !== null) {
            RoleMiddleware::check($permission);
        }
        
        // Load controller
        require_once __DIR__ . '/../app/Controllers/' . $controller . '.php';
        
        // Instantiate and call action
        $controllerInstance = new $controller();
        call_user_func_array([$controllerInstance, $action], $matches);
        exit;
    }
    
    return false;
}

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

// Home - redirect to login or dashboard
route('GET', '/', 'AuthController', 'index');

// Authentication
route('GET', '/login', 'AuthController', 'showLogin');
route('POST', '/login', 'AuthController', 'login');
route('GET', '/register', 'AuthController', 'showRegister');
route('POST', '/register', 'AuthController', 'register');
route('GET', '/logout', 'AuthController', 'logout');

// File serving (encrypted files with decryption) - Authentication required, permission checked in controller
route('GET', '/file/view/{type}/{id}', 'FileController', 'view');
route('GET', '/file/download/{type}/{id}', 'FileController', 'download');

// Email Verification
route('GET', '/auth/verify-email', 'AuthController', 'showVerifyEmail');
route('POST', '/auth/verify-email', 'AuthController', 'verifyEmail');
route('POST', '/auth/resend-otp', 'AuthController', 'resendOTP');

// Google OAuth
route('GET', '/auth/google', 'AuthController', 'googleLogin');
route('GET', '/auth/google/callback', 'AuthController', 'googleCallback');

// ============================================
// AUTHENTICATED ROUTES
// ============================================

// Dashboard
route('GET', '/dashboard', 'DashboardController', 'index');
route('POST', '/dashboard/dismiss-lrn-notification', 'DashboardController', 'dismissLrnNotification');

// Notifications (AJAX endpoints)
route('GET', '/notifications/get', 'NotificationController', 'getNotifications');
route('POST', '/notifications/{id}/read', 'NotificationController', 'markAsRead');
route('POST', '/notifications/read-all', 'NotificationController', 'markAllAsRead');
route('POST', '/notifications/{id}/delete', 'NotificationController', 'delete');

// Location API (AJAX endpoints for enrollment form)
route('GET', '/api/locations/provinces', 'LocationController', 'getProvinces');
route('GET', '/api/locations/cities/{province}', 'LocationController', 'getCities');
route('GET', '/api/locations/barangays/{province}/{city}', 'LocationController', 'getBarangays');

// Services (General Information)
route('GET', '/services', 'ServicesController', 'index');

// Role Selection
route('GET', '/role/select', 'RoleController', 'showSelection', 'role.select');
route('POST', '/role/select-parent', 'RoleController', 'selectParent', 'role.select');
route('POST', '/role/submit-staff', 'RoleController', 'submitStaffApplication', 'role.select');

// ============================================
// PARENT ROUTES
// ============================================

// Parent Enrollment Routes
route('GET', '/enrollment', 'EnrollmentController', 'index', 'enrollment.submit');
route('GET', '/enrollment/create', 'EnrollmentController', 'create', 'enrollment.submit');
route('GET', '/enrollment/returning-lookup', 'EnrollmentController', 'returningLookup', 'enrollment.submit');
route('GET', '/enrollment/search-student', 'EnrollmentController', 'searchStudent', 'enrollment.submit');
route('POST', '/enrollment/save-draft', 'EnrollmentController', 'saveDraft', 'enrollment.submit');
route('POST', '/enrollment/submit', 'EnrollmentController', 'submit', 'enrollment.submit');
route('POST', '/enrollment/discard-draft', 'EnrollmentController', 'discardDraft', 'enrollment.submit');
route('POST', '/enrollment/keepalive', 'EnrollmentController', 'keepalive', 'enrollment.submit');
route('GET', '/enrollment/status', 'EnrollmentController', 'status', 'enrollment.track');
route('GET', '/enrollment/view/{id}', 'EnrollmentController', 'view', 'enrollment.view');

// ============================================
// SPED TEACHER ROUTES
// ============================================

// Verification (Process 2)
route('GET', '/verification', 'VerificationController', 'index', 'enrollment.verify');
route('GET', '/verification/{id}', 'VerificationController', 'show', 'enrollment.verify');
route('POST', '/verification/{id}/verify', 'VerificationController', 'verify', 'enrollment.verify');

// Enrollment Review (Process 1 - SPED Teacher Side)
route('GET', '/enrollment/review', 'EnrollmentController', 'review', 'enrollment.verify');
route('GET', '/enrollment/review/{id}', 'EnrollmentController', 'reviewDetail', 'enrollment.verify');

// Simplified Enrollment Approval (Single Action - NEW)
route('POST', '/enrollment/approve/{id}', 'EnrollmentController', 'approveEnrollment', 'enrollment.verify');
route('POST', '/enrollment/reject/{id}', 'EnrollmentController', 'rejectEnrollment', 'enrollment.verify');

// Per-Document Approval (Legacy - kept for backward compatibility)
route('POST', '/enrollment/document/approve/{id}', 'EnrollmentController', 'approveDocument', 'enrollment.verify');
route('POST', '/enrollment/document/reject/{id}', 'EnrollmentController', 'rejectDocument', 'enrollment.verify');

// Assessment (Process 3)
route('GET', '/assessment', 'AssessmentController', 'index', 'assessment.manage');
route('GET', '/assessment/conduct', 'AssessmentController', 'conduct', 'assessment.conduct');
route('GET', '/assessment/conduct/{id}', 'AssessmentController', 'conduct', 'assessment.conduct');
route('GET', '/assessment/get-student-data/{id}', 'AssessmentController', 'getStudentData', 'assessment.conduct');
route('POST', '/assessment/save-draft', 'AssessmentController', 'saveDraft', 'assessment.conduct');
route('POST', '/assessment/submit', 'AssessmentController', 'submit', 'assessment.conduct');
route('GET', '/assessment/view/{id}', 'AssessmentController', 'view', 'assessment.view');
route('GET', '/assessment/history/{id}', 'AssessmentController', 'history', 'assessment.view');
route('POST', '/assessment/{id}/approve', 'AssessmentController', 'approve', 'assessment.manage');
route('POST', '/assessment/{id}/reject', 'AssessmentController', 'reject', 'assessment.manage');

// IEP Implementation (Process 6 - Teacher Side)
route('GET',  '/iep/implementation',                                'IEPImplementationController', 'index',             'iep.implement');
route('GET',  '/iep/implementation/assign',                         'IEPImplementationController', 'showAssign',        'iep.implement');
route('POST', '/iep/implementation/assign',                         'IEPImplementationController', 'assign',            'iep.implement');
route('GET',  '/iep/implementation/materials/{id}',                 'IEPImplementationController', 'materials',         'iep.implement');
route('GET',  '/iep/implementation/create-activity',                'IEPImplementationController', 'showCreateActivity','iep.implement');
route('GET',  '/iep/implementation/create-activity/{id}',           'IEPImplementationController', 'showCreateActivity','iep.implement');
route('POST', '/iep/implementation/upload-file',                    'IEPImplementationController', 'uploadFile',        'iep.implement');
route('POST', '/iep/implementation/save-activity',                  'IEPImplementationController', 'saveActivity',      'iep.implement');
route('POST', '/iep/implementation/delete-material/{id}',           'IEPImplementationController', 'deleteMaterial',    'iep.implement');
route('GET',  '/iep/implementation/progress/{id}',                  'IEPImplementationController', 'progress',          'iep.implement');
// Process 6 — new workspace routes
route('GET',  '/iep/implementation/workspace/{id}',                 'IEPImplementationController', 'workspace',         'iep.implement');
route('POST', '/iep/implementation/lesson-plan/create',             'IEPImplementationController', 'createLessonPlan',  'iep.implement');
route('POST', '/iep/implementation/lesson-plan/upload-doc',         'IEPImplementationController', 'uploadLessonDoc',   'iep.implement');
route('POST', '/iep/implementation/lesson-plan/{id}/publish',       'IEPImplementationController', 'publishLessonPlan', 'iep.implement');
route('POST', '/iep/implementation/lesson-plan/{id}/delete',        'IEPImplementationController', 'deleteLessonPlan',  'iep.implement');
route('POST', '/iep/implementation/material/add',                   'IEPImplementationController', 'addMaterial',       'iep.implement');
route('POST', '/iep/implementation/activity/create',                'IEPImplementationController', 'addActivity',       'iep.implement');
route('POST', '/iep/implementation/activity/{id}/delete',           'IEPImplementationController', 'deleteActivity',    'iep.implement');
route('POST', '/iep/implementation/material/{id}/delete',           'IEPImplementationController', 'deleteMaterialNew', 'iep.implement');
// Template downloads
route('GET',  '/iep/implementation/template/{type}',                'IEPImplementationController', 'downloadTemplate',  'iep.implement');
route('POST', '/iep/implementation/activity/import',                'IEPImplementationController', 'importActivitiesCSV','iep.implement');
route('GET',  '/iep/implementation/progress-tracker',               'IEPImplementationController', 'progressTracker',   'iep.implement');
route('POST', '/iep/implementation/material/{id}/edit',             'IEPImplementationController', 'editMaterial',      'iep.implement');
route('POST', '/iep/implementation/activity/{id}/edit',             'IEPImplementationController', 'editActivity',      'iep.implement');
route('GET',  '/iep/implementation/submission/{id}',                'IEPImplementationController', 'viewSubmission',    'iep.implement');
route('POST', '/iep/implementation/submission/{id}/confirm-grade', 'IEPImplementationController', 'confirmSubmissionGrade', 'iep.implement');

// ============================================
// LEARNER ROUTES (Process 7)
// ============================================
route('GET',  '/learning/dashboard',              'LearningController', 'dashboard',       'learning.access');
route('GET',  '/learning/lesson/{id}',            'LearningController', 'lessonView',      'learning.access');
route('GET',  '/learning/activity/{id}',          'LearningController', 'activityPlay',    'learning.access');
route('POST', '/learning/activity/{id}/submit',   'LearningController', 'submitActivity',  'learning.access');
route('GET',  '/learning/progress',               'LearningController', 'progress',        'learning.access');

// Steps 16 & 17 — Parent child progress (Process 7)
route('GET',  '/parent/child-progress',           'LearningController', 'parentChildProgress', 'parent.dashboard');
route('GET',  '/parent/child-progress/{id}',      'LearningController', 'parentStudentProgress', 'parent.dashboard');

// ============================================
// GUIDANCE ROUTES
// ============================================

// ============================================
// IEP MEETING (Process 4)
// ============================================

// Availability Calendar
route('GET', '/iep/availability', 'IEPMeetingController', 'availability', 'iep.meeting');
route('POST', '/iep/availability/save-recurring', 'IEPMeetingController', 'saveRecurringAvailability', 'iep.meeting');
route('POST', '/iep/availability/toggle-exception', 'IEPMeetingController', 'toggleExceptionDate', 'iep.meeting');

// Meeting Scheduling
route('GET', '/iep/meetings', 'IEPMeetingController', 'index', 'iep.meeting');
route('GET', '/iep/meetings/schedule', 'IEPMeetingController', 'schedule', 'iep.meeting');
route('POST', '/iep/meetings/create', 'IEPMeetingController', 'createMeeting', 'iep.meeting');

// PDSP Form (Part II)
route('GET', '/iep/meetings/{id}/pdsp', 'IEPMeetingController', 'pdspForm', 'iep.meeting');
route('POST', '/iep/meetings/pdsp/save', 'IEPMeetingController', 'savePDSP', 'iep.meeting');
route('POST', '/iep/meetings/pdsp/submit', 'IEPMeetingController', 'submitPDSP', 'iep.meeting');
route('POST', '/iep/meetings/pdsp/ai-extract', 'IEPMeetingController', 'aiExtract', 'iep.meeting');
route('POST', '/iep/meetings/pdsp/upload-signed-document', 'IEPMeetingController', 'uploadSignedDocument', 'iep.meeting');
route('POST', '/iep/meetings/pdsp/mark-as-signed', 'IEPMeetingController', 'markAsSigned', 'iep.meeting');
route('GET', '/iep/meetings/schedule', 'IEPMeetingController', 'schedule', 'iep.meeting');
route('POST', '/iep/meetings/schedule', 'IEPMeetingController', 'createMeeting', 'iep.meeting');
route('POST', '/iep/meetings/availability', 'IEPMeetingController', 'getAvailability', 'iep.meeting');
route('GET', '/iep/meetings/{id}', 'IEPMeetingController', 'show', 'iep.meeting');
route('POST', '/iep/meetings/{id}/update', 'IEPMeetingController', 'updateMeeting', 'iep.meeting');
route('POST', '/iep/meetings/{id}/cancel', 'IEPMeetingController', 'cancelMeeting', 'iep.meeting');
route('POST', '/iep/meetings/upload-calendar', 'IEPMeetingController', 'uploadCalendar', 'iep.meeting');

// ============================================
// PROCESS 5 — IEP Generation
// ============================================

// IEP Repository
route('GET',  '/iep',                          'IEPController', 'index',          'iep.view');
// Create new IEP draft
route('GET',  '/iep/create',                   'IEPController', 'create',         'iep.create');
// IEP Form (view/edit)
route('GET',  '/iep/form/{id}',                'IEPController', 'form',           'iep.view');
// Save IEP Sections 1–4 (header, domains, core, re-eval date)
route('POST', '/iep/save-part1',               'IEPController', 'savePartOne',    'iep.create');
route('POST', '/iep/save-steps',               'IEPController', 'saveSteps',      'iep.create');
route('POST', '/iep/ajax/lesson-plan-for-step', 'IEPController', 'createLessonPlanForStep', 'iep.create');
route('GET',  '/iep/ajax/lesson-plans-for-step', 'IEPController', 'lessonPlansForIepStepJson', 'iep.create');
route('POST', '/iep/ajax/link-lesson-plan-to-step', 'IEPController', 'linkExistingLessonPlanToStep', 'iep.create');
route('POST', '/iep/ajax/unlink-lesson-plan',   'IEPController', 'unlinkLessonPlanFromStep', 'iep.create');
route('GET',  '/iep/ajax/step-progress/{id}',   'IEPController', 'stepProgress',   'iep.view');
// Submit IEP (form POST — upload + signatories + re-eval date)
route('POST', '/iep/submitIEP',                'IEPController', 'submitIEP',      'iep.create');
route('POST', '/iep/finalize-digital',         'IEPController', 'finalizeDigitalIep', 'iep.create');
route('POST', '/iep/draft/{id}/delete',        'IEPController', 'deleteDraft',    'iep.create');
route('GET',  '/iep/print/{id}',               'IEPController', 'printForm',      'iep.view');
route('GET',  '/iep/ajax/eligible-students',   'IEPController', 'eligibleStudentsJson', 'iep.create');
route('POST', '/iep/ajax/lesson-plan-upload',  'IEPController', 'uploadLessonPlanDocForIep', 'iep.create');
route('GET',  '/iep/sign/{id}/{sid}',          'IEPController', 'signPage',       'iep.sign');
route('POST', '/iep/save-signature',          'IEPController', 'saveSignature',  'iep.sign');
// Upload IEP document (AJAX)
route('POST', '/iep/upload-signed-doc',        'IEPController', 'upload',         'iep.create');
// Download IEP document
route('GET',  '/iep/download/{id}',            'IEPController', 'downloadDocument', 'iep.view');
// New cycle

// IEP Documents — Unified dashboard (replaces p2/review, p3/sign, approval)
route('GET', '/iep/documents', 'IEPDocumentController', 'documents', 'iep.view');

// IEP P2 Documents (Process 4)
route('GET', '/iep/p2/review', 'IEPDocumentController', 'listP2ForReview', 'iep.view');
route('GET', '/iep/p2/create/{id}', 'IEPDocumentController', 'createP2', 'iep.create');
route('POST', '/iep/p2/submit', 'IEPDocumentController', 'submitP2', 'iep.create');
route('POST', '/iep/p2/upload', 'IEPDocumentController', 'uploadP2', 'iep.create');
route('POST', '/iep/p2/send-review', 'IEPDocumentController', 'sendP2ForReview', 'iep.create');
route('GET', '/iep/p2/{id}/review', 'IEPDocumentController', 'reviewP2', 'iep.view');
route('POST', '/iep/p2/review-submit', 'IEPDocumentController', 'submitP2Review', 'iep.sign');

// IEP P3 Documents (Process 5)
route('GET', '/iep/p3/sign', 'IEPDocumentController', 'listP3ForSignature', 'iep.sign');
route('GET', '/iep/p3/create/{id}', 'IEPDocumentController', 'createP3', 'iep.create');
route('POST', '/iep/p3/submit', 'IEPDocumentController', 'submitP3', 'iep.create');
route('POST', '/iep/p3/upload', 'IEPDocumentController', 'uploadP3', 'iep.create');
route('POST', '/iep/p3/send-signature', 'IEPDocumentController', 'sendP3ForSignature', 'iep.create');
route('GET', '/iep/p3/{id}/sign', 'IEPDocumentController', 'signP3', 'iep.sign');
route('POST', '/iep/p3/sign-submit', 'IEPDocumentController', 'submitP3Signature', 'iep.sign');

// ============================================
// PRINCIPAL ROUTES
// ============================================

// IEP Approval (Process 5)
route('GET', '/iep/approval', 'IEPDocumentController', 'approvalQueue', 'iep.approve');
route('POST', '/iep/documents/{id}/approve', 'IEPDocumentController', 'approve', 'iep.approve');

// Staff Role Requests (Principal approves staff)
route('GET', '/principal/staff-requests', 'PrincipalController', 'staffRequests', 'staff.approve');
route('POST', '/principal/staff-requests/{id}/approve', 'PrincipalController', 'approveStaff', 'staff.approve');
route('POST', '/principal/staff-requests/{id}/reject', 'PrincipalController', 'rejectStaff', 'staff.approve');

// ============================================
// ADMIN ROUTES
// ============================================

route('GET', '/admin', 'AdminController', 'index', '*');
route('GET', '/admin/users', 'AdminController', 'users', '*');
route('GET', '/admin/role-requests', 'AdminController', 'roleRequests', '*');
route('POST', '/admin/role-requests/{id}/approve', 'AdminController', 'approveRole', '*');
route('POST', '/admin/role-requests/{id}/reject', 'AdminController', 'rejectRole', '*');
route('GET', '/admin/login-logs', 'AdminController', 'loginLogs', '*');
route('GET', '/admin/activity-logs', 'AdminController', 'activityLogs', '*');

// System Settings
route('GET', '/admin/settings', 'AdminController', 'settings', '*');
route('POST', '/admin/settings/update', 'AdminController', 'updateSettings', '*');

// User Management
route('GET', '/admin/manage-users', 'AdminController', 'manageUsers', '*');
route('POST', '/admin/user/change-role', 'AdminController', 'changeUserRole', '*');
route('POST', '/admin/user/toggle-status', 'AdminController', 'toggleUserStatus', '*');
route('POST', '/admin/user/delete', 'AdminController', 'deleteUser', '*');
route('GET', '/admin/user/details/{id}', 'AdminController', 'getUserDetails', '*');

// Activity Logs Export
route('GET', '/admin/activity-logs/export', 'AdminController', 'exportActivityLogs', '*');

// ============================================
// STUDENT RECORDS (All staff except parent)
// ============================================

route('GET', '/students', 'StudentController', 'index', 'student.records');
route('GET', '/students/view/{id}', 'StudentController', 'view', 'student.view');

// ============================================
// 404 NOT FOUND
// ============================================

http_response_code(404);
echo "404 - Page Not Found";
