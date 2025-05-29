<?php 
require '../api/auth.php';
checkUserRole('admin'); // Ensure only admins can access this page

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;

// Fetch all events
$sql = "SELECT id, title, event_date, org_id, created_at FROM events ORDER BY created_at DESC";
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

$title = "Manage Events"; 
$style = "manageevents_Styles.css"; // Your updated CSS for this page
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<div class="main-layout">
    <!-- Sidebar -->
    <?php
    include '../includes/sidebar.php';
    ?>

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
                                <td><?= htmlspecialchars($event['org_id']) ?></td>
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
<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
