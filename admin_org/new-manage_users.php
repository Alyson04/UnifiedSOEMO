<?php
require '../api/auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Get org admin's organization ID
$org_id = null;
if ($admin_id) {
    $sql_org = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_org = $stmt->get_result();
    if ($result_org->num_rows > 0) {
        $org_id = $result_org->fetch_assoc()['id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt->close();

    // Get admin name
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

$title = "Manage Organization Members";
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
    <h2 class="page-title">MANAGE ORGANIZATION MEMBERS</h2>

    <div class="top-bars">
        <form method="GET" class="status-filter-form" onsubmit="return false;">
            <label for="status_filter">Filter by Status</label>
        <select name="status" id="status_filter">
            <option value="">All</option>
                <option value="active">Active</option>
                <option value="renewal">Renewal</option>
                <option value="disabled">Disabled</option>
        </select>
    </form>

        <div class="top-actions">
            <a href="create_new_user.php" class="action-btn">+ Add New Member</a>
            <button class="action-btn" style="border: none;" onclick="openDeleteModal()">Remove Member</button>
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
                    <th>Application Status</th>
                    <th>Account Status</th>
                    <th>Graduated</th>
                    <th>Date Joined</th>
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
            <label for="studentNumber">Enter Student Number of Member to Remove:</label>
            <input type="text" name="studentNumber" id="studentNumber" required>
            <div class="modal-actions">
                <button type="submit" class="action-btn danger" style="border:none;">Confirm Remove</button>
                <button type="button" class="action-btn" style="border:none;" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.pagination-controls {
    margin-top: .7em;
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
.action-btn {
    padding: 10px 16px;
    background-color: #2A4365;
    color: #fff;
    text-decoration: none;
    border-radius: 999px;
    font-weight: bold;
    transition: background-color 0.3s;
}
.action-btn:hover {
    background-color: #1f2f47;
}
.action-btn.danger {
    background-color: #dc3545;
}
.action-btn.danger:hover {
    background-color: #c82333;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}
.modal-box h3 {
    margin-top: 0;
}
.modal-box input {
    width: 100%;
    padding: 8px;
    margin: 10px 0;
}
.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.hidden {
    display: none !important;
}
.edit-input {
    width: 100%;
    padding: 4px;
    box-sizing: border-box;
}
</style>

<script>
// Enable edit mode for the row
function enableEdit(button) {
    const row = button.closest('tr');
    row.querySelectorAll('.view').forEach(el => el.classList.add('hidden'));
    row.querySelectorAll('.edit').forEach(el => {
        el.classList.remove('hidden');
        // Add input validation for section
        if (el.parentElement === row.cells[6]) { // Section is in the 7th column (index 6)
            el.addEventListener('input', function() {
                if (this.value && !isNaN(this.value) && parseInt(this.value) < 1) {
                    this.value = 1;
                }
            });
        }
    });
    button.style.display = 'none';
    row.querySelector('.save-btn').classList.remove('hidden');
    row.querySelector('.cancel-btn').classList.remove('hidden');
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

// Save edited fields
function saveEdit(button, userId) {
    const row = button.closest('tr');
    
    const sectionInput = row.querySelector('input[value="' + row.querySelector('td:nth-child(7) span.view').textContent + '"]');
    if (sectionInput && !isNaN(sectionInput.value) && parseInt(sectionInput.value) < 1) {
        sectionInput.value = 1;
    }


    const fieldMappings = {
        lastName: row.cells[0],
        firstName: row.cells[1],
        middleName: row.cells[2],
        studentNumber: row.cells[3],
        course: row.cells[4],
        year: row.cells[5],
        section: row.cells[6],
        email: row.cells[7],
        applicationStatus: row.cells[8],
        status: row.cells[9],
        graduated: row.cells[10]
    };

    const updatePromises = Object.entries(fieldMappings).map(([field, cell]) => {
        const input = cell.querySelector('.edit');
        if (!input) return Promise.resolve();
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

    Promise.allSettled(updatePromises).then(results => {
        const hasError = results.some(r => r.status === 'rejected');
        if (hasError) {
            alert('Some fields failed to update. Please try again.');
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
    const statusFilter = document.getElementById('status_filter');
let currentPage = 1;

    function fetchUsers(page = 1) {
    currentPage = page;
        const status = statusFilter.value;
    const url = `get_users.php?page=${page}&status=${encodeURIComponent(status)}`;
    fetch(url)
        .then(response => response.json())
            .then(data => {
                renderUsers(data.users);
                renderPagination(data.total, data.perPage, page);
            })
        .catch(err => {
            tableBody.innerHTML = '<tr><td colspan="13">Error loading members.</td></tr>';
                paginationControls.innerHTML = '';
            console.error(err);
            });
    }

    function renderUsers(users) {
    tableBody.innerHTML = '';
    if (!users.length) {
        tableBody.innerHTML = '<tr><td colspan="13">No members found.</td></tr>';
        return;
    }

    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <span class="view">${escapeHtml(user.lastName)}</span>
                <input type="text" class="edit hidden edit-input" value="${escapeHtml(user.lastName)}">
            </td>
            <td>
                <span class="view">${escapeHtml(user.firstName)}</span>
                <input type="text" class="edit hidden edit-input" value="${escapeHtml(user.firstName)}">
            </td>
            <td>
                <span class="view">${escapeHtml(user.middleName)}</span>
                <input type="text" class="edit hidden edit-input" value="${escapeHtml(user.middleName)}">
            </td>
            <td>
                <span class="view">${escapeHtml(user.studentNumber)}</span>
                <input type="text" class="edit hidden edit-input" value="${escapeHtml(user.studentNumber)}">
            </td>
            <td>
                <span class="view">${escapeHtml(user.course)}</span>
                <select class="edit hidden" name="course">
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
                <span class="view">${escapeHtml(user.year)}</span>
                <select class="edit hidden edit-input" name="year">
                    <option value="1" ${user.year === '1' ? 'selected' : ''}>1</option>
                    <option value="2" ${user.year === '2' ? 'selected' : ''}>2</option>
                    <option value="3" ${user.year === '3' ? 'selected' : ''}>3</option>
                </select>
            </td>
            <td>
                <span class="view">${escapeHtml(user.section)}</span>
                <input type="text" class="edit hidden edit-input" value="${escapeHtml(user.section)}">
            </td>
            <td>
                <span class="view">${escapeHtml(user.email)}</span>
                <input type="email" class="edit hidden edit-input" value="${escapeHtml(user.email)}">
            </td>
            <td>
                <span class="view">${escapeHtml(user.applicationStatus ? user.applicationStatus.charAt(0).toUpperCase() + user.applicationStatus.slice(1) : 'Approved')}</span>
                <select class="edit hidden edit-input" name="applicationStatus">
                    <option value="pending" ${user.applicationStatus === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="approved" ${(!user.applicationStatus || user.applicationStatus === 'approved') ? 'selected' : ''}>Approved</option>
                    <option value="rejected" ${user.applicationStatus === 'rejected' ? 'selected' : ''}>Rejected</option>
                </select>
            </td>
            <td>
                <span class="view">${escapeHtml(user.status)}</span>
                <select class="edit hidden edit-input" name="status">
                    <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
                    <option value="renewal" ${user.status === 'renewal' ? 'selected' : ''}>Renewal</option>
                    <option value="disabled" ${user.status === 'disabled' ? 'selected' : ''}>Disabled</option>
                </select>
            </td>
            <td>
                <span class="view">${escapeHtml(user.graduated)}</span>
            </td>
            <td>${formatDate(user.joined_at || user.created_at)}</td>
            <td>
                <button class="edit-btn" onclick="enableEdit(this)">Edit</button>
                <button class="save-btn hidden" onclick="saveEdit(this, ${user.id})">Save</button>
                <button class="cancel-btn hidden" onclick="cancelEdit(this)">Cancel</button>
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

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-US', options);
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialize
statusFilter.addEventListener('change', () => fetchUsers(1));
fetchUsers(1);

// Handle session alerts
const alertBox = document.querySelector('.session-alert');
if (alertBox) {
    setTimeout(() => {
        alertBox.style.transition = 'opacity 0.5s ease';
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 4000);
}

function attachEditListeners() {
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const tr = this.closest('tr');
            tr.querySelectorAll('.edit').forEach(input => {
                if (input.classList.contains('section-input')) {
                    input.addEventListener('input', function() {
                        if (this.value && !isNaN(this.value) && parseInt(this.value) < 1) {
                            this.value = 1;
                        }
                    });
                }
            });
        });
    });
}
</script>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<script src="../assets/scripts/hamburger.js"></script>
<?php include '../includes/footer.php'; ?>
