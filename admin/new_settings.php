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
$style = "new-settings.css";
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
         <div class="outer-box">
          <h2 class="section-title">Manage Users</h2>
      
          <div class="inner-card">
            <div class="card-row-horizontal">
              <!-- Left: User Information -->
              <div class="card-section user-info-section">
                  <h3>User Information</h3>
                  <div class="card-row">
                      <div class="card-label">User List:</div>
                      <div class="card-action">[View List] ✎</div>
                  </div>
                  <div class="card-row">
                      <div class="card-label">Add User:</div>
                      <div class="card-action">[Add New] ✎</div>
                  </div>
              </div>
            
              <!-- Right: Upload Logo -->
              <div class="card-section upload-section">
                  <h3>Organization Logo</h3>
                  <form action="#" method="POST" enctype="multipart/form-data">
                      <div class="upload-frame">
                          <label for="logo-upload" class="upload-label">
                              <img src="../fromOtherBranches/pics/logo.png" alt="Insert Logo Here" />
                              <span class="upload-text">Upload Media</span>
                          </label>
                          <input type="file" id="logo-upload" name="logo" accept="image/*" />
                      </div>
                  </form>
              </div>
            </div>            
      
              <!-- Section 2 -->
              <div class="card-section">
                  <h3>Roles & Permissions</h3>
                  <div class="card-row">
                      <div class="card-label">Manage Roles:</div>
                      <div class="card-action">[Manage] ✎</div>
                  </div>
                  <div class="card-row">
                      <div class="card-label">Edit Permissions:</div>
                      <div class="card-action">[Edit] ✎</div>
                  </div>
              </div>
      
              <!-- Section 3 -->
              <div class="card-section">
                  <h3>Activity Logs</h3>
                  <div class="card-row">
                      <div class="card-label">User Logs:</div>
                      <div class="card-action">[View Logs] ✎</div>
                  </div>
              </div>
              <!-- Section 4: Upload Logo / Media -->
          </div>
      </div>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
