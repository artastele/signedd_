// SPED LMS — Custom JavaScript
// DO NOT ALTER WITHOUT APPROVAL
// Last modified: 2026-05-01

// Session timeout warning (13 minutes = 780 seconds)
let sessionWarningShown = false;
const SESSION_TIMEOUT = 900000; // 15 minutes in milliseconds
const WARNING_TIME = 780000; // 13 minutes in milliseconds

function checkSessionTimeout() {
    // Show warning at 13 minutes
    setTimeout(() => {
        if (!sessionWarningShown) {
            sessionWarningShown = true;
            showSessionWarning();
        }
    }, WARNING_TIME);
}

function showSessionWarning() {
    const warningDiv = document.createElement('div');
    warningDiv.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    warningDiv.style.zIndex = '9999';
    warningDiv.innerHTML = `
        <i class="bi bi-clock-history"></i> <strong>Session Expiring Soon!</strong>
        Your session will expire in 2 minutes due to inactivity.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(warningDiv);
}

// Initialize session timeout check if user is logged in
if (document.body.dataset.loggedIn === 'true') {
    checkSessionTimeout();
}

// Password strength indicator
const passwordInput = document.getElementById('password');
if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('password-strength');
        
        if (!strengthDiv) {
            const div = document.createElement('div');
            div.id = 'password-strength';
            div.className = 'form-text mt-1';
            this.parentElement.appendChild(div);
        }
        
        const strength = calculatePasswordStrength(password);
        const strengthText = document.getElementById('password-strength');
        
        if (password.length === 0) {
            strengthText.innerHTML = '';
            return;
        }
        
        let color = 'text-danger';
        let text = 'Weak';
        
        if (strength >= 4) {
            color = 'text-success';
            text = 'Strong';
        } else if (strength >= 3) {
            color = 'text-warning';
            text = 'Medium';
        }
        
        strengthText.innerHTML = `<span class="${color}">Password Strength: ${text}</span>`;
    });
}

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

// Confirm password match indicator
const confirmPasswordInput = document.getElementById('confirm_password');
if (confirmPasswordInput && passwordInput) {
    confirmPasswordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirmPassword = this.value;
        
        if (confirmPassword.length === 0) {
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        if (password === confirmPassword) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
}

// Auto-dismiss alerts after 10 seconds (except permanent ones)
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        // Don't auto-dismiss error or warning alerts
        if (!alert.classList.contains('alert-danger') && !alert.classList.contains('alert-warning')) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 10000); // 10 seconds for success messages
        }
    });
});


// ============================================
// NOTIFICATION SYSTEM
// ============================================

let notificationPanel = null;
let notificationBell = null;
let notificationBadge = null;
let notificationBody = null;
let markAllReadBtn = null;

// Initialize notification system
function initNotifications() {
    notificationPanel = document.getElementById('notificationPanel');
    notificationBell = document.getElementById('notificationBell');
    notificationBadge = document.getElementById('notificationBadge');
    notificationBody = document.getElementById('notificationBody');
    markAllReadBtn = document.getElementById('markAllRead');

    if (!notificationBell) return;

    // Toggle notification panel
    notificationBell.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleNotificationPanel();
    });

    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            markAllNotificationsAsRead();
        });
    }

    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationPanel && 
            notificationPanel.style.display === 'block' && 
            !notificationPanel.contains(e.target) && 
            !notificationBell.contains(e.target)) {
            notificationPanel.style.display = 'none';
        }
    });

    // Load notifications on page load
    loadNotifications();

    // Poll for new notifications every 30 seconds
    setInterval(loadNotifications, 30000);
}

// Toggle notification panel
function toggleNotificationPanel() {
    if (notificationPanel.style.display === 'none' || notificationPanel.style.display === '') {
        notificationPanel.style.display = 'block';
        loadNotifications();
    } else {
        notificationPanel.style.display = 'none';
    }
}

// Get base path from the page
function getBasePath() {
    // Try to get from a data attribute or meta tag first
    const basePathMeta = document.querySelector('meta[name="base-path"]');
    if (basePathMeta) {
        return basePathMeta.getAttribute('content');
    }
    
    // Fallback: extract from current pathname
    const pathname = window.location.pathname;
    const match = pathname.match(/^(\/[^\/]+\/public)/);
    if (match) {
        return match[1];
    }
    
    // Check if just /public
    if (pathname.includes('/public')) {
        return '/public';
    }
    
    return '';
}

// Load notifications via AJAX
function loadNotifications() {
    const basePath = getBasePath();
    
    console.log('Loading notifications from:', basePath + '/notifications/get');
    
    fetch(basePath + '/notifications/get')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Notification data:', data);
            if (data.success) {
                updateNotificationBadge(data.unreadCount);
                renderNotifications(data.notifications);
            } else {
                console.error('Notification fetch failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

// Update notification badge
function updateNotificationBadge(count) {
    if (count > 0) {
        notificationBadge.textContent = count > 99 ? '99+' : count;
        notificationBadge.style.display = 'inline-block';
        markAllReadBtn.style.display = 'inline-block';
    } else {
        notificationBadge.style.display = 'none';
        markAllReadBtn.style.display = 'none';
    }
}

// Render notifications
function renderNotifications(notifications) {
    if (!notifications || notifications.length === 0) {
        notificationBody.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-bell-slash" style="font-size: 2rem;"></i>
                <p class="mb-0 mt-2">No new notifications</p>
            </div>
        `;
        return;
    }

    let html = '';
    notifications.forEach(notification => {
        const data = notification.data ? JSON.parse(notification.data) : {};

        // Determine icon and color by notification type
        let iconClass, icon;
        switch (notification.type) {
            case 'role_approved':
            case 'enrollment_approved':
            case 'email_verified':
                iconClass = 'success'; icon = 'check-circle-fill'; break;
            case 'role_rejected':
            case 'enrollment_rejected':
            case 'document_rejected':
                iconClass = 'danger'; icon = 'x-circle-fill'; break;
            case 'enrollment_submitted':
            case 'new_enrollment':
                iconClass = 'primary'; icon = 'file-earmark-text-fill'; break;
            default:
                iconClass = 'info'; icon = 'info-circle-fill';
        }

        // Build action buttons based on type
        let actionHtml = '';
        if (notification.type === 'role_rejected') {
            actionHtml = `
                <a href="${getBasePath()}/role/select" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Reapply
                </a>
                <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>
            `;
        } else if (notification.type === 'enrollment_approved') {
            actionHtml = `
                <a href="${getBasePath()}/enrollment/status" class="btn btn-sm btn-success">
                    <i class="bi bi-eye"></i> View Status
                </a>
                <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>
            `;
        } else if (notification.type === 'iep_signature_request' && data.iep_id && data.signatory_id) {
            actionHtml = `
                <a href="${getBasePath()}/iep/sign/${data.iep_id}/${data.signatory_id}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pen"></i> Open sign page
                </a>
                <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>
            `;
        } else if (notification.type === 'iep_signed' && data.iep_id) {
            actionHtml = `
                <a href="${getBasePath()}/iep/form/${data.iep_id}" class="btn btn-sm btn-success">
                    <i class="bi bi-eye"></i> View IEP
                </a>
                <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>
            `;
        } else if (notification.type === 'process6_unlocked' && data.iep_id) {
            actionHtml = `
                <a href="${getBasePath()}/iep/implementation/workspace/${data.iep_id}" class="btn btn-sm btn-primary">
                    <i class="bi bi-journal-text"></i> IEP Workspace
                </a>
                <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>
            `;
        } else if (notification.type === 'enrollment_rejected') {
            actionHtml = `
                <a href="${getBasePath()}/enrollment/status" class="btn btn-sm btn-danger">
                    <i class="bi bi-eye"></i> View Reason
                </a>
                <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>
            `;
        } else {
            actionHtml = `<button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">Mark as Read</button>`;
        }
        
        html += `
            <div class="notification-item ${notification.is_read ? '' : 'unread'}" data-id="${notification.id}">
                <div class="d-flex">
                    <div class="notification-icon ${iconClass}">
                        <i class="bi bi-${icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="notification-title">${escapeHtml(notification.title)}</div>
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        ${data.reason ? `<div class="alert alert-light mb-2 p-2" style="font-size: 0.85rem;"><strong>Reason:</strong> ${escapeHtml(data.reason)}</div>` : ''}
                        <div class="notification-time">${formatNotificationTime(notification.created_at)}</div>
                        <div class="notification-actions">${actionHtml}</div>
                    </div>
                </div>
            </div>
        `;
    });

    notificationBody.innerHTML = html;

    // Add event listeners to mark as read buttons
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const notifId = this.getAttribute('data-id');
            markNotificationAsRead(notifId);
        });
    });
}

// Mark notification as read
function markNotificationAsRead(notificationId) {
    const basePath = getBasePath();
    
    fetch(basePath + '/notifications/' + notificationId + '/read', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationBadge(data.unreadCount);
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Mark all notifications as read
function markAllNotificationsAsRead() {
    const basePath = getBasePath();
    
    fetch(basePath + '/notifications/read-all', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationBadge(0);
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

// Format notification time
function formatNotificationTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds

    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    
    return date.toLocaleDateString();
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotifications);
} else {
    initNotifications();
}
