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

<?php $title = "Manage Events"; $style = "admindashboard_styles.css"; include '../includes/header.php'; include '../includes/navbar.php'; ?>

<a href="dashboard.php"><h1>Admin Dashboard</h1></a>
<main>
    <section class="dashboard">
        <div class="card"> <a href="manage_users.php"> <p>Manage Users</p> </a> </div>
        <div class="card"> <a href="manage_organizations.php"> <p>Organizations</p> </a> </div>
        <div class="card"> <a href="manage_events.php"> <p>Events</p> </a> </div>
        <div class="card"> <a href="settings.php"> <p>Settings</p> </a> </div>
    </section>

    <section class="events">
        <h2>Events</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Event Date</th>
                    <th>Organization ID</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($events)) : ?>
                    <?php foreach ($events as $event) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['id']); ?></td>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($event['event_date'])); ?></td>
                            <td><?php echo htmlspecialchars($event['organization_id']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($event['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="5">No events found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
