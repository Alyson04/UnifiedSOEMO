<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

if ($admin_id) {
    $sql_admin = "SELECT firstName, middleName, lastName FROM newusers WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $row = $result_admin->fetch_assoc();
        $admin_name = ucwords(strtolower(trim("{$row['firstName']} {$row['middleName']} {$row['lastName']}")));
    }
    $stmt->close();
}

$title = "Unified SOEMO Dashboard";
$style = "new-manage_user.css";
include '../includes/header.php';
?>

<!-- Hamburger Menu -->
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

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="content">
    <h2 class="page-title">MANAGE USERS</h2>

    <div class="top-bars">
        <form method="GET" class="status-filter-form" onsubmit="return false;">
            <label for="role_filter">Filter by Role</label>
            <select name="role" id="role_filter">
                <option value="">All</option>
                <option value="admin">Admin</option>
                <option value="student">Student</option>
                <option value="orgAdmin">Org Admin</option>
            </select>
        </form>

        <div class="top-actions">
            <a href="create_new_user.php" class="action-btn">+ Create New User</a>
            <button class="action-btn" style="border: none;" onclick="openDeleteModal()">Delete User</button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="user-table">
        <table>
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Student No.</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Application Status</th> <!-- New column for Application Status -->
                    <th>Organization</th> <!-- New column for Organization Name -->
                    <th>Account Status</th>
                    <th>Graduated</th>
                    <th>Date Created</th>
                    <th>Actions</th>
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
        <form action="delete_user.php" method="POST">
            <label for="studentNumber">Enter Student Number of User to Delete:</label>
            <input type="text" name="studentNumber" id="studentNumber" required>
            <div class="modal-actions">
                <button type="submit" class="action-btn danger" style="border:none;">Confirm Delete</button>
                <button type="button" class="action-btn" style="border:none;" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Handle status change only for UI update (no backend request yet)
function handleStatusChange(selectElement) {
    const status = selectElement.value;
    const row = selectElement.closest('tr');
    const applicationStatusCell = row.querySelector('td:nth-child(10)'); // The cell where application status is located
    applicationStatusCell.querySelector('span').textContent = status.charAt(0).toUpperCase() + status.slice(1); // Update UI
}

// Enable edit mode for the row
function enableEdit(button) {
    const row = button.closest('tr');
    row.querySelectorAll('.view').forEach(el => el.classList.add('hidden'));
    row.querySelectorAll('.edit').forEach(el => el.classList.remove('hidden'));
    button.style.display = 'none';
    row.querySelector('.save-btn').classList.remove('hidden');
    row.querySelector('.cancel-btn').classList.remove('hidden');
    
    // Reset dropdown value to match current course
    const courseCell = row.querySelector('td:nth-child(5)'); // Assuming 'course' is in the 5th column
    const course = courseCell.querySelector('.view').textContent.trim(); // Get the course value from the 'view' span
    const courseSelect = row.querySelector('select[name="course"]'); // Get the dropdown

    // Ensure that the dropdown shows the correct value based on the current course
    const option = Array.from(courseSelect.options).find(opt => opt.value === course);
    if (option) {
        option.selected = true; // Set the correct option as selected
    }
}

// Cancel edit mode
function cancelEdit(button) {
    const row = button.closest('tr');
    row.querySelectorAll('.view').forEach(el => el.classList.remove('hidden'));
    row.querySelectorAll('.edit').forEach(el => el.classList.add('hidden'));
    row.querySelector('.edit-btn').style.display = 'inline-block';
    row.querySelector('.save-btn').classList.add('hidden');
    row.querySelector('.cancel-btn').classList.add('hidden');
}

