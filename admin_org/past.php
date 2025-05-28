<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org_admins

require '../config/db_conn.php';

$total_users = 0;
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
$org_id = null;

// Get org_id and admin's name
if ($admin_id) {
    $stmt = $conn->prepare("SELECT fullName, org_id FROM users WHERE ID = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($fullName, $org_id);
    if ($stmt->fetch()) {
        $admin_name = ucwords(strtolower($fullName));
    }
    $stmt->close();
}

// Count students in the same org
if ($org_id !== null) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'student' AND org_id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $stmt->bind_result($total_users);
    $stmt->fetch();
    $stmt->close();
}

// Get upcoming events
$sql_events = "SELECT COUNT(*) AS total_events FROM events WHERE event_date >= CURDATE()";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

$sql_past = "SELECT COUNT(*) AS past_events FROM events WHERE event_date < CURDATE()";
$result_past = $conn->query($sql_past);
$past_events = $result_past->fetch_assoc()['past_events'];

$sql_past_events = "SELECT title, event_date FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC";
$result_past_events = $conn->query($sql_past_events);

$past_events_list = [];
while ($row = $result_past_events->fetch_assoc()) {
    $past_events_list[] = $row;
}

// Get recent events
$sql_recent_events = "SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);

$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "past.css";
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

<?php include '../includes/footer.php'; ?>
