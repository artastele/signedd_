<?php
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$role = $_SESSION['role'] ?? 'user';
?>

<!-- Top Navigation Bar -->
<div class="topbar">
    <div class="topbar-left">
        <!-- Hamburger — visible on mobile only -->
        <button class="hamburger-btn" id="sidebarHamburger" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>
    
    <div class="topbar-right">
        <!-- Notification Bell -->
        <div class="topbar-notification">
            <button class="notification-bell-btn" id="notificationBell" type="button">
                <i class="bi bi-bell-fill"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            </button>
            
            <!-- Notification Dropdown Panel -->
            <div class="notification-panel" id="notificationPanel" style="display: none;">
                <div class="notification-header">
                    <h6 class="mb-0">Notifications</h6>
                    <button class="btn btn-sm btn-link text-white p-0" id="markAllRead" style="display: none;">
                        Mark all as read
                    </button>
                </div>
                <div class="notification-body" id="notificationBody">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bell-slash" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2">No new notifications</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="topbar-user dropdown">
            <button class="topbar-user-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role"><?php echo ucwords(str_replace('_', ' ', $role)); ?></div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <div class="dropdown-header">
                        <strong><?php echo htmlspecialchars($userName); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($userEmail); ?></small>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/dashboard"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/account/settings"><i class="bi bi-gear"></i> Account Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo $basePath; ?>/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>
