<?php 
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow admins

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

$title = "Manage Events";
$style = "new-manage_events.css";
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

<!-- Main Content -->
<div class="content">
    <h2 class="page-title">MANAGE EVENTS</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

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
                    <th>Description</th>
                    <th>Event Date</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="eventTableBody">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
        <div id="paginationControls" class="pagination-controls"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('eventTableBody');
    const paginationControls = document.getElementById('paginationControls');

function fetchEvents(page = 1) {
    fetch(`get_events.php?page=${page}`)
        .then(res => res.json())
        .then(data => {
            console.log("User ID from server:", data.user_id); // <-- log user_id here

            renderEvents(data.events);
            renderPagination(data.total, data.perPage, page);
        })
        .catch(err => {
            console.error("Error fetching events:", err);
        });
}



function renderEvents(events) {
    const minRows = 5;
    tableBody.innerHTML = '';

    if (events.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6">No events found.</td></tr>';
        for (let i = 1; i < minRows; i++) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="6" style="height: 50px;"></td>';
            tableBody.appendChild(emptyRow);
        }
        return;
    }

    events.forEach(event => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="editable" data-field="title">${escapeHtml(event.title)}</span></td>
            <td><span class="editable" data-field="description">${escapeHtml(event.description || '')}</span></td>
            <td><span class="editable" data-field="event_date">${formatDate(event.event_date)}</span></td>
            <td>${formatDate(event.created_at)}</td>
            <td>${escapeHtml(event.status || 'Pending')}</td>
            <td>
                <button class="btn-edit">Edit</button>
                <button class="btn-save" style="display:none;">Save</button>
                <button class="btn-cancel" style="display:none;">Cancel</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    for (let i = events.length; i < minRows; i++) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `<td colspan="6" style="height: 50px; visibility: hidden;">&nbsp;</td>`;
        tableBody.appendChild(emptyRow);
    }

    attachEditListeners();
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

    function attachEditListeners() {
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            const tr = this.closest('tr');
            tr.querySelectorAll('.editable').forEach(span => {
                const value = span.textContent;
                const field = span.dataset.field;

                let input = document.createElement(field === 'event_date' ? 'input' : 'textarea');
                input.name = field;
                input.value = value;

                if (field === 'event_date') {
                    input.type = 'date';
                    input.value = new Date(value).toISOString().split('T')[0];
                }

                span.replaceWith(input);
            });

            tr.querySelector('.btn-edit').style.display = 'none';
            tr.querySelector('.btn-save').style.display = 'inline-block';
            tr.querySelector('.btn-cancel').style.display = 'inline-block';
        });
    });

    document.querySelectorAll('.btn-cancel').forEach(button => {
        button.addEventListener('click', function () {
            fetchEvents(); // Just reload the events
        });
    });

    document.querySelectorAll('.btn-save').forEach(button => {
        button.addEventListener('click', function () {
            const tr = this.closest('tr');
            const title = tr.querySelector('input[name="title"], textarea[name="title"]').value;
            const description = tr.querySelector('input[name="description"], textarea[name="description"]').value;
            const event_date = tr.querySelector('input[name="event_date"]').value;

            // You need a way to identify the event ID (e.g. add it as a hidden field or data-* attribute)
            const eventId = tr.dataset.eventId;

            fetch('update_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: eventId, title, description, event_date })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    fetchEvents(); // Reload the updated table
                } else {
                    alert("Update failed");
                }
            })
            .catch(err => console.error("Update error:", err));
        });
    });
}


    fetchEvents();

    const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
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

.session-alert {
    position: fixed;
    top: 20px;
    left: 55%;
    transform: translateX(-50%);
    background-color: #4CAF50; /* Green by default for success */
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    max-width: 80%;
    text-align: center;
    animation: fadeInSlideDown 0.4s ease-in-out;
}

.session-alert.error {
    background-color: #f44336; /* Red for error */
}

@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

</style>

<!-- Add shared JavaScript for admin_org section -->
<script src="../assets/scripts/admin_org_shared.js"></script>
<script src="../assets/scripts/admin_org_mobile.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
