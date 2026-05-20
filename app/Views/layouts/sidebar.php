<?php
$currentPath = $_SERVER['REQUEST_URI'];
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$role = $_SESSION['role'] ?? 'user';
$userName = $_SESSION['user_name'] ?? 'User';

function isActive($path) {
    global $currentPath, $basePath;
    $fullPath = $basePath . $path;
    return strpos($currentPath, $fullPath) === 0 ? 'active' : '';
}

// Check if any IEP Procedure link is active
function isIEPProcedureActive() {
    global $currentPath, $basePath;
    $iepPaths = ['/assessment/conduct', '/iep/meetings', '/iep/p2/', '/iep/p3/', '/iep/documents'];
    foreach ($iepPaths as $path) {
        if (strpos($currentPath, $basePath . $path) === 0) {
            return true;
        }
    }
    return false;
}

function isEnrollmentAvailabilityMenuActive() {
    global $currentPath, $basePath;
    foreach (['/verification', '/enrollment/review', '/iep/availability'] as $p) {
        if (strpos($currentPath, $basePath . $p) === 0) {
            return true;
        }
    }
    return false;
}
?>

<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <?php if (file_exists(__DIR__ . '/../../../public/images/logo.png')): ?>
            <img src="<?php echo $basePath; ?>/images/logo.png" alt="SPED LMS Logo">
        <?php else: ?>
            <i class="bi bi-mortarboard-fill" style="font-size: 3rem; color: #ffffff;"></i>
        <?php endif; ?>
        <h4>SPED LMS</h4>
    </div>

    <!-- Navigation Menu (Scrollable) -->
    <div class="sidebar-menu">
        <a href="<?php echo $basePath; ?>/dashboard" class="<?php echo isActive('/dashboard'); ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($role === 'user'): ?>
            <a href="<?php echo $basePath; ?>/services" class="<?php echo isActive('/services'); ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Services</span>
            </a>

        <?php elseif ($role === 'learner'): ?>
            <!-- Process 7 — Learner nav links -->
            <a href="<?php echo $basePath; ?>/learning/dashboard" class="<?php echo isActive('/learning/dashboard'); ?>">
                <i class="bi bi-book-open"></i>
                <span>My Lessons</span>
            </a>
            <a href="<?php echo $basePath; ?>/learning/progress" class="<?php echo isActive('/learning/progress'); ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>My Progress</span>
            </a>

        <?php elseif ($role === 'parent'): ?>
            <a href="<?php echo $basePath; ?>/enrollment/status" class="<?php echo isActive('/enrollment/status'); ?>">
                <i class="bi bi-list-check"></i>
                <span>My Enrollments</span>
            </a>
            <a href="<?php echo $basePath; ?>/enrollment" class="<?php echo isActive('/enrollment'); ?>">
                <i class="bi bi-plus-circle"></i>
                <span>Enroll Child</span>
            </a>
            <!-- Step 16 — Child LMS Progress -->
            <a href="<?php echo $basePath; ?>/parent/child-progress" class="<?php echo isActive('/parent/child-progress'); ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>My Child's Progress</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/meetings" class="<?php echo isActive('/iep/meetings'); ?>">
                <i class="bi bi-calendar-check"></i>
                <span>IEP Meetings</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/meetings') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>My Child's IEP</span>
            </a>
            <a href="<?php echo $basePath; ?>/services" class="<?php echo isActive('/services'); ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Services</span>
            </a>

        <?php elseif ($role === 'sped_teacher'): ?>
            <div class="sidebar-section">
                <a href="#" class="sidebar-section-toggle <?php echo isEnrollmentAvailabilityMenuActive() ? 'active' : ''; ?>" data-bs-toggle="collapse" data-bs-target="#enrollmentAvailabilityMenu" aria-expanded="<?php echo isEnrollmentAvailabilityMenuActive() ? 'true' : 'false'; ?>">
                    <i class="bi bi-people"></i>
                    <span>Enrollments &amp; availability</span>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse <?php echo isEnrollmentAvailabilityMenuActive() ? 'show' : ''; ?>" id="enrollmentAvailabilityMenu">
                    <a href="<?php echo $basePath; ?>/verification" class="sidebar-submenu-item <?php echo isActive('/verification'); ?>">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Verify Enrollments</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/enrollment/review" class="sidebar-submenu-item <?php echo isActive('/enrollment/review'); ?>">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Review Enrollments</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/iep/availability" class="sidebar-submenu-item <?php echo isActive('/iep/availability'); ?>">
                        <i class="bi bi-calendar3"></i>
                        <span>My Availability</span>
                    </a>
                </div>
            </div>

            <!-- IEP Procedure (Collapsible) -->
            <div class="sidebar-section">
                <a href="#" class="sidebar-section-toggle <?php echo isIEPProcedureActive() ? 'active' : ''; ?>" data-bs-toggle="collapse" data-bs-target="#iepProcedureMenu" aria-expanded="<?php echo isIEPProcedureActive() ? 'true' : 'false'; ?>">
                    <i class="bi bi-file-earmark-medical"></i>
                    <span>IEP Procedure</span>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse <?php echo isIEPProcedureActive() ? 'show' : ''; ?>" id="iepProcedureMenu">
                    <a href="<?php echo $basePath; ?>/assessment" class="sidebar-submenu-item <?php echo isActive('/assessment') && !isActive('/assessment/conduct'); ?>">
                        <i class="bi bi-clock-history"></i>
                        <span>Assessment History</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/assessment/conduct" class="sidebar-submenu-item <?php echo isActive('/assessment/conduct'); ?>">
                        <i class="bi bi-1-circle"></i>
                        <span>Part 1: Assessment</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/iep/meetings" class="sidebar-submenu-item <?php echo isActive('/iep/meetings'); ?>">
                        <i class="bi bi-2-circle"></i>
                        <span>Part 2: Meeting & PDSP</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/iep" class="sidebar-submenu-item <?php echo isActive('/iep') && !isActive('/iep/meetings') && !isActive('/iep/availability') ? 'active' : ''; ?>">
                        <i class="bi bi-3-circle"></i>
                        <span>Part 3: Generate IEP</span>
                    </a>
                </div>
            </div>

            <a href="<?php echo $basePath; ?>/iep/implementation" class="<?php echo isActive('/iep/implementation'); ?>">
                <i class="bi bi-book"></i>
                <span>IEP Workspace</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/implementation/progress-tracker" class="<?php echo isActive('/iep/implementation/progress-tracker'); ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>Progress Tracker</span>
            </a>
            <a href="<?php echo $basePath; ?>/activities" class="<?php echo isActive('/activities'); ?>">
                <i class="bi bi-activity"></i>
                <span>Activity Logs</span>
            </a>

        <?php elseif ($role === 'guidance'): ?>
            <a href="<?php echo $basePath; ?>/iep/availability" class="<?php echo isActive('/iep/availability'); ?>">
                <i class="bi bi-calendar3"></i>
                <span>My Availability</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/meetings" class="<?php echo isActive('/iep/meetings'); ?>">
                <i class="bi bi-calendar-event"></i>
                <span>IEP Meetings</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/meetings') && !isActive('/iep/availability') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-medical"></i>
                <span>IEP Documents</span>
            </a>

        <?php elseif ($role === 'principal'): ?>
            <a href="<?php echo $basePath; ?>/iep/availability" class="<?php echo isActive('/iep/availability'); ?>">
                <i class="bi bi-calendar3"></i>
                <span>My Availability</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/meetings" class="<?php echo isActive('/iep/meetings'); ?>">
                <i class="bi bi-calendar-event"></i>
                <span>IEP Meetings</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/meetings') && !isActive('/iep/availability') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-medical"></i>
                <span>IEP Documents</span>
            </a>
            <a href="<?php echo $basePath; ?>/principal/staff-requests" class="<?php echo isActive('/principal/staff-requests'); ?>">
                <i class="bi bi-person-check"></i>
                <span>Staff Requests</span>
            </a>

        <?php elseif ($role === 'master_teacher'): ?>
            <a href="<?php echo $basePath; ?>/observation" class="<?php echo isActive('/observation'); ?>">
                <i class="bi bi-eye"></i>
                <span>Class Observation</span>
            </a>
            <a href="<?php echo $basePath; ?>/cot" class="<?php echo isActive('/cot'); ?>">
                <i class="bi bi-clipboard-check"></i>
                <span>COT Results</span>
            </a>

        <?php elseif ($role === 'admin'): ?>
            <a href="<?php echo $basePath; ?>/admin/manage-users" class="<?php echo isActive('/admin/manage-users'); ?>">
                <i class="bi bi-people"></i>
                <span>Manage Users</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/role-requests" class="<?php echo isActive('/admin/role-requests'); ?>">
                <i class="bi bi-person-check"></i>
                <span>Role Requests</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/settings" class="<?php echo isActive('/admin/settings'); ?>">
                <i class="bi bi-gear"></i>
                <span>System Settings</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/login-logs" class="<?php echo isActive('/admin/login-logs'); ?>">
                <i class="bi bi-shield-lock"></i>
                <span>Login Logs</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/activity-logs" class="<?php echo isActive('/admin/activity-logs'); ?>">
                <i class="bi bi-activity"></i>
                <span>Activity Logs</span>
            </a>
        <?php endif; ?>

        <?php if ($role !== 'parent' && $role !== 'user' && $role !== 'learner'): ?>
            <!-- Student Records - Available for all staff roles -->
            <div class="sidebar-divider"></div>
            <a href="<?php echo $basePath; ?>/students" class="<?php echo isActive('/students'); ?>">
                <i class="bi bi-person-lines-fill"></i>
                <span>Student Records</span>
            </a>
        <?php endif; ?>

        <div class="sidebar-divider"></div>

        <a href="<?php echo $basePath; ?>/account/settings">
            <i class="bi bi-gear"></i>
            <span>Account Settings</span>
        </a>
    </div>

    <!-- User Info & Logout -->
    <div class="sidebar-user">
        <div class="sidebar-user-info">
            <div class="name"><?php echo htmlspecialchars($userName); ?></div>
            <div class="role">
                <span class="badge bg-secondary"><?php echo ucwords(str_replace('_', ' ', $role)); ?></span>
            </div>
        </div>
        <a href="<?php echo $basePath; ?>/logout" class="btn btn-logout">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<style>
