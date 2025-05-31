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
$style = "new-manage_events.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<!-- Main Content -->
<div class="content">
    <h2 class="page-title">MANAGE EVENTS</h2>

    <!-- Events Table -->
    <div class="event-table">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Event Date</th>
                    <th>Organization</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody id="eventsTableBody">
                <!-- Events data will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div id="paginationControls" class="pagination-controls"></div>
</div>

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
    transition: background 0.3s;
}
.pagination-controls button:hover {
    background: #bbb;
}
.pagination-controls button.active {
    background: #333;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('eventsTableBody');
    const paginationControls = document.getElementById('paginationControls');

    function fetchEvents(page = 1) {
        fetch(`get_events.php?page=${page}`)
            .then(res => res.json())
            .then(data => {
                renderEvents(data.events);
                renderPagination(data.total, data.perPage, page);
            })
            .catch(() => {
                tableBody.innerHTML = '<tr><td colspan="4">Error loading events.</td></tr>';
                paginationControls.innerHTML = '';
            });
    }

    function renderEvents(events) {
        if (events.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4">No events found.</td></tr>';
            return;
        }
        tableBody.innerHTML = '';
        events.forEach(event => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(event.title)}</td>
                <td>${new Date(event.event_date).toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'})}</td>
                <td>${escapeHtml(event.org_name || 'N/A')}</td>
                <td>${new Date(event.created_at).toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'})}</td>
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
            btn.onclick = () => fetchEvents(i);
            paginationControls.appendChild(btn);
        }
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[m];
        });
    }

    // Load page 1 on start
    fetchEvents();
});
</script>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
