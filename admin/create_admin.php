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
$style = "create_admin.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>


      <div id="addRecordSection">
        <div class="card mb-4">
          <div class="card-header">
            <h3>Create an Admin</h3>
          </div>
          <div class="card-body">
            <form action="../api/create_admin.php" method="POST">
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

              <input type="hidden" name="role" value="admin" />
              <input type="hidden" name="is_approved" value="approved" />

              <button type="submit" class="btn btn-success">Add Record</button>
              <button type="button" onclick="history.back()" class="btn-cancel">Cancel</button>
            </form>
                </div>
            </div>
        </div>


<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>