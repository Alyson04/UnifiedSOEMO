<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get total users excluding admin and deleted users
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin' AND status != 'deleted'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

// Get total organizations (exclude those where the organization owner's account is deleted)
$sql_orgs = "
    SELECT COUNT(*) AS total_organizations 
    FROM organizations o 
    JOIN users u ON o.id = u.ID 
    WHERE u.status != 'deleted'";
$result_orgs = $conn->query($sql_orgs);
$total_organizations = $result_orgs->fetch_assoc()['total_organizations'];

// Get upcoming events (exclude events where the organizer's account is deleted)
$sql_events = "
    SELECT COUNT(*) AS total_events 
    FROM events e 
    JOIN users u ON e.org_id = u.ID 
    WHERE e.event_date >= CURDATE() AND u.status != 'deleted'";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

// Get past events (exclude events by deleted users)
$sql_past = "
    SELECT COUNT(*) AS past_events 
    FROM events e 
    JOIN users u ON e.org_id = u.ID 
    WHERE e.event_date < CURDATE() AND u.status != 'deleted'";
$result_past = $conn->query($sql_past);
$past_events = $result_past->fetch_assoc()['past_events'];

// Fetch past event details
$sql_past_events = "
    SELECT e.title, e.event_date 
    FROM events e 
    JOIN users u ON e.org_id = u.ID 
    WHERE e.event_date < CURDATE() AND u.status != 'deleted' 
    ORDER BY e.event_date DESC";
$result_past_events = $conn->query($sql_past_events);

$past_events_list = [];
while ($row = $result_past_events->fetch_assoc()) {
    $past_events_list[] = $row;
}

// Get recent events (still filtered by active users only)
$sql_recent_events = "
    SELECT e.title, e.event_date 
    FROM events e 
    JOIN users u ON e.org_id = u.ID 
    WHERE u.status != 'deleted' 
    ORDER BY e.event_date DESC 
    LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);

$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

// Get logged-in admin info
$admin_id = $_SESSION['user_id'] ?? null;
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
$style = "past.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<section class="stats">
  <a href="dashboard.php" class="card stat-card">
    <h2><?= $total_users ?></h2><p>Total Users</p>
  </a>
  <a href="upcoming.php" class="card stat-card">
    <h2><?= $total_events ?></h2><p>Upcoming Events</p>
  </a>
  <a href="past.php" class="card stat-card active">
    <h2><?= $past_events ?></h2><p>Past Events</p>
  </a>
</section>     

<section class="events-upcoming">
  <div class="events-column">
    <h3>Past Event Dates</h3>
    <?php if (!empty($past_events_list)) : ?>
        <?php foreach ($past_events_list as $event) : ?>
            <div class="event-item"><?= date("M d, Y", strtotime($event['event_date'])) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div>No past events found.</div>
    <?php endif; ?>
  </div>

  <div class="events-column">
    <h3>Past Event Names</h3>
    <?php if (!empty($past_events_list)) : ?>
        <?php foreach ($past_events_list as $event) : ?>
            <div class="event-item"><?= htmlspecialchars($event['title']) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div>No past events found.</div>
    <?php endif; ?>
  </div>
</section>

</main>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