// Save edited fields including application status
function saveEdit(button, userId) {
    const row = button.closest('tr');
    
    const fieldMappings = {
        lastName: row.cells[0],
        firstName: row.cells[1],
        middleName: row.cells[2],
        studentNumber: row.cells[3],
        course: row.cells[4],
        year: row.cells[5],
        section: row.cells[6],
        email: row.cells[7],
        role: row.cells[8],
        applicationStatus: row.cells[9],
        status: row.cells[11],
        graduated: row.cells[12]
    };

    const updatePromises = Object.entries(fieldMappings).map(([field, cell]) => {
        const input = cell.querySelector('.edit');
        if (!input) return Promise.resolve(); // skip if not editable
        const value = input.value;

        return fetch('update_user_field.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(userId)}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(`Failed to update ${field}: ${data.message}`);
            const span = cell.querySelector('span.view');
            if (span) span.textContent = value;
        });
    });

    // Wait for all updates to complete
    Promise.allSettled(updatePromises).then(results => {
        const hasError = results.some(r => r.status === 'rejected');
        if (hasError) {
            alert('Some fields failed to update. Check console for details.');
            console.error(results);
        }

        row.querySelectorAll('.edit').forEach(el => el.classList.add('hidden'));
        row.querySelectorAll('.view').forEach(el => el.classList.remove('hidden'));
        row.querySelector('.edit-btn').style.display = 'inline-block';
        row.querySelector('.save-btn').classList.add('hidden');
        row.querySelector('.cancel-btn').classList.add('hidden');
    });
}

function openDeleteModal() {
    document.getElementById('deleteUserModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteUserModal').style.display = 'none';
}

// Fetch users and display them
const tableBody = document.getElementById('userTableBody');
const paginationControls = document.getElementById('paginationControls');
const roleFilter = document.getElementById('role_filter');
let currentPage = 1;

function fetchUsers(page = 1) {
    currentPage = page;
    const role = roleFilter.value;
    const url = `get_users.php?page=${page}&role=${encodeURIComponent(role)}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            renderUsers(data.users);
            renderPagination(data.total, data.perPage, page);
        })
        .catch(err => {
            tableBody.innerHTML = '<tr><td colspan="14">Error loading users.</td></tr>';
            paginationControls.innerHTML = '';
            console.error(err);
        });
}

// Render user rows
function renderUsers(users) {
    tableBody.innerHTML = '';
    if (!users.length) {
        tableBody.innerHTML = '<tr><td colspan="14">No users found.</td></tr>';
        return;
    }

    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="view">${safeValue(user.lastName)}</span><input class="edit hidden" value="${user.lastName}"></td>
            <td><span class="view">${safeValue(user.firstName)}</span><input class="edit hidden" value="${user.firstName}"></td>
            <td><span class="view">${safeValue(user.middleName)}</span><input class="edit hidden" value="${user.middleName}"></td>
            <td><span class="view">${safeValue(user.studentNumber)}</span><input class="edit hidden" value="${user.studentNumber}" ${user.role === 'admin' ? 'disabled' : ''}></td>
            <td>
            <span class="view">${safeValue(user.course)}</span>
            <select class="edit hidden" name="course" ${user.role === 'admin' ? 'disabled' : ''}>
                <option value="">-- Select Course --</option>
                <option value="DCvET" ${user.course === 'DCvET' ? 'selected' : ''}>Diploma in Civil Engineering Technology</option>
                <option value="DCET" ${user.course === 'DCET' ? 'selected' : ''}>Diploma in Computer Engineering Technology</option>
                <option value="DEET" ${user.course === 'DEET' ? 'selected' : ''}>Diploma in Electrical Engineering Technology</option>
                <option value="DECET" ${user.course === 'DECET' ? 'selected' : ''}>Diploma in Electronics Engineering Technology</option>
                <option value="DIT" ${user.course === 'DIT' ? 'selected' : ''}>Diploma in Information Technology</option>
                <option value="DMET" ${user.course === 'DMET' ? 'selected' : ''}>Diploma in Mechanical Engineering Technology</option>
                <option value="DOMT" ${user.course === 'DOMT' ? 'selected' : ''}>Diploma in Office Management Technology</option>
                <option value="DRET" ${user.course === 'DRET' ? 'selected' : ''}>Diploma in Railway Engineering Technology</option>
            </select>
            </td>
            <td>
            <span class="view">${safeValue(user.year)}</span>
            <select class="edit hidden" ${user.role === 'admin' ? 'disabled' : ''}>
                <option value="">-- Select Year --</option>
                <option value="1" ${user.year === '1' ? 'selected' : ''}>1</option>
                <option value="2" ${user.year === '2' ? 'selected' : ''}>2</option>
                <option value="3" ${user.year === '3' ? 'selected' : ''}>3</option>
            </select>
            </td>
            <td><span class="view">${safeValue(user.section)}</span><input class="edit hidden" value="${user.section}" ${user.role === 'admin' ? 'disabled' : ''}></td>
            <td><span class="view">${safeValue(user.email)}</span><input class="edit hidden" value="${user.email}"></td>
            <td><span class="view">${safeValue(user.role)}</span>
                <select class="edit hidden" ${user.role === 'admin' ? 'disabled' : ''}>
                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                    <option value="student" ${user.role === 'student' ? 'selected' : ''}>Student</option>
                    <option value="orgAdmin" ${user.role === 'orgAdmin' ? 'selected' : ''}>Org Admin</option>
                </select>
            </td>
            <td><span class="view">${safeValue(user.applicationStatus)}</span>
                <select class="edit hidden" ${user.role === 'admin' ? 'disabled' : ''} onchange="handleStatusChange(this)">
                    <option value="pending" ${user.applicationStatus === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="approved" ${user.applicationStatus === 'approved' ? 'selected' : ''}>Approved</option>
                    <option value="rejected" ${user.applicationStatus === 'rejected' ? 'selected' : ''}>Rejected</option>
                </select>
            </td>
            <td><span class="view">${safeValue(user.orgName)}</span><input class="edit hidden" value="${user.orgName}" disabled></td> <!-- New column -->
            <td><span class="view">${safeValue(user.status)}</span>
                <select class="edit hidden" ${user.role === 'admin' ? 'disabled' : ''}>
                    <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
                    <option value="disabled" ${user.status === 'disabled' ? 'selected' : ''}>Disabled</option>
                    <option value="renewal" ${user.status === 'renewal' ? 'selected' : ''}>Renewal</option>
                </select>
            </td>
            <td><span class="view">${safeValue(user.graduated)}</span>
                <select class="edit hidden" ${user.role === 'admin' ? 'disabled' : ''}>
                    <option value="yes" ${user.graduated === 'yes' ? 'selected' : ''}>Yes</option>
                    <option value="no" ${user.graduated === 'no' ? 'selected' : ''}>No</option>
                    <option value="N/A" ${user.graduated === 'N/A' ? 'selected' : ''}>N/A</option>
                </select>
            </td>
            <td><span class="view">${safeValue(user.created_at).substring(0, 10)}</span><input class="edit hidden" value="${user.created_at}" disabled></td>
            <td>
                <button class="edit-btn" onclick="enableEdit(this)">Edit</button>
                <button class="edit hidden save-btn" onclick="saveEdit(this, ${user.id})">Save</button>
                <button class="edit hidden cancel-btn" onclick="cancelEdit(this)">Cancel</button>
            </td>
        `;

        tableBody.appendChild(tr);
    });
}

