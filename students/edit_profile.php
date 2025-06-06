<?php 
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
$email = '';
require '../config/db_conn.php';

$profile_img = '../assets/uploads_pfp/profile.png'; // fallback image

if ($student_id) {
    $sql_student = "SELECT firstName, middleName, lastName, email, profile_picture FROM newusers WHERE id = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();

        // Combine first, middle, last name into one display name
        $firstName = $row['firstName'] ?? '';
        $middleName = $row['middleName'] ?? '';
        $lastName = $row['lastName'] ?? '';
        $student_name = ucwords(strtolower(trim("$firstName $middleName $lastName")));

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
<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">Edit Profile</h2>

        <button id="edit-btn">Edit</button>

        <form action="../api/update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-pic-container">
                <img src="<?= htmlspecialchars($profile_img); ?>" alt="Profile Picture" id="profile-preview">
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*" disabled>
            </div>

            <label for="oldPassword">Old Password:</label>
            <div class="input-icon">
                <input type="password" name="oldPassword" id="oldPassword" placeholder="Enter old password" disabled>
                <span class="toggle-icon" onclick="togglePassword('oldPassword', this)" style="display: none;">👁️</span>
            </div>

            <label for="newPassword">New Password:</label>
            <div class="input-icon">
                <input type="password" name="newPassword" id="newPassword" placeholder="Enter new password" disabled>
                <span class="toggle-icon" onclick="togglePassword('newPassword', this)" style="display: none;">👁️</span>
            </div>
            <small id="passwordWarning" style="color: red; display: none;">
                Password must be 8–20 characters, include 2 numbers and 2 special characters.
            </small>

            <label for="confirmNewPassword">Confirm New Password:</label>
            <div class="input-icon">
                <input type="password" name="confirmNewPassword" id="confirmNewPassword" placeholder="Confirm new password" disabled>
                <span class="toggle-icon" onclick="togglePassword('confirmNewPassword', this)" style="display: none;">👁️</span>
            </div>
            <small id="passwordMismatch" style="color: red; display: none;">
                Passwords do not match.
            </small>


            <div class="button-group">
                <button type="submit" id="save-btn" disabled>Save Changes</button>
                <button type="button" id="cancel-btn" disabled>Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
    .session-alert {
    position: fixed;
    top: 100px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50; /* Green by default for success */
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    max-width: 80%;
    text-align: center;
    animation: fadeInSlideDown 0.4s ease-in-out;
}

.session-alert.error {
    background-color: #f44336; /* Red for error */
}

@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}
</style>

<script>
document.getElementById("edit-btn").addEventListener("click", function () {
    document.getElementById("profile_pic").disabled = false;
    document.getElementById("oldPassword").disabled = false;
    document.getElementById("newPassword").disabled = false;
    document.getElementById("confirmNewPassword").disabled = false;

    document.querySelectorAll(".toggle-icon").forEach(icon => {
        icon.style.display = "inline";
    });

    document.getElementById("save-btn").disabled = false;
    document.getElementById("cancel-btn").disabled = false;
    this.style.display = "none"; // Hide Edit button
});

document.getElementById("cancel-btn").addEventListener("click", function () {
    location.reload();
});


const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }

function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
     if (input.disabled) {
        icon.style.display = 'none'; // Hide the icon if input is disabled
        return;
    }
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.textContent = isPassword ? "🙈" : "👁️";
}

document.getElementById("newPassword").addEventListener("input", validatePassword);
document.getElementById("confirmNewPassword").addEventListener("input", checkPasswordMatch);

function validatePassword() {
    const pwd = document.getElementById('newPassword').value;
    const warning = document.getElementById('passwordWarning');
    const regex = /^(?=(?:.*\d){2,})(?=(?:.*[^A-Za-z0-9]){2,}).{8,20}$/;
    warning.style.display = regex.test(pwd) ? 'none' : 'block';
}

function checkPasswordMatch() {
    const pwd = document.getElementById('newPassword').value;
    const confirmPwd = document.getElementById('confirmNewPassword').value;
    const mismatch = document.getElementById('passwordMismatch');
    mismatch.style.display = (pwd && confirmPwd && pwd !== confirmPwd) ? 'block' : 'none';
}
</script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/editprofile_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
