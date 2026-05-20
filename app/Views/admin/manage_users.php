<?php
$pageTitle = 'Manage Users - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-people"></i> Manage Users
    </h1>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #1e4072;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Users</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #3b6d11;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Active</h6>
                    <h3 class="mb-0 text-success"><?php echo number_format($stats['active']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #a01422;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Inactive</h6>
                    <h3 class="mb-0 text-danger"><?php echo number_format($stats['inactive']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending</h6>
                    <h3 class="mb-0 text-warning"><?php echo number_format($stats['pending']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo $basePath; ?>/admin/manage-users" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="all" <?php echo ($_GET['role'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <option value="user" <?php echo ($_GET['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="parent" <?php echo ($_GET['role'] ?? '') === 'parent' ? 'selected' : ''; ?>>Parent</option>
                        <option value="sped_teacher" <?php echo ($_GET['role'] ?? '') === 'sped_teacher' ? 'selected' : ''; ?>>SPED Teacher</option>
                        <option value="guidance" <?php echo ($_GET['role'] ?? '') === 'guidance' ? 'selected' : ''; ?>>Guidance</option>
                        <option value="principal" <?php echo ($_GET['role'] ?? '') === 'principal' ? 'selected' : ''; ?>>Principal</option>
                        <option value="master_teacher" <?php echo ($_GET['role'] ?? '') === 'master_teacher' ? 'selected' : ''; ?>>Master Teacher</option>
                        <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo ($_GET['status'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">No users found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge" style="background-color: #1e4072;">
                                            <?php echo ucwords(str_replace('_', ' ', $user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($user['status'] === 'inactive'): ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="changeRole(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>', '<?php echo $user['role']; ?>')"
                                                    title="Change Role">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="toggleStatus(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>', '<?php echo $user['status']; ?>')"
                                                    title="<?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="bi bi-<?php echo $user['status'] === 'active' ? 'lock' : 'unlock'; ?>"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" 
                                                    onclick="viewDetails(<?php echo $user['id']; ?>)"
                                                    title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="<?php echo $basePath; ?>/admin/activity-logs?user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-secondary" 
                                               title="View Activity">
                                                <i class="bi bi-list-ul"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')"
                                                    title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 text-muted">
                <small>Showing <?php echo count($users); ?> users</small>
            </div>
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title">Change User Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Change role for: <strong id="roleUserName"></strong></p>
                <input type="hidden" id="roleUserId">
                <div class="mb-3">
                    <label class="form-label">New Role</label>
                    <select id="newRole" class="form-select">
                        <option value="user">User</option>
                        <option value="parent">Parent</option>
                        <option value="sped_teacher">SPED Teacher</option>
                        <option value="guidance">Guidance</option>
                        <option value="principal">Principal</option>
                        <option value="master_teacher">Master Teacher</option>
                        <option value="learner">Learner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmChangeRole()">Change Role</button>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e4072; color: white;">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const basePath = '<?php echo $basePath; ?>';

function changeRole(userId, userName, currentRole) {
    document.getElementById('roleUserId').value = userId;
    document.getElementById('roleUserName').textContent = userName;
    document.getElementById('newRole').value = currentRole;
    new bootstrap.Modal(document.getElementById('changeRoleModal')).show();
}

function confirmChangeRole() {
    const userId = document.getElementById('roleUserId').value;
    const newRole = document.getElementById('newRole').value;
    
    fetch(basePath + '/admin/user/change-role', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `user_id=${userId}&new_role=${newRole}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function toggleStatus(userId, userName, currentStatus) {
    const action = currentStatus === 'active' ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} ${userName}?`)) return;
    
    fetch(basePath + '/admin/user/toggle-status', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function viewDetails(userId) {
    const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
    modal.show();
    
    fetch(basePath + '/admin/user/details/' + userId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            document.getElementById('userDetailsContent').innerHTML = `
                <table class="table">
                    <tr><th>ID:</th><td>${user.id}</td></tr>
                    <tr><th>Name:</th><td>${user.name}</td></tr>
                    <tr><th>Email:</th><td>${user.email}</td></tr>
                    <tr><th>Contact:</th><td>${user.contact_number || 'N/A'}</td></tr>
                    <tr><th>Role:</th><td><span class="badge" style="background-color: #1e4072;">${user.role.replace('_', ' ').toUpperCase()}</span></td></tr>
                    <tr><th>Status:</th><td><span class="badge bg-${user.status === 'active' ? 'success' : 'danger'}">${user.status.toUpperCase()}</span></td></tr>
                    <tr><th>Email Verified:</th><td>${user.email_verified ? 'Yes' : 'No'}</td></tr>
                    <tr><th>Auth Provider:</th><td>${user.auth_provider}</td></tr>
                    <tr><th>Created:</th><td>${new Date(user.created_at).toLocaleString()}</td></tr>
                    <tr><th>Updated:</th><td>${new Date(user.updated_at).toLocaleString()}</td></tr>
                </table>
            `;
        } else {
            document.getElementById('userDetailsContent').innerHTML = '<p class="text-danger">Failed to load user details</p>';
        }
    });
}

function deleteUser(userId, userName) {
    if (!confirm(`Are you sure you want to DELETE ${userName}? This action cannot be undone.`)) return;
    if (!confirm(`Final confirmation: Delete ${userName}?`)) return;
    
    fetch(basePath + '/admin/user/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
