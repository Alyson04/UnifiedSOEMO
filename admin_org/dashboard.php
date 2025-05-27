<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org_admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
$org_id = null;

// Fetch admin's full name and org_id from database
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

// Get total users with role 'student' and same org_id
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

// Get recent events
$sql_recent_events = "SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);

$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "admindashboard.css";
include '../includes/header.php';
?>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

  <!-- Stats -->
  <section class="stats">
    <a href="dashboard.php" class="card stat-card active">
      <h2><?= $total_users ?></h2><p>Total Members</p>
    </a>
    <a href="upcoming.php" class="card stat-card">
      <h2><?= $total_events ?></h2><p>Upcoming Events</p>
    </a>
    <a href="past.php" class="card stat-card">
      <h2><?= $past_events ?></h2><p>Past Events</p>
    </a>
  </section>

  <section class="charts">
    <div class="chart-box">
      <h3>User Growth (Jan–Jun)</h3>
      <div class="line-chart">
        <div class="grid-lines"></div>
        <svg viewBox="0 0 100 50" preserveAspectRatio="none">
          <polyline fill="none" stroke="#23406C" stroke-width="2" points="0,10 20,15 40,25 60,30 80,40 100,45" />
        </svg>
        <div class="x-axis-labels">
          <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
        </div>
      </div>
    </div>

    <div class="chart-box">
      <h3>User Growth (Jul–Dec)</h3>
      <div class="line-chart">
        <div class="grid-lines"></div>
        <svg viewBox="0 0 100 50" preserveAspectRatio="none">
          <polyline fill="none" stroke="#23406C" stroke-width="2" points="0,15 20,18 40,35 60,30 80,20 100,40" />
        </svg>
        <div class="x-axis-labels">
          <span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Recent Signups -->
  <section class="recent-signups">
    <h3>Recent Signups</h3>
    <table>
      <thead>
        <tr><th>Name</th><th>Email</th></tr>
      </thead>
      <tbody>
        <tr>
          <td>Jusphine Lacano</td>
          <td>jusphinemlacano@iskolarnagbayan.pup.edu.ph</td>
        </tr>
        <tr>
          <td>Janna Mae Caballero</td>
          <td>jannamaeccaballero@iskolarnagbayan.pup.edu.ph</td>
        </tr>
        <tr>
          <td>Rica Mae Malgapo</td>
          <td>ricamaemalgapo@iskolarnagbayan.pup.edu.ph</td>
        </tr>
      </tbody>
    </table>
  </section>

</main>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
