<?php 
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
$email = '';
require '../config/db_conn.php';

$profile_img = '../assets/uploads_pfp/profile.png'; // fallback image

if ($student_id) {
    $sql_student = "SELECT fullName, email, profile_picture FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

    if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();
        $student_name = ucwords(strtolower($row['fullName']));
        $email = $row['email'];

        if (!empty($row['profile_picture'])) {
            $uploaded_path = "../assets/uploads_pfp/" . $row['profile_picture'];
            if (file_exists($uploaded_path)) {
                $profile_img = $uploaded_path;
            }
        }
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

        <form action="../api/update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-pic-container">
                <img src="<?= htmlspecialchars($profile_img); ?>" alt="Profile Picture" id="profile-preview">
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*" disabled>
            </div>

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
    document.getElementById("profile_pic").disabled = true;
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
