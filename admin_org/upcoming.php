<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org_admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
$org_id = null;

// Fetch admin's full name and org_id
if ($admin_id) {
    $sql_admin = "SELECT fullName, org_id FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $admin_data = $result_admin->fetch_assoc();
        $admin_name = ucwords(strtolower($admin_data['fullName']));
        $org_id = $admin_data['org_id'];
    }
    $stmt->close();
}

// Get total users (students) with same org_id
$total_users = 0;
if ($org_id !== null) {
    $sql = "SELECT COUNT(*) AS total_users FROM users WHERE role = 'student' AND org_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_users = $result->fetch_assoc()['total_users'];
    $stmt->close();
}

// Get upcoming events
$sql_events = "SELECT COUNT(*) AS total_events FROM events WHERE event_date >= CURDATE()";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

// Get past events
$sql_past = "SELECT COUNT(*) AS past_events FROM events WHERE event_date < CURDATE()";
$result_past = $conn->query($sql_past);
$past_events = $result_past->fetch_assoc()['past_events'];

// Upcoming events list
$sql_upcoming_events = "SELECT title, event_date FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$result_upcoming_events = $conn->query($sql_upcoming_events);
$upcoming_events = [];
while ($row = $result_upcoming_events->fetch_assoc()) {
    $upcoming_events[] = $row;
}

// Recent events list
$sql_recent_events = "SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);
$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "upcoming.css";
include '../includes/header.php';
?>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<!-- Stats -->
<section class="stats">
  <a href="dashboard.php" class="card stat-card">
    <h2><?= $total_users ?></h2><p>Total Members</p>
  </a>
  <a href="upcoming.php" class="card stat-card active">
    <h2><?= $total_events ?></h2><p>Upcoming Events</p>
  </a>
  <a href="past.php" class="card stat-card">
    <h2><?= $past_events ?></h2><p>Past Events</p>
  </a>
</section>

<!-- Upcoming Events Section -->
<section class="events-upcoming">
  <div class="list-events">
    <h3>Event Dates</h3>
    <?php if (!empty($upcoming_events)) : ?>
        <?php foreach ($upcoming_events as $event) : ?>
            <div><?= date("M d", strtotime($event['event_date'])) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div>No upcoming events found.</div>
    <?php endif; ?>
  </div>

  <div class="list-events">
    <h3>Event Names</h3>
    <?php if (!empty($upcoming_events)) : ?>
        <?php foreach ($upcoming_events as $event) : ?>
            <div><?= htmlspecialchars($event['title']) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div>No upcoming events found.</div>
    <?php endif; ?>
  </div>
</section>

</main>

<?php include '../includes/footer.php'; ?>
