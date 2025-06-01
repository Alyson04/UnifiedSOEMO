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

// Get total users (students) with same org_id and not deleted
$total_users = 0;
if ($org_id !== null) {
    $sql = "SELECT COUNT(*) AS total_users FROM users WHERE role = 'student' AND org_id = ? AND status != 'deleted'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_users = $result->fetch_assoc()['total_users'];
    $stmt->close();
}

// Get upcoming events count
$total_events = 0;
if ($org_id !== null) {
    $sql_events = "
        SELECT COUNT(*) AS total_events 
        FROM events
        WHERE event_date >= CURDATE() 
          AND org_id = ?
    ";
    $stmt_events = $conn->prepare($sql_events);
    $stmt_events->bind_param("i", $org_id);
    $stmt_events->execute();
    $result_events = $stmt_events->get_result();
    $total_events = $result_events->fetch_assoc()['total_events'];
    $stmt_events->close();
}

// Get past events count
$past_events = 0;
if ($org_id !== null) {
    $sql_past = "
        SELECT COUNT(*) AS past_events 
        FROM events
        WHERE event_date < CURDATE() 
          AND org_id = ?
    ";
    $stmt_past = $conn->prepare($sql_past);
    $stmt_past->bind_param("i", $org_id);
    $stmt_past->execute();
    $result_past = $stmt_past->get_result();
    $past_events = $result_past->fetch_assoc()['past_events'];
    $stmt_past->close();
}

// Upcoming events list
$upcoming_events = [];
if ($org_id !== null) {
    $sql_upcoming_events = "
        SELECT title, event_date 
        FROM events
        WHERE event_date >= CURDATE() 
          AND org_id = ?
        ORDER BY event_date ASC
    ";
    $stmt_upcoming = $conn->prepare($sql_upcoming_events);
    $stmt_upcoming->bind_param("i", $org_id);
    $stmt_upcoming->execute();
    $result_upcoming_events = $stmt_upcoming->get_result();
    while ($row = $result_upcoming_events->fetch_assoc()) {
        $upcoming_events[] = $row;
    }
    $stmt_upcoming->close();
}

// Recent events list (limit 5)
$recent_events = [];
if ($org_id !== null) {
    $sql_recent_events = "
        SELECT title, event_date 
        FROM events
        WHERE org_id = ?
        ORDER BY event_date DESC 
        LIMIT 5
    ";
    $stmt_recent = $conn->prepare($sql_recent_events);
    $stmt_recent->bind_param("i", $org_id);
    $stmt_recent->execute();
    $result_recent_events = $stmt_recent->get_result();
    while ($row = $result_recent_events->fetch_assoc()) {
        $recent_events[] = $row;
    }
    $stmt_recent->close();
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
  <div class="events-column dates-column">
    <h3>Event Dates</h3>
    <?php if (!empty($upcoming_events)) : ?>
        <?php foreach ($upcoming_events as $event) : ?>
            <div class="event-item"><?= date("M d, Y", strtotime($event['event_date'])) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="no-events">No upcoming events found.</div>
    <?php endif; ?>
  </div>

  <div class="events-column names-column">
    <h3>Event Names</h3>
    <?php if (!empty($upcoming_events)) : ?>
        <?php foreach ($upcoming_events as $event) : ?>
            <div class="event-item"><?= htmlspecialchars($event['title']) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="no-events">No upcoming events found.</div>
    <?php endif; ?>
  </div>
</section>

</main>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
