<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get total users excluding admin and deleted accounts
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin' AND status != 'deleted'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

// Get total organizations (exclude orgs whose owner is deleted)
$sql_orgs = "
  SELECT COUNT(*) AS total_organizations 
  FROM organizations o
  JOIN users u ON o.id = u.ID
  WHERE u.status != 'deleted'";
$result_orgs = $conn->query($sql_orgs);
$total_organizations = $result_orgs->fetch_assoc()['total_organizations'];

// Get upcoming events where creator is not deleted
$sql_events = "
  SELECT COUNT(*) AS total_events 
  FROM events e
  JOIN users u ON e.org_id = u.ID
  WHERE e.event_date >= CURDATE() AND u.status != 'deleted'";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

// Get past events where creator is not deleted
$sql_past = "
  SELECT COUNT(*) AS past_events 
  FROM events e
  JOIN users u ON e.org_id = u.ID
  WHERE e.event_date < CURDATE() AND u.status != 'deleted'";
$result_past = $conn->query($sql_past);
$past_events = $result_past->fetch_assoc()['past_events'];

// Get recent events (no change needed unless displayed)
$sql_recent_events = "
  SELECT title, event_date 
  FROM events e
  JOIN users u ON e.org_id = u.ID
  WHERE u.status != 'deleted'
  ORDER BY event_date DESC 
  LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);
$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch admin's full name
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

// Recent signups (excluding admin and deleted)
$sql_recent_signups = "
  SELECT fullName, email 
  FROM users 
  WHERE role != 'admin' AND status != 'deleted' 
  ORDER BY created_at DESC 
  LIMIT 5";
$result_recent_signups = $conn->query($sql_recent_signups);
$recent_signups = [];
if ($result_recent_signups) {
    while ($row = $result_recent_signups->fetch_assoc()) {
        $recent_signups[] = $row;
    }
}

// Monthly signups for chart
$sql_monthly = "
  SELECT MONTH(created_at) AS month, COUNT(*) AS signups 
  FROM users 
  WHERE role != 'admin' AND status != 'deleted' AND YEAR(created_at) = YEAR(CURDATE()) 
  GROUP BY MONTH(created_at) 
  ORDER BY MONTH(created_at)";
$result_monthly = $conn->query($sql_monthly);
$monthly_signups = array_fill(1, 12, 0);
while ($row = $result_monthly->fetch_assoc()) {
    $monthly_signups[(int)$row['month']] = (int)$row['signups'];
}
$conn->close();

$monthly_signups_json = json_encode(array_values($monthly_signups));

$title = "Unified SOEMO Dashboard";
$style = "admindashboard.css";
include '../includes/header.php';
?>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<section class="stats">
  <a href="dashboard.php" class="card stat-card active">
    <h2><?= $total_users ?></h2>
    <p>Total Users</p>
  </a>
  <a href="upcoming.php" class="card stat-card">
    <h2><?= $total_events ?></h2>
    <p>Upcoming Events</p>
  </a>
  <a href="past.php" class="card stat-card">
    <h2><?= $past_events ?></h2>
    <p>Past Events</p>
  </a>
</section>

<section class="charts">
  <div class="chart-box" style="width: 100%;">
    <h3>User Growth (Jan–Dec)</h3>
    <div class="line-chart" style="position: relative; height: 180px;">
      <div class="grid-lines"></div>
      <svg viewBox="0 0 100 50" preserveAspectRatio="none" style="position: absolute; top: 10px; left: 10px; width: calc(100% - 10px); height: 150px;">
        <polyline id="year-polyline" fill="none" stroke="#23406C" stroke-width="0.5" points="" />
      </svg>
    </div>
    <div class="month-numbers" style="margin-top: 12px; font-weight: 600; color: #23406C; display: grid; grid-template-columns: repeat(12, 1fr); text-align: center; gap: 8px;">
      <?php
      $month_names = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      foreach ($month_names as $month) echo "<div>$month</div>";
      for ($m = 1; $m <= 12; $m++) echo "<div>{$monthly_signups[$m]}</div>";
      ?>
    </div>
  </div>
</section>

<section class="recent-signups">
  <h3>Recent Signups</h3>
  <table>
    <thead><tr><th>Name</th><th>Email</th></tr></thead>
    <tbody>
      <?php if (!empty($recent_signups)) : ?>
        <?php foreach ($recent_signups as $user) : ?>
          <tr>
            <td><?= htmlspecialchars($user['fullName']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else : ?>
        <tr><td colspan="2">No recent signups found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<script>
  const monthlySignups = <?= $monthly_signups_json ?>;
  function buildPoints(data) {
    const maxCount = Math.max(...data, 1);
    return data.map((count, i) => {
      const x = i * (100 / (data.length - 1));
      const y = 50 - (count / maxCount) * 40;
      return `${x},${y}`;
    }).join(' ');
  }
  document.addEventListener('DOMContentLoaded', () => {
    const yearPolyline = document.getElementById('year-polyline');
    if (yearPolyline) yearPolyline.setAttribute('points', buildPoints(monthlySignups));
  });
</script>
</main>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
