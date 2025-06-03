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

$title = "Manage Users";
$style = "manage_users.css";
include '../includes/header.php';
?>

<!-- Add shared CSS for admin_org section -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_shared.css">

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
    <div class="filter-section">
        <label for="status_filter">Filter</label>
        <select name="status" id="status_filter" class="filter-select">
            <option value="">All</option>
            <option value="approved">Approved</option>
            <option value="rejected">Declined</option>
            <option value="under review">Under Review</option>
        </select>
    </div>

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
                <td class="status-cell ${user.application_status || 'pending'}">${capitalizeFirstLetter(user.application_status) || 'Pending'}</td>
                <td>${escapeHtml(user.applied_at)}</td>
                <td class="action-cell">
                    ${['approved', 'rejected'].includes(user.application_status) ? `
                        <div class="action-status ${user.application_status}">
                            <span class="status-icon"></span>
                            ${capitalizeFirstLetter(user.application_status)}
                        </div>
                    ` : `
                        <div class="action-buttons">
                            <button type="button" class="btn-accept" onclick="handleAction('${user.id}', '${user.org_id}', 'accept')">
                                <span class="btn-icon">✓</span>
                                Accept
                            </button>
                            <button type="button" class="btn-decline" onclick="handleAction('${user.id}', '${user.org_id}', 'decline')">
                                <span class="btn-icon">✕</span>
                                Decline
                            </button>
                        </div>
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

    // Handle form submissions
    document.addEventListener('submit', async (e) => {
        if (e.target.classList.contains('action-form')) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to perform this action?')) {
                return;
            }

            try {
                const form = e.target;
                const formData = new FormData(form);
                
                const response = await fetch('../api/process_application.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    // Show success message
                    const action = formData.get('action');
                    const message = `User has been ${action}ed successfully`;
                    showSuccessMessage(message);
                    
                    // Refresh the table
                    fetchUsers();
                } else {
                    throw new Error('Failed to process action');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to process action. Please try again.');
            }
        }
    });

    function showSuccessMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'success-message';
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.style.opacity = '0';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }

    // Add the handleAction function
    function handleAction(userId, orgId, action) {
        if (!confirm(`Are you sure you want to ${action} this user?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('org_id', orgId);
        formData.append('action', action);

        fetch('../api/process_application.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(() => {
            showSuccessMessage(`User has been ${action}ed successfully`);
            fetchUsers(); // Refresh the table
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process action. Please try again.');
        });
    }
});
</script>

