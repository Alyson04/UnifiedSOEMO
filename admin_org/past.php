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
} else {
    $total_users = 0;
}

// Get upcoming events count filtered by org_id
if ($org_id !== null) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND org_id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $stmt->bind_result($total_events);
    $stmt->fetch();
    $stmt->close();
} else {
    $total_events = 0;
}

// Get past events count filtered by org_id
if ($org_id !== null) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE event_date < CURDATE() AND org_id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $stmt->bind_result($past_events);
    $stmt->fetch();
    $stmt->close();
} else {
    $past_events = 0;
}

// Get past events list filtered by org_id
if ($org_id !== null) {
    $stmt = $conn->prepare("SELECT title, event_date FROM events WHERE event_date < CURDATE() AND org_id = ? ORDER BY event_date DESC");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $past_events_list = [];
    while ($row = $result->fetch_assoc()) {
        $past_events_list[] = $row;
    }
    $stmt->close();
} else {
    $past_events_list = [];
}

// Get recent events list filtered by org_id
if ($org_id !== null) {
    $stmt = $conn->prepare("SELECT title, event_date FROM events WHERE org_id = ? ORDER BY event_date DESC LIMIT 5");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recent_events = [];
    while ($row = $result->fetch_assoc()) {
        $recent_events[] = $row;
    }
    $stmt->close();
} else {
    $recent_events = [];
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
<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
