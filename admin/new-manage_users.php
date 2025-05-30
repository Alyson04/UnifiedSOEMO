<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch admin's full name
if ($admin_id) {
    $sql_admin = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $admin_name = ucwords(strtolower($result_admin->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

// Handle optional role filter
$role_filter = $_GET['role'] ?? '';

// Get users (exclude admins, deleted)
$sql = "SELECT id, fullName, email, role, created_at FROM users WHERE role != 'admin' AND status = 'active'";
if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role_filter);
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "new-manage_user.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="content">
    <h2 class="page-title">MANAGE USERS</h2>

    <!-- Role Filter -->
    <form method="GET" class="status-filter-form">
        <label for="role_filter">Filter by Role</label>
        <select name="role" id="role_filter" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="student" <?= $role_filter === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="org_admin" <?= $role_filter === 'org_admin' ? 'selected' : '' ?>>Org Admin</option>
        </select>
    </form>

    <div class="top-actions">
        <a href="create_org_admin.php" class="action-btn">+ Create Org Admin</a>
        <a href="create_admin.php" class="action-btn">+ Create Admin</a>
        <button class="action-btn" onclick="openDeleteModal()">Delete User</button>
    </div>

    <!-- Users Table -->
    <div class="user-table">
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr><td colspan="4">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['fullName']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= $user['role'] === 'org_admin' ? 'Organization Admin' : ucfirst($user['role']); ?></td>
                            <td><?= htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteUserModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Delete User</h3>
        <form action="delete_user.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
            <label for="user_fullname">Enter Full Name of User to Delete:</label>
            <input type="text" name="fullName" id="user_fullname" required>
            <div class="modal-actions">
                <button type="submit" class="action-btn danger">Confirm Delete</button>
                <button type="button" class="action-btn" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteUserModal').style.display = 'block';
}
function closeDeleteModal() {
    document.getElementById('deleteUserModal').style.display = 'none';
}
</script>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
