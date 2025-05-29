<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

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

// Handle optional role filter from query parameter
$role_filter = $_GET['role'] ?? '';

// Base SQL query
$sql = "SELECT id, fullName, email, role, created_at FROM users WHERE role != 'admin'";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role_filter);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch users
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "create_orgadmin.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>


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


<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>

