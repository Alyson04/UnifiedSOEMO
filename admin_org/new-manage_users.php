<?php
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org_admins

require '../config/db_conn.php';

// Get session values
$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;
$admin_name = '';

// Get admin name
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

    <!-- Status Filter -->
    <form id="statusFilterForm" class="status-filter-form">
        <label for="status_filter">Filter</label>
        <select name="status" id="status_filter">
            <option value="">All</option>
            <option value="approved">Approved</option>
            <option value="rejected">Declined</option>
            <option value="under review">Under Review</option>
        </select>
    </form>

    <!-- Users Table -->
    <div class="user-table">
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Applied At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <!-- Data will load here via AJAX -->
            </tbody>
        </table>

        <!-- Pagination -->
        <div id="paginationControls" class="pagination-controls"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('userTableBody');
    const paginationControls = document.getElementById('paginationControls');
    const statusFilter = document.getElementById('status_filter');

    function fetchUsers(page = 1) {
        const status = statusFilter.value;
        fetch(`get_users.php?page=${page}&status=${encodeURIComponent(status)}`)
            .then(res => res.json())
            .then(data => {
                renderUsers(data.users);
                renderPagination(data.total, data.perPage, page);
            })
            .catch(() => {
                tableBody.innerHTML = '<tr><td colspan="5">Error loading users.</td></tr>';
                paginationControls.innerHTML = '';
            });
    }

    function renderUsers(users) {
        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5">No users found for this organization.</td></tr>';
            return;
        }
        tableBody.innerHTML = '';
        users.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(user.fullName)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${capitalizeFirstLetter(user.application_status) || 'Pending'}</td>
                <td>${escapeHtml(user.applied_at)}</td>
                <td>
                    ${['approved', 'rejected'].includes(user.application_status) ? `
                        <button disabled>Accept</button>
                        <button disabled>Decline</button>
                    ` : `
                        <form action="../api/process_application.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="${user.id}">
                            <input type="hidden" name="org_id" value="${user.org_id}">
                            <button type="submit" name="action" value="accept">Accept</button>
                        </form>
                        <form action="../api/process_application.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="${user.id}">
                            <input type="hidden" name="org_id" value="${user.org_id}">
                            <button type="submit" name="action" value="decline">Decline</button>
                        </form>
                    `}
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function renderPagination(total, perPage, current) {
        const totalPages = Math.ceil(total / perPage);
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'pagination-btn' + (i === current ? ' active' : '');
            btn.onclick = () => fetchUsers(i);
            paginationControls.appendChild(btn);
        }
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[m];
        });
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    statusFilter.addEventListener('change', () => fetchUsers());

    fetchUsers();
});
</script>

<style>
.pagination-controls {
    margin-top: 1em;
}
.pagination-controls button {
    padding: 5px 10px;
    margin: 0 3px;
    border: none;
    background: #ddd;
    cursor: pointer;
    border-radius: 3px;
}
.pagination-controls button.active {
    background: #333;
    color: #fff;
}
</style>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