// Pagination and other necessary functions
function renderPagination(total, perPage, currentPage) {
    const totalPages = Math.ceil(total / perPage);
    paginationControls.innerHTML = '';
    if (totalPages <= 1) return;
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
        btn.onclick = () => fetchUsers(i);
        paginationControls.appendChild(btn);
    }
}

function safeValue(value) {
    return value === null || value === undefined || value === '' ? 'N/A' : escapeHtml(String(value));
}

function escapeHtml(text) {
    return text.replace(/[&<>"']|"/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
}

roleFilter.addEventListener('change', () => fetchUsers(1));
document.addEventListener('DOMContentLoaded', () => fetchUsers());
</script>

<style>
.pagination-controls { margin-top: 1em; }
.pagination-controls button { padding: 5px 10px; margin: 0 3px; border: none; background: #ddd; cursor: pointer; border-radius: 3px; }
.pagination-controls button.active { background: #333; color: #fff; }
.session-alert { position: fixed; top: 20px; left: 55%; transform: translateX(-50%); background-color: #4CAF50; color: white; padding: 14px 24px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 2000; font-weight: 500; max-width: 80%; text-align: center; animation: fadeInSlideDown 0.4s ease-in-out; }
.session-alert.error { background-color: #f44336; }
@keyframes fadeInSlideDown { from { opacity: 0; transform: translate(-50%, -20px); } to { opacity: 1; transform: translate(-50%, 0); } }
.edit.hidden, .save-btn.hidden, .cancel-btn.hidden, .view.hidden { display: none; }
</style>

<script src="../assets/scripts/profile_dropdown.js"></script>
<script src="../assets/scripts/sidebar.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
