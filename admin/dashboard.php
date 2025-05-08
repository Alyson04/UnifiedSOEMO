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
$style = "admindashboard.css";
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
      <a href="dashboard.html" class="card stat-card active">
        <h2>1,000</h2><p>Total Users</p>
      </a>
      <a href="Active_org.html" class="card stat-card">
        <h2>88</h2><p>Active Organizations</p>
      </a>
      <a href="upcoming.hmtl" class="card stat-card">
        <h2>6</h2><p>Upcoming Events</p>
      </a>
      <a href="past.html" class="card stat-card">
        <h2>8</h2><p>Past Events</p>
      </a>
    </section>
    

    <section class="charts">
      <div class="chart-box">
        <h3>User Growth (Jan–Jun)</h3>
        <div class="line-chart">
          <div class="grid-lines"></div>
          <svg viewBox="0 0 100 50" preserveAspectRatio="none">
            <polyline
              fill="none"
              stroke="#23406C"
              stroke-width="2"
              points="0,10 20,15 40,25 60,30 80,40 100,45"
            />
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
            <polyline
              fill="none"
              stroke="#23406C"
              stroke-width="2"
              points="0,15 20,18 40,35 60,30 80,20 100,40"
            />
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

<?php include '../includes/footer.php'; ?>
