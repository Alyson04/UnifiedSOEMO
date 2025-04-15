<?php 
require '../api/auth.php';
checkUserRole('admin'); // Ensure only admins can access this page

require '../config/db_conn.php';

// Fetch all events
$sql = "SELECT id, title, event_date, organization_id, created_at FROM events ORDER BY created_at DESC";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

$conn->close();
?>

<?php 
$title = "Manage Events"; 
$style = "manageevents_Styles.css"; // Your updated CSS for this page
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<div class="main-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php"><img src="dashboard-icon.png" alt=""> Dashboard</a></li>
            <li><a href="manage_users.php"><img src="user-icon.png" alt=""> Manage Users</a></li>
            <li><a href="manage_organizations.php"><img src="org-icon.png" alt=""> Organizations</a></li>
            <li><a href="manage_events.php" class="active"><img src="event-icon.png" alt=""> Events</a></li>
            <li><a href="settings.php"><img src="settings-icon.png" alt=""> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">Manage Events</h2>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" placeholder="Search Events...">
            <button>🔍</button>
        </div>

        <!-- Events Table -->
        <div class="event-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Organization ID</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($events)) : ?>
                        <?php foreach ($events as $event) : ?>
                            <tr>
                                <td><?= htmlspecialchars($event['id']) ?></td>
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><?= date("M d, Y", strtotime($event['event_date'])) ?></td>
                                <td><?= htmlspecialchars($event['organization_id']) ?></td>
                                <td><?= date("M d, Y", strtotime($event['created_at'])) ?></td>
                                <td><button class="edit-btn">Edit/Cancel</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="6">No events found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
