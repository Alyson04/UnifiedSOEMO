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
    <form method="GET" class="status-filter-form" onsubmit="return false;">
        <label for="role_filter">Filter by Role</label>
        <select name="role" id="role_filter">
            <option value="">All</option>
            <option value="student">Student</option>
            <option value="org_admin">Org Admin</option>
        </select>
    </form>

    <div class="top-actions">
        <a href="create_org_admin.php" class="action-btn">+ Create Org Admin</a>
        <a href="create_admin.php" class="action-btn">+ Create Admin</a>
        <button class="action-btn" style="border: none;" onclick="openDeleteModal()">Delete User</button>
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
            <tbody id="userTableBody">
                <!-- User rows injected here by JS -->
            </tbody>
        </table>
    </div>

    <div id="paginationControls" class="pagination-controls"></div>
</div>

<!-- Delete Modal -->
<div id="deleteUserModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Delete User</h3>
        <form action="delete_user.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
            <label for="user_fullname">Enter Full Name of User to Delete:</label>
            <input type="text" name="fullName" id="user_fullname" required>
            <div class="modal-actions">
                <button type="submit" class="action-btn danger" style="border:none;">Confirm Delete</button>
                <button type="button" class="action-btn" style="border:none;" onclick="closeDeleteModal()">Cancel</button>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('userTableBody');
    const paginationControls = document.getElementById('paginationControls');
    const roleFilter = document.getElementById('role_filter');
    
    let currentPage = 1;

    function fetchUsers(page = 1) {
        const role = roleFilter.value;
        fetch(`get_users.php?page=${page}&role=${encodeURIComponent(role)}`)
            .then(response => response.json())
            .then(data => {
                renderUsers(data.users);
                renderPagination(data.total, data.perPage, page);
            })
            .catch(err => {
                tableBody.innerHTML = '<tr><td colspan="4">Error loading users.</td></tr>';
                paginationControls.innerHTML = '';
                console.error(err);
            });
    }

    function renderUsers(users) {
        tableBody.innerHTML = '';
        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4">No users found.</td></tr>';
            return;
        }

        users.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(user.fullName)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${user.role === 'org_admin' ? 'Organization Admin' : capitalize(user.role)}</td>
                <td>${escapeHtml(user.created_at)}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function renderPagination(total, perPage, currentPage) {
        const totalPages = Math.ceil(total / perPage);
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
            btn.onclick = () => {
                fetchUsers(i);
            };
            paginationControls.appendChild(btn);
        }
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Basic escaping to avoid XSS
    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[m];
        });
    }

    roleFilter.addEventListener('change', () => {
        fetchUsers(1);
    });

    fetchUsers();
});
</script>

<style>
.pagination-controls {
    margin-top: 1em;
}
.pagination-controls button {
    padding: 5px 10px;
    margin: 0 2px;
    border: none;
    background: #ddd;
    cursor: pointer;
}
.pagination-controls button.active {
    background: #333;
    color: #fff;
}
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
