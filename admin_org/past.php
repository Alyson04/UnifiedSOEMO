<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org_admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
$org_id = null;
$total_users = 0;
$total_events = 0;
$past_events = 0;
$past_events_list = [];
$recent_events = [];

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

// Count students in the same org AND not deleted
if ($org_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'student' AND org_id = ? AND status != 'deleted'");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $stmt->bind_result($total_users);
    $stmt->fetch();
    $stmt->close();
}

// Count upcoming events
if ($org_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND org_id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $stmt->bind_result($total_events);
    $stmt->fetch();
    $stmt->close();
}

// Count past events
if ($org_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE event_date < CURDATE() AND org_id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $stmt->bind_result($past_events);
    $stmt->fetch();
    $stmt->close();
}

// Get past events list
if ($org_id) {
    $stmt = $conn->prepare("
        SELECT title, event_date 
        FROM events 
        WHERE event_date < CURDATE() 
        AND org_id = ? 
        ORDER BY event_date DESC
    ");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $past_events_list[] = $row;
    }
    $stmt->close();
}

// Get recent events (latest 5)
if ($org_id) {
    $stmt = $conn->prepare("
        SELECT title, event_date 
        FROM events 
        WHERE org_id = ? 
        ORDER BY event_date DESC 
        LIMIT 5
    ");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_events[] = $row;
    }
    $stmt->close();
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "past.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

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

<!-- Past Events Section -->
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

<!-- Scripts -->
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
