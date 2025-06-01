<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch admin's full name from database
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
$style = "new-manage_organizations.css"; // Reuse manage events style
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="content">
    <h2 class="page-title">MANAGE ORGANIZATIONS</h2>

    <!-- Organizations Table -->
    <div class="event-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody id="orgTableBody">
                <!-- Data will load here via AJAX -->
            </tbody>
        </table>
        
    </div>
<div id="paginationControls" class="pagination-controls"></div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('orgTableBody');
    const paginationControls = document.getElementById('paginationControls');

    function fetchOrganizations(page = 1) {
        fetch(`get_organizations.php?page=${page}`)
            .then(res => res.json())
            .then(data => {
                renderOrganizations(data.organizations);
                renderPagination(data.total, data.perPage, page);
            })
            .catch(() => {
                tableBody.innerHTML = '<tr><td colspan="3">Error loading organizations.</td></tr>';
                paginationControls.innerHTML = '';
            });
    }

    function renderOrganizations(orgs) {
    tableBody.innerHTML = '';

    if (orgs.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="3">No organizations found.</td></tr>';
        return;
    }

    orgs.forEach(org => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${escapeHtml(org.name)}</td>
            <td>${escapeHtml(org.description)}</td>
            <td>${new Date(org.created_at).toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'})}</td>
        `;
        tableBody.appendChild(tr);
    });

    // Pad table with empty rows if fewer than 5
    const minRows = 5;
    const emptyRows = minRows - orgs.length;
    for (let i = 0; i < emptyRows; i++) {
        const emptyTr = document.createElement('tr');
        emptyTr.innerHTML = `
            <td colspan="3" style="height: 50px; visibility: hidden;">&nbsp;</td>
        `;
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

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[m];
        });
    }

    fetchOrganizations();
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
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script><script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
