<?php
require '../api/auth.php';
checkUserRole('admin');

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch admin name
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Organization Admin</title>
  <link rel="stylesheet" href="../assets/stylesheets/create_orgadmin.css">
</head>
<body>
<div class="container">

  <div class="sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
      <li><a href="admin_dashboard.php">Manage Users</a></li>
      <li><a href="#" class="active">Organizations</a></li>
      <li><a href="#">Events</a></li>
      <li><a href="#">Settings</a></li>
      <li><a href="../functions/logout.php">Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <div id="addRecordSection">
      <div class="card">
        <div class="card-header">
          <h3>Create an Organization Admin</h3>
        </div>
        <div class="card-body">
          <form id="form1">
            <div class="form-group">
              <label for="fullName">Fullname:</label>
              <input type="text" id="fullName" name="fullName" required />
            </div>
            <div class="form-group">
              <label for="email">Email:</label>
              <input type="text" id="email" name="email" required />
            </div>
            <div class="form-group">
              <label for="password">Password:</label>
              <input type="password" id="password" name="password" required />
            </div>
            <input type="hidden" id="role" name="role" value="org_admin" />
            <input type="hidden" id="is_approved" name="is_approved" value="approved" />
          </form>

          <form id="form2">
            <div class="form-group">
              <label for="name">Name of Organization:</label>
              <input type="text" id="name" name="name" required />
            </div>
            <div class="form-group">
              <label for="description">Short Description:</label>
              <input type="text" id="description" name="description" required />
            </div>
          </form>

          <form id="form3">
            <div class="form-group">
              <label for="objectives">Introduction:</label>
              <input type="text" id="objectives" name="objectives" required />
            </div>
            <div class="form-group">
              <label for="skills">Skills:</label>
              <input type="text" id="skills" name="skills" required />
            </div>
            <div class="form-group">
              <label for="requirements">Requirements:</label>
              <input type="text" id="requirements" name="requirements" required />
            </div>
            <div class="form-group">
              <label for="image">Organization Logo:</label>
              <input type="file" name="image" accept="image/*" />
            </div>
          </form>

          <button type="button" onclick="submitAllForms()" class="btn btn-success">Create Organization</button>
          <button type="button" onclick="history.back()" class="btn-cancel">Cancel</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/createorg_script.js"></script>
</body>
</html>
