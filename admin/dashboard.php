<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

$sql_orgs = "SELECT COUNT(*) AS total_organizations FROM organizations";
$result_orgs = $conn->query($sql_orgs);
$total_organizations = $result_orgs->fetch_assoc()['total_organizations'];

$sql_events = "SELECT COUNT(*) AS total_events FROM events WHERE event_date >= CURDATE()";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

$sql_recent_events = "SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);

$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

$conn->close();

?>

<?php $title = "Admin Dashboard";$style = "admindashboard_styles.css"; include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<h1>Admin Dashboard</h1>
<main>
        <section class="dashboard">
            <div class="card"> <a href = "manage_users.php"> <p>Manage Users</p> </a> </div>
            <div class="card"> <a href = "manage_organizations.php"> <p>Organizations</p> </a> </div>
            <div class="card"> <a href = "manage_events.php"> <p>Events</p> </a> </div>
            <div class="card"> <a href = "settings.php"> <p>Settings</p> </a> </div>
        </section>
        
        <section class="summary">
            <h2>Dashboard Summary</h2>
            <div class="stat">
                <span>Total Users: <?php echo $total_users; ?></span>
                <div class="progress"><div style="width: <?php echo min($total_users, 100); ?>%;"></div></div>
            </div>
            <div class="stat">
                <span>Active Organizations: <?php echo $total_organizations; ?></span>
                <div class="progress"><div style="width: <?php echo min($total_organizations, 100); ?>%;"></div></div>
            </div>
            <div class="stat">
                <span>Upcoming Events: <?php echo $total_events; ?></span>
                <div class="progress"><div style="width: <?php echo min($total_events, 100); ?>%;"></div></div>
            </div>
            <div class="stat">
                <span>Recent Events:</span>
                <ul>
                    <?php 
                    if (!empty($recent_events)) {
                        foreach ($recent_events as $event) {
                            echo "<li>" . htmlspecialchars($event['title']) . " - " . date("M d, Y", strtotime($event['event_date'])) . "</li>";
                        }
                    } else {
                        echo "<li>No recent events</li>";
                    }
                    ?>
                </ul>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>
