<?php
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

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
$style = "new-manage_organizations.css";
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

<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="content">
    <h2 class="page-title">MANAGE ORGANIZATIONS</h2>

    <div class="filter-status" style="margin-bottom: 1rem;">
        <label for="statusFilter">Filter by Status:</label>
        <select id="statusFilter">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="renewal">Renewal</option>
            <option value="expired">Expired</option>
            <option value="revalidation">Revalidation</option>
        </select>
    </div>

    <div class="top-actions" style="margin-top: 30px; margin-bottom: 50px;">
        <a href="create_organization.php" class="action-btn">+ Create Organization</a>
    </div>

    <div class="event-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Mission</th>
                    <th>Vision</th>
                    <th>Status</th>
                    <th>Last<br>Updated</th>
                    <th>Renewal<br>Date</th>
                    <th>Expiry<br>Date</th>
                    <th>Org<br>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="orgTableBody"></tbody>
        </table>
    </div>

    <div id="paginationControls" class="pagination-controls"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const tableBody = document.getElementById('orgTableBody');
    const paginationControls = document.getElementById('paginationControls');
    const statusFilter = document.getElementById('statusFilter');

    let orgAdmins = [];

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/[&<>"']/g, (m) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[m]);
    }

    async function fetchOrgAdmins() {
        try {
            const res = await fetch('get_org_admins.php');
            const data = await res.json();
            orgAdmins = data;
            console.log("Fetched orgAdmins: ", orgAdmins);  // Log orgAdmins
        } catch (err) {
            console.error('Failed to fetch orgAdmins:', err);
        }
    }

    function populateAdminSelect(selectEl, selectedId = null) {
        selectEl.innerHTML = '<option value="">Select Admin</option>';
        orgAdmins.forEach(admin => {
            const option = document.createElement('option');
            option.value = admin.id;
            option.textContent = admin.fullName;
            if (selectedId && selectedId == admin.id) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
    }

    async function fetchOrganizations(page = 1) {
        const status = statusFilter.value;
        const res = await fetch(`get_organizations.php?page=${page}&status=${status}`);
        const data = await res.json();

        console.log("Fetched organizations:", data.organizations);  // Log the organization data

        renderOrganizations(data.organizations);
        renderPagination(data.total, data.perPage, page);
    }

    function renderOrganizations(orgs) {
        tableBody.innerHTML = '';

        if (orgs.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="10">No organizations found.</td></tr>';
            return;
        }

        orgs.forEach(org => {
        // Use admin_name from backend for display, but show 'Not Assigned' if empty or whitespace
        const adminName = org.admin_name && org.admin_name.trim() ? escapeHtml(org.admin_name) : 'Not Assigned';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="view">${escapeHtml(org.name)}</span><input class="edit hidden" value="${escapeHtml(org.name)}"></td>
                <td><span class="view">${escapeHtml(org.description)}</span><input class="edit hidden" value="${escapeHtml(org.description)}"></td>
                <td><span class="view">${escapeHtml(org.mission)}</span><input class="edit hidden" value="${escapeHtml(org.mission)}"></td>
                <td><span class="view">${escapeHtml(org.vision)}</span><input class="edit hidden" value="${escapeHtml(org.vision)}"></td>
                <td>
                    <span class="view">${escapeHtml(org.status)}</span>
                    <select class="edit hidden">
                        <option value="active" ${org.status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="renewal" ${org.status === 'renewal' ? 'selected' : ''}>Renewal</option>
                        <option value="expired" ${org.status === 'expired' ? 'selected' : ''}>Expired</option>
                        <option value="revalidation" ${org.status === 'revalidation' ? 'selected' : ''}>Revalidation</option>
                    </select>
                </td>
                <td>${org.last_updated ? new Date(org.last_updated).toLocaleDateString() : 'N/A'}</td>
                <td>${org.renewal_date ? new Date(org.renewal_date).toLocaleDateString() : 'N/A'}</td>
                <td>${org.expiry_date ? new Date(org.expiry_date).toLocaleDateString() : 'N/A'}</td>
                <td>
                    <span class="view">${adminName}</span>
                    <select class="edit hidden admin-select"></select>
                </td>
                <td>
                    <button class="edit-btn" onclick="enableEdit(this)">Edit</button>
                    <button class="save-btn hidden" onclick="saveEdit(this, ${org.id})">Save</button>
                    <button class="cancel-btn hidden" onclick="cancelEdit(this)">Cancel</button>
                </td>
            `;
            tableBody.appendChild(tr);
            const select = tr.querySelector('select.admin-select');
            populateAdminSelect(select, org.user_id);
        });

        const minRows = 5;
        const emptyRows = minRows - orgs.length;
        for (let i = 0; i < emptyRows; i++) {
            const emptyTr = document.createElement('tr');
            emptyTr.innerHTML = `<td colspan="10" style="height: 50px; visibility: hidden;">&nbsp;</td>`;
            tableBody.appendChild(emptyTr);
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
            btn.onclick = () => fetchOrganizations(i);
            paginationControls.appendChild(btn);
        }
    }

    window.enableEdit = function(button) {
        const row = button.closest('tr');
        row.querySelectorAll('.view').forEach(el => el.classList.add('hidden'));
        row.querySelectorAll('.edit').forEach(el => el.classList.remove('hidden'));
        button.style.display = 'none';
        row.querySelector('.save-btn').classList.remove('hidden');
        row.querySelector('.cancel-btn').classList.remove('hidden');
    };

    window.cancelEdit = function(button) {
        const row = button.closest('tr');
        row.querySelectorAll('.view').forEach(el => el.classList.remove('hidden'));
        row.querySelectorAll('.edit').forEach(el => el.classList.add('hidden'));
        row.querySelector('.edit-btn').style.display = 'inline-block';
        row.querySelector('.save-btn').classList.add('hidden');
        row.querySelector('.cancel-btn').classList.add('hidden');
    };

    window.saveEdit = function(button, orgId) {
        const row = button.closest('tr');
        const fieldMappings = {
            name: row.cells[0],
            description: row.cells[1],
            mission: row.cells[2],
            vision: row.cells[3],
            status: row.cells[4],
            user_id: row.cells[8] // Admin assignment
        };

        const updatePromises = Object.entries(fieldMappings).map(([field, cell]) => {
            const input = cell.querySelector('.edit');
            if (!input) return Promise.resolve();
            const value = input.value;

            return fetch('update_organization_field.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(orgId)}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(`Failed to update ${field}: ${data.message}`);
                }
                const view = cell.querySelector('.view');
                if (field === 'user_id') {
                    const admin = orgAdmins.find(a => a.id == value);
                    view.textContent = admin ? admin.fullName : 'Not Assigned';
                } else {
                    view.textContent = value;
                }
            })
            .catch(err => alert(err.message));
        });

        Promise.all(updatePromises).then(() => cancelEdit(button));
    };

    // Initialize
    await fetchOrgAdmins();
    await fetchOrganizations(1);
    statusFilter.addEventListener('change', () => fetchOrganizations(1));
});
</script>


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
 align-self: flex-start;
  padding: 12px 32px;
  background: linear-gradient(135deg, #36577d, #2A4365);
  color: #dbe2ef; /* soft blue-white */
  border-radius: 9999px;
  font-weight: 700;
  font-size: 1rem;
  text-decoration: none;
  box-shadow: 0 6px 20px rgba(42, 67, 101, 0.55);
  transition: background 0.4s ease, box-shadow 0.4s ease;
  user-select: none;
  margin-bottom: 25px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.action-btn:hover{
background: linear-gradient(135deg, #1f3554, #15273c);
  box-shadow: 0 10px 28px rgba(21, 39, 60, 0.8);
}
.action-btn:first-child {
  margin-left: 0;
}

.action-btn:hover {
  background-color: #0056b3;
}
.edit.hidden,
.save-btn.hidden,
.cancel-btn.hidden,
.view.hidden { 
    display: none; 
}
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<script src="../assets/scripts/hamburger.js"></script>
<?php include '../includes/footer.php'; ?>
