<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get total users excluding admin
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

// Get total organizations
$sql_orgs = "SELECT COUNT(*) AS total_organizations FROM organizations";
$result_orgs = $conn->query($sql_orgs);
$total_organizations = $result_orgs->fetch_assoc()['total_organizations'];

// Get upcoming events
$sql_events = "SELECT COUNT(*) AS total_events FROM events WHERE event_date >= CURDATE()";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

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
// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch admin's full name from database
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
$style = "Active_org.css";
include '../includes/header.php';
?>


<?php
include '../includes/sidebar.php';
?>

  <!-- Main Panel -->
<main class="main-content">
<?php
include '../includes/navbar.php';
?>

    <!-- Stats -->
    <section class="stats">
      <a href="dashboard.php" class="card stat-card ">
      <h2><?= $total_users ?></h2><p>Total Users</p>
      </a>
      <a href="Active_org.php" class="card stat-card active">
      <h2><?= $total_organizations ?></h2><p>Active Organizations</p>
      </a>
      <a href="upcoming.php" class="card stat-card">
      <h2><?= $total_events ?></h2><p>Upcoming Events</p>
      </a>
      <a href="past.php" class="card stat-card">
      <h2><?= $past_events ?></h2><p>Past Events</p>
      </a>
    </section>   

    <!-- Recent Signups -->
   <!-- Charts Section -->
<section class="charts">
    <!-- Active vs. Inactive Pie Chart Simulation -->
    <div class="chart-box">
      <h3>Active vs. Inactive</h3>
      <div class="donut-chart">
        <div class="donut"></div>
        <div class="legend">
          <div><span class="dot active"></span>Active</div>
          <div><span class="dot inactive"></span>Inactive</div>
        </div>
      </div>
    </div>
  
    <!-- Top Performing Organizations Bar Chart Simulation -->
    <div class="chart-box">
      <h3>Top Performing Organizations</h3>
      <div class="bar-chart">
        <div class="bar-row">
          <span>PUP Red Cross Youth Council</span>
          <div class="bar"><div style="width: 95%"></div></div>
        </div>
        <div class="bar-row">
          <span>PUP Seeds of the Nations</span>
          <div class="bar"><div style="width: 70%"></div></div>
        </div>
        <div class="bar-row">
          <span>PUP The Symposium</span>
          <div class="bar"><div style="width: 50%"></div></div>
        </div>
        <div class="bar-row">
          <span>AWS Cloud Club PUP</span>
          <div class="bar"><div style="width: 30%"></div></div>
        </div>
      </div>
    </div>
  </section>
  
      
  </main>

<?php include '../includes/footer.php'; ?>

