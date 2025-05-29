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


// Get monthly signups for the organization
$sql_monthly_signups = "
    SELECT MONTH(created_at) AS month, COUNT(*) AS signups 
    FROM users 
    WHERE role = 'student' AND org_id = ? 
    GROUP BY MONTH(created_at) 
    ORDER BY MONTH(created_at)
";
$stmt = $conn->prepare($sql_monthly_signups);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result_monthly = $stmt->get_result();

$monthly_signups = array_fill(1, 12, 0); // Initialize with zeros for all months 1 to 12
while ($row = $result_monthly->fetch_assoc()) {
    $monthly_signups[(int)$row['month']] = (int)$row['signups'];
}
$stmt->close();

// Get recent users (last 5 signups)
$sql_recent_signups = "SELECT fullName, email FROM users WHERE org_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql_recent_signups);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result_recent_signups = $stmt->get_result();

$recent_signups = [];
while ($row = $result_recent_signups->fetch_assoc()) {
    $recent_signups[] = $row;
}
$stmt->close();
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

  <!-- User Growth Stats -->
  <section class="charts">
    <div class="chart-box" style="width: 100%; margin-bottom: 20px;">
      <h3>User Growth (Monthly)</h3>
      <div class="line-chart" style="position: relative; height: 180px;">
        <div class="grid-lines"></div>
        <svg viewBox="0 0 100 50" preserveAspectRatio="none" style="position: absolute; top: 10px; left: 10px; width: calc(100% - 10px); height: 150px;">
          <polyline id="monthly-growth-polyline" fill="none" stroke="#23406C" stroke-width=".5" points="" />
        </svg>
      </div>
      <div class="month-numbers" style="margin-top: 12px; font-weight: 600; color: #23406C; display: grid; grid-template-columns: repeat(12, 1fr); text-align: center; gap: 8px;">
        <!-- Month Names -->
        <?php
        $month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach ($month_names as $month) {
            echo "<div>$month</div>";
        }
        // Actual signup counts row
        for ($m = 1; $m <= 12; $m++) {
            echo "<div>{$monthly_signups[$m]}</div>";
        }
        ?>
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
        <?php if (count($recent_signups) === 0): ?>
          <tr><td colspan="2">No recent signups found for this organization.</td></tr>
        <?php else: ?>
          <?php foreach ($recent_signups as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['fullName']); ?></td>
              <td><?= htmlspecialchars($user['email']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

</main>

<script>
  // Monthly signups data from PHP backend
  const monthlySignups = <?= json_encode(array_values($monthly_signups)); ?>;

  function buildPoints(data) {
    const maxCount = Math.max(...data, 1); // Prevent division by zero
    return data.map((count, i) => {
      const x = i * (100 / (data.length - 1)); // Evenly spread along x-axis
      const y = 50 - (count / maxCount) * 40; // scale y (invert for SVG coords)
      return `${x},${y}`;
    }).join(' ');
  }

  document.addEventListener('DOMContentLoaded', () => {
    const monthlyGrowthPolyline = document.getElementById('monthly-growth-polyline');
    if (monthlyGrowthPolyline) monthlyGrowthPolyline.setAttribute('points', buildPoints(monthlySignups));
  });
</script>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>



