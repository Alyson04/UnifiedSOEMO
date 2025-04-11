<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

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
$stmt->close();

$conn->close();
?>

<?php $title = "Admin Settings"; $style = "admindashboard_styles.css"; include '../includes/header.php'; include '../includes/navbar.php'; ?>

<a href="dashboard.php"><h1>Admin Dashboard</h1></a>
<main>
    <section class="dashboard">
        <div class="card"> <a href="manage_users.php"> <p>Manage Users</p> </a> </div>
        <div class="card"> <a href="manage_organizations.php"> <p>Organizations</p> </a> </div>
        <div class="card"> <a href="manage_events.php"> <p>Events</p> </a> </div>
        <div class="card"> <a href="settings.php"> <p>Settings</p> </a> </div>
    </section>

    <section class="settings">
        <h2>Admin Settings</h2>

        <button id="edit-btn">Edit</button>

        <form action="update_admin_settings.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled required>

            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter new password" disabled>

            <button type="submit" id="save-btn" disabled>Save Changes</button>
            <button type="button" id="cancel-btn" disabled>Cancel</button>
        </form>
    </section>
</main>

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
