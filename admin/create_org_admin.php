<?php
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

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

$title = "Create Organization Admin";
$style = "admindashboard_styles.css";
include '../includes/header.php';
include '../includes/navbar.php';
?>   
    
        <div class="main-content">
        
            <div id="addRecordSection">
            <!-- Add Record Form -->
            <div class="card">
                <div class="card-header">
                <a href="new-manage_users.php" style="font-size:16px; padding: 10px 15px; background-color: #f44336; color: white; text-decoration: none; border-radius: 5px;">Back</a>
                    <h3>Create an Organization Admin</h3>
                </div>
                <div class="card-body">
                    <form id="form1">

                        <label for="fullName">Fullname:</label>
                        <input type="text" id="fullName" name="fullName" required><br>

                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email" required><br>

                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required><br>

                        <input type="hidden" id="role" name="role" value="org_admin" required><br>

                        <input type="hidden" id="is_approved" name="is_approved" value="approved" required><br>

                    </form>

                    <form id="form2">

                        <label for="name">Name of Organization:</label>
                        <input type="text" id="name" name="name" required><br>

                        <label for="description">Short Description:</label>
                        <input type="text" id="description" name="description" required><br>

                        <label for="image">Organization Logo:</label>
                        <input type="file" name="image" accept="image/*">

                        <br><br><br><br>


                    </form>

                    <form id="form3">

                        <label for="objectives">Introduction:</label>
                        <input type="text" id="objectives" name="objectives" required><br>

                        <label for="skills">Skills:</label>
                        <input type="text" id="skills" name="skills" required><br>

                        <label for="requirements">Requirements:</label>
                        <input type="text" id="requirements" name="requirements" required><br>

                    </form>

                    <button type="button" onclick="submitAllForms()">Create Organization</button>
                </div>
            </div>
        </div>
    </div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/createorg_script.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>