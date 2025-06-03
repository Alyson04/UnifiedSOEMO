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
$style = "new-manage_user2.css";
include '../includes/header.php';
?>

<!-- Add shared CSS for admin_org section -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_shared.css">

<!-- Add mobile-specific styles -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_mobile.css">

<!-- Hamburger Menu Button -->
<button class="hamburger-menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>

<?php include '../includes/sidebar.php'; ?>

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

    </div>
        <!-- Pagination -->
        <div id="paginationControls" class="pagination-controls"></div>
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
    const minRows = 5; // Number of rows to maintain consistent height
    tableBody.innerHTML = '';

    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5">No users found for this organization.</td></tr>';
        // Add empty rows to preserve height
        for (let i = 1; i < minRows; i++) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="5" style="height: 50px;"></td>';
            tableBody.appendChild(emptyRow);
        }
        return;
    }

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

    // Add extra empty rows to maintain fixed height
    for (let i = users.length; i < minRows; i++) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="5" style="height: 50px; visibility: hidden;">&nbsp;</td>
        `;
        tableBody.appendChild(emptyRow);
    }
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
.status-filter-form{
    margin-top: 20px;
    margin-bottom: 20px;
}
</style>

<script src="../assets/scripts/admin_org_shared.js"></script>
<script src="../assets/scripts/admin_org_mobile.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
