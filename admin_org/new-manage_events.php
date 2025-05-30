<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;
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

    <!-- Add New Event Button -->
    <div class="add-event-button" style="margin: 15px 0;">
        <a href="add_event.php" class="btn-add-event">+ Add New Event</a>
    </div>

    <!-- Events Table -->
    <div class="event-table">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Event Date</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody id="eventTableBody">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>

        <!-- Pagination -->
        <div id="paginationControls" class="pagination-controls"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('eventTableBody');
    const paginationControls = document.getElementById('paginationControls');

    function fetchEvents(page = 1) {
        fetch(`get_events.php?page=${page}`)
            .then(res => res.json())
            .then(data => {
                renderEvents(data.events);
                renderPagination(data.total, data.perPage, page);
            })
            .catch(() => {
                tableBody.innerHTML = '<tr><td colspan="3">Error loading events.</td></tr>';
                paginationControls.innerHTML = '';
            });
    }

    function renderEvents(events) {
        if (events.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="3">No events found.</td></tr>';
            return;
        }
        tableBody.innerHTML = '';
        events.forEach(event => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(event.title)}</td>
                <td>${formatDate(event.event_date)}</td>
                <td>${formatDate(event.created_at)}</td>
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

    function formatDate(dateStr) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', options);
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[m];
        });
    }

    fetchEvents();
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