<style>
/* Content Styles */
.content {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    margin: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.page-title {
    color: #1B2A47;
    font-size: 24px;
    margin-bottom: 25px;
    text-align: center;
}

/* Filter Section */
.filter-section {
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-section label {
    font-weight: 500;
    color: #1B2A47;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    color: #1B2A47;
    font-size: 14px;
    cursor: pointer;
}

/* Table Styles */
.user-table {
    overflow-x: auto;
    margin: 20px 0;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-table table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.user-table th {
    background: #1B2A47;
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 500;
}

.user-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.user-table tr:hover td {
    background-color: #f8f9fa;
}

/* Status Cell */
.status-cell {
    font-weight: 500;
    position: relative;
    padding-left: 24px !important;
}

.status-cell::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-cell.approved {
    color: #28a745;
}

.status-cell.approved::before {
    background-color: #28a745;
}

.status-cell.rejected {
    color: #dc3545;
}

.status-cell.rejected::before {
    background-color: #dc3545;
}

.status-cell.pending {
    color: #ffc107;
}

.status-cell.pending::before {
    background-color: #ffc107;
}

/* Action Cell */
.action-cell {
    padding: 8px !important;
    text-align: center;
    min-width: 200px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-accept,
.btn-decline {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    color: white;
    min-width: 90px;
    justify-content: center;
}

.btn-icon {
    font-size: 12px;
    font-weight: bold;
}

.btn-accept {
    background-color: #28a745;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}

.btn-accept:hover {
    background-color: #218838;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
}

.btn-decline {
    background-color: #dc3545;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

.btn-decline:hover {
    background-color: #c82333;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(220, 53, 69, 0.3);
}

.action-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 14px;
}

.action-status.approved {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.action-status.rejected {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.status-icon {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.approved .status-icon {
    background-color: #28a745;
}

.rejected .status-icon {
    background-color: #dc3545;
}

/* Pagination Controls */
.pagination-controls {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 5px;
}

.pagination-controls button {
    padding: 8px 12px;
    border: none;
    background: #1B2A47;
    color: white;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.3s;
}

.pagination-controls button:hover {
    background: #2c3e50;
}

.pagination-controls button.active {
    background: #3498db;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .content {
        margin: 10px;
        padding: 15px;
    }

    .user-table {
        margin: 10px 0;
    }

    .user-table th,
    .user-table td {
        padding: 10px;
    }

    .btn-accept,
    .btn-decline {
        padding: 5px 10px;
        font-size: 12px;
    }
}

/* Action Form Styles */
.action-form {
    display: inline-flex;
    gap: 8px;
}

/* Success Message Styles */
.success-message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 24px;
    background-color: #28a745;
    color: white;
    border-radius: 6px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s ease-out;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 8px;
}

.success-message::before {
    content: '✓';
    font-weight: bold;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Mobile Responsiveness */
@media screen and (max-width: 1200px) {
    .content {
        margin: 15px;
        padding: 20px;
    }

    .filter-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}

@media screen and (max-width: 768px) {
    .main-content {
        padding: 15px;
    }

    .page-title {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .user-table {
        overflow-x: auto;
        margin: 10px 0;
        padding: 10px;
    }

    .user-table table {
        min-width: 600px;
    }

    .user-table th,
    .user-table td {
        padding: 10px;
        font-size: 14px;
    }

    .filter-select {
        width: 100%;
        max-width: none;
    }

    .action-cell {
        min-width: 160px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 8px;
    }

    .btn-accept,
    .btn-decline {
        width: 100%;
        padding: 8px;
        font-size: 13px;
    }

    .pagination-controls {
        gap: 5px;
    }

    .pagination-btn {
        padding: 6px 10px;
        font-size: 13px;
        min-width: 35px;
    }
}

@media screen and (max-width: 480px) {
    .content {
        margin: 10px;
        padding: 15px;
        border-radius: 15px;
    }

    .page-title {
        font-size: 18px;
        padding: 10px;
    }

    .filter-section {
        margin: 15px 0;
    }

    .filter-section label {
        font-size: 14px;
    }

    .filter-select {
        padding: 6px 10px;
        font-size: 13px;
    }

    .user-table {
        border-radius: 10px;
        padding: 5px;
    }

    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 15px;
    }

    .pagination-btn {
        padding: 5px 8px;
        font-size: 12px;
        min-width: 30px;
    }

    .success-message {
        padding: 10px 15px;
        font-size: 13px;
        right: 10px;
    }
}

/* Hamburger Menu and Sidebar for Mobile */
@media screen and (max-width: 768px) {
    .hamburger-menu {
        display: block;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1000;
        background: none;
        border: none;
        padding: 10px;
        cursor: pointer;
    }

    .hamburger-menu span {
        display: block;
        width: 25px;
        height: 3px;
        background-color: #1B2A47;
        margin: 5px 0;
        transition: all 0.3s ease;
    }

    .hamburger-menu.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .hamburger-menu.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger-menu.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }

    .sidebar {
        position: fixed;
        left: -250px;
        top: 0;
        height: 100vh;
        transition: transform 0.3s ease;
        z-index: 999;
    }

    .sidebar.active {
        transform: translateX(250px);
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
    }

    .sidebar-overlay.active {
        display: block;
    }

    .main-content {
        margin-left: 0;
        padding-top: 60px;
    }
}
</style>

<script>
// Add mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const mainContent = document.querySelector('.main-content');

    if (hamburger && sidebar && overlay) {
        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            hamburger.classList.remove('active');
            sidebar.classList.remove('active');
            this.classList.remove('active');
        });

        // Close sidebar when clicking outside
        mainContent.addEventListener('click', function() {
            if (sidebar.classList.contains('active')) {
                hamburger.classList.remove('active');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    }
});
</script>

<!-- Add shared JavaScript for admin_org section -->
<script src="../assets/scripts/admin_org_shared.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
