<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;

if (!$admin_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch admin details
$sql = "SELECT fullName, email FROM users WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
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

$title = "Admin Settings"; 
$style = "adminsettings_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<div class="main-layout">
    <!-- Sidebar -->
    <?php
    include '../includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">Admin Settings</h2>

        <button id="edit-btn">Edit</button>

        <form action="../api/settings.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($admin['email']); ?>" disabled required>

            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter new password" disabled>

            <button type="submit" id="save-btn" disabled>Save Changes</button>
            <button type="button" id="cancel-btn" disabled>Cancel</button>
        </form>
    </div>
</div>

<script>
document.getElementById("edit-btn").addEventListener("click", function () {
    document.getElementById("email").disabled = false;
    document.getElementById("password").disabled = false;
    document.getElementById("save-btn").disabled = false;
    document.getElementById("cancel-btn").disabled = false;
    this.style.display = "none"; // Hide Edit button
});

document.getElementById("cancel-btn").addEventListener("click", function () {
    document.getElementById("email").disabled = true;
    document.getElementById("password").disabled = true;
    document.getElementById("save-btn").disabled = true;
    document.getElementById("cancel-btn").disabled = true;
    document.getElementById("edit-btn").style.display = "inline-block"; // Show Edit button again
});
</script>

<?php include '../includes/footer.php'; ?>
