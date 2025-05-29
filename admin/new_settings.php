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
          <h2 class="section-title">EDIT PROFILE</h2>
      
          <div class="inner-card">
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

               <div class="card-section">
                    <h3>Account Settings</h3>
                    <div class="card-row">
                    <div class="card-label">Full Name   :</div>
                    <div class="card-action">[Change Full Name] ✎</div>
                </div>
                <div class="card-row">
                    <div class="card-label">Email    :</div>
                    <div class="card-action">[Change Email] ✎</div>
                </div>
                <div class="card-row">
                    <div class="card-label">Username    :</div>
                    <div class="card-action">[Change Username] ✎</div>
                </div>
                <div class="card-row">
                    <div class="card-label">Password    :</div>
                    <div class="card-action">[Change Password] ✎</div>
                </div>

                <!-- Save/Cancel Buttons -->
                <div class="action-buttons">
                    <button class="save-btn" type="submit">Save Changes</button>
                    <button class="cancel-btn" type="button">Cancel</button>
                </div>

            </div>      
        </div>
      </div>
<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
