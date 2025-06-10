<?php 
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;
$admin_name = '';

if ($admin_id) {
    $sql_admin = "SELECT CONCAT(firstName, ' ', COALESCE(middleName, ''), ' ', lastName) as fullName FROM newusers WHERE ID = ?";
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
            <tbody id="eventsTableBody">
                <!-- Loaded via AJAX -->
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
        <div id="paginationControls" class="pagination-controls"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('eventsTableBody');
    const paginationControls = document.getElementById('paginationControls');
    const minRows = 5;

    function fetchEvents(page = 1) {
        fetch(`get_events.php?page=${page}`)
            .then(res => res.json())
            .then(data => {
                renderEvents(data.events);
                renderPagination(data.total, data.perPage, page);
            })
            .catch(err => {
                console.error("Error fetching events:", err);
                tableBody.innerHTML = '<tr><td colspan="6">Error loading events.</td></tr>';
            });
    }

    function renderEvents(events) {
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
            tr.dataset.eventId = event.id;
            tr.innerHTML = `
                <td><span class="editable" data-field="title">${escapeHtml(event.title)}</span></td>
                <td><span class="editable" data-field="description">${escapeHtml(event.description || '')}</span></td>
                <td><span class="editable" data-field="event_date">${formatDate(event.event_date)}</span></td>
                <td>${formatDate(event.created_at)}</td>
                <td>${escapeHtml(event.status || 'Under Review')}</td>
                <td>
                    <button class="btn-edit">Edit</button>
                    <button class="btn-save" style="display:none;">Save</button>
                    <button class="btn-cancel" style="display:none;">Cancel</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        // Add empty rows if needed
        for (let i = events.length; i < minRows; i++) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="6" style="height: 50px; visibility: hidden;">&nbsp;</td>';
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
        if (!dateStr) return 'N/A';
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', options);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString().replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m];
        });
    }

    function attachEditListeners() {
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const tr = this.closest('tr');
                
                tr.querySelectorAll('.editable').forEach(span => {
                    const value = span.textContent;
                    const field = span.dataset.field;

                    let input;
                    if (field === 'description') {
                        input = document.createElement('textarea');
                        input.className = 'edit-desc';
                    } else {
                        input = document.createElement('input');
                        input.className = 'edit-' + field;
                    }

                    input.name = field;
                    input.value = value;

                    if (field === 'event_date') {
                        input.type = 'date';
                        const date = new Date(value);
                        input.value = date.toISOString().split('T')[0];
                    }

                    span.replaceWith(input);
                });

                this.style.display = 'none';
                tr.querySelector('.btn-save').style.display = 'inline-block';
                tr.querySelector('.btn-cancel').style.display = 'inline-block';
            });
        });

        document.querySelectorAll('.btn-save').forEach(button => {
            button.addEventListener('click', function() {
                const tr = this.closest('tr');
                const eventId = tr.dataset.eventId;
                
                const data = {
                    id: eventId,
                    title: tr.querySelector('[name="title"]').value,
                    description: tr.querySelector('[name="description"]').value,
                    event_date: tr.querySelector('[name="event_date"]').value
                };

                fetch('update_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        fetchEvents();
                    } else {
                        alert(response.message || "Update failed");
                    }
                })
                .catch(err => {
                    console.error("Update error:", err);
                    alert("Failed to update event. Please try again.");
                });
            });
        });

        document.querySelectorAll('.btn-cancel').forEach(button => {
            button.addEventListener('click', () => fetchEvents());
        });
    }

    // Initialize
    fetchEvents();

    // Handle session alerts
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
.edit-title, .edit-desc, .edit-date { 
    width: 100%;
    padding: 4px;
    box-sizing: border-box;
}
.edit-desc {
    min-height: 60px;
}
.session-alert {
    position: fixed;
    top: 20px;
    left: 55%;
    transform: translateX(-50%);
    background-color: #4CAF50;
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
    background-color: #f44336;
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
<script src="../assets/scripts/hamburger.js"></script>
<?php include '../includes/footer.php'; ?>