/* ============================================
   SIDEBAR — Responsive overrides
   (base styles live in custom.css)
   ============================================ */

/* Collapsible section toggle */
.sidebar-section { margin: 5px 0; }

.sidebar-section-toggle {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
}
.sidebar-section-toggle:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding-left: 25px;
}
.sidebar-section-toggle.active {
    background: rgba(160,20,34,0.3);
    border-left: 4px solid #a01422;
    color: #fff;
}
.sidebar-section-toggle i:first-child { margin-right: 12px; font-size: 1.1rem; }
.sidebar-section-toggle .toggle-icon { margin-left: auto; transition: transform 0.3s; font-size: 0.9rem; }
.sidebar-section-toggle[aria-expanded="true"] .toggle-icon { transform: rotate(180deg); }

/* Submenu items */
.sidebar-submenu-item {
    display: flex;
    align-items: center;
    padding: 10px 20px 10px 45px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.9rem;
    border-left: 3px solid transparent;
}
.sidebar-submenu-item:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding-left: 50px;
    border-left-color: rgba(255,255,255,0.3);
}
.sidebar-submenu-item.active {
    background: rgba(160,20,34,0.4);
    border-left-color: #a01422;
    color: #fff;
}
.sidebar-submenu-item i { margin-right: 10px; font-size: 1rem; }

/* Scrollable menu */
.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
}
.sidebar-menu::-webkit-scrollbar { width: 6px; }
.sidebar-menu::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); }
.sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }
.sidebar-menu::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.5); }
</style>
