<?php 
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
$email = '';
require '../config/db_conn.php';
if ($student_id) {
    $sql_student = "SELECT fullName, email FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();
    if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();
        $student_name = ucwords(strtolower($row['fullName']));
        $email = $row['email'];
    }
    $stmt->close();
}

$conn->close();

$title = "Edit Profile";
$style = "editprofile_styles.css";
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<div class="main-layout">

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">Edit Profile</h2>

        <button id="edit-btn">Edit</button>
        <div class="profile-pic-container">
            <img src="../assets/images/default-profile.png" alt="Profile Picture" id="profile-preview">
            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" disabled>
        </div>

        <form action="../api/update_profile.php" method="POST">
            <label for="fullName">Full Name:</label>
            <input type="text" name="fullName" id="fullName" value="<?= htmlspecialchars($student_name); ?>" disabled required>    

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email); ?>" disabled required>

            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter new password" disabled>

            <div class="button-group">
                <button type="submit" id="save-btn" disabled>Save Changes</button>
                <button type="button" id="cancel-btn" disabled>Cancel</button>
            </div>

        </form>
    </div>
</div>

<script>
document.getElementById("edit-btn").addEventListener("click", function () {
    document.getElementById("profile_pic").disabled = false;
    document.getElementById("fullName").disabled = false;
    document.getElementById("email").disabled = false;
    document.getElementById("password").disabled = false;
    document.getElementById("save-btn").disabled = false;
    document.getElementById("cancel-btn").disabled = false;
    this.style.display = "none"; // Hide Edit button
});

document.getElementById("cancel-btn").addEventListener("click", function () {
    document.getElementById("fullName").disabled = true;
    document.getElementById("email").disabled = true;
    document.getElementById("password").disabled = true;
    document.getElementById("save-btn").disabled = true;
    document.getElementById("cancel-btn").disabled = true;
    document.getElementById("edit-btn").style.display = "block"; // Show Edit button again
});
</script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/editprofile_script.js"></script>

<?php include '../includes/footer.php'; ?>