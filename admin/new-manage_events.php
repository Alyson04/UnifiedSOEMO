<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch all events
$sql = "SELECT events.id, events.title, events.event_date, organizations.name AS org_name, events.created_at
        FROM events
        LEFT JOIN organizations ON events.org_id = organizations.id
        ORDER BY events.created_at DESC";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

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
<?php
include '../includes/navbar.php';
?>

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
                <tbody>
                    <?php if (!empty($events)) : ?>
                        <?php foreach ($events as $event) : ?>
                            <tr>
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><?= date("M d, Y", strtotime($event['event_date'])) ?></td>
                                <td><?= htmlspecialchars($event['org_name'] ?? 'N/A') ?></td>
                                <td><?= date("M d, Y", strtotime($event['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">No events found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
