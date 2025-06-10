<?php 
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow org_admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
$org_id = null;
$total_users = 0;
$total_events = 0;
$past_events = 0;
$upcoming_events_list = [];

// Get org_id and admin's name
if ($admin_id) {
    $stmt = $conn->prepare("SELECT CONCAT_WS(' ', firstName, middleName, lastName) AS fullName, no.id as organization_id 
                           FROM newusers nu
                           JOIN neworganizations no ON no.user_id = nu.id
                           WHERE nu.ID = ? AND nu.role = 'orgAdmin'");
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
    $stmt = $conn->prepare("SELECT COUNT(n.id) FROM organization_members om
                           JOIN newusers n ON om.user_id = n.id
                           WHERE om.organization_id = ? 
                           AND n.role = 'student' 
                           AND n.status != 'deleted'");
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

// Get upcoming events list
if ($org_id) {
    $stmt = $conn->prepare("
        SELECT title, event_date 
        FROM events 
        WHERE event_date >= CURDATE() 
        AND org_id = ? 
        ORDER BY event_date ASC
    ");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcoming_events_list[] = $row;
    }
    $stmt->close();
}

$conn->close();

$title = "Upcoming Events";
$style = "upcoming.css";
include '../includes/header.php';
?>

<!-- Add shared CSS for admin_org section -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_shared.css">

<!-- Add mobile-specific styles -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_mobile.css">

<!-- Hamburger Menu Button -->
<button class="hamburger-menu">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
</button>

<!-- Overlay for mobile sidebar -->
<div class="overlay"></div>

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
  <div class="events-column">
    <h3>Event Dates</h3>
    <?php if (!empty($upcoming_events_list)) : ?>
        <?php foreach ($upcoming_events_list as $event) : ?>
            <div class="event-item"><?= date("M d, Y", strtotime($event['event_date'])) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div>No upcoming events found.</div>
    <?php endif; ?>
  </div>

  <div class="events-column">
    <h3>Event Names</h3>
    <?php if (!empty($upcoming_events_list)) : ?>
        <?php foreach ($upcoming_events_list as $event) : ?>
            <div class="event-item"><?= htmlspecialchars($event['title']) ?></div>
        <?php endforeach; ?>
    <?php else : ?>
        <div>No upcoming events found.</div>
    <?php endif; ?>
  </div>
</section>

</main>

<script src="../assets/scripts/admin_org_mobile.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
