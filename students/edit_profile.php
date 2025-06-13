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

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-lines">&#9776;</div>
</div>

<!-- Mobile Profile -->
<div class="mobile-profile" onclick="toggleMobileProfileDropdown()">
    <img src="<?= $profile_img ?>" alt="Profile Picture">
    <div class="mobile-dropdown-tray" id="mobileProfileDropdown">
        <a href="edit_profile.php">Edit Profile</a>
        <a href="../api/logout.php">Logout</a>
    </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="new-post.php">Posts</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="about_us.php">About Us</a></li>
    </ul>
</div>

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

/* Center password fields */
.input-icon {
    width: 80%;
    margin: 0 auto;
}

.input-icon input[type="password"],
.input-icon input[type="text"] {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

label {
    display: block;
    text-align: center;
    margin-bottom: 5px;
}

small {
    display: block;
    text-align: center;
    margin-top: 5px;
}

/* Mobile Menu Styles */
.hamburger {
    display: none;
    position: fixed;
    top: 15px;
    left: 30px;
    z-index: 1002;
    cursor: pointer;
    background: #1e3a4f;
    width: 35px;
    height: 35px;
    border-radius: 6px;
    justify-content: center;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    padding: 0;
}

.hamburger-lines {
    color: #fff;
    font-size: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

.mobile-profile {
    display: none;
    position: absolute;
    top: 15px;
    right: 30px;
    z-index: 1001;
    cursor: pointer;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1001;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
}

.mobile-profile img {
    width: 35px;
    height: 35px;
    border-radius: 6px;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.mobile-dropdown-tray {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    width: 150px;
    z-index: 1001;
}

.mobile-dropdown-tray.active {
    display: block;
}

.mobile-dropdown-tray a {
    display: block;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.mobile-dropdown-tray a:hover {
    background: #f5f5f5;
}

.mobile-dropdown-tray a:last-child {
    border-top: 1px solid #eee;
    color: #E74C3C;
}

.mobile-sidebar {
    display: none;
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: #1e3a4f;
    z-index: 1002;
    transition: all 0.3s ease-in-out;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.mobile-sidebar.active {
    left: 0;
}

.sidebar-list {
    list-style: none;
    padding: 20px 0;
    margin: 0;
}

.sidebar-list li {
    padding: 0;
    margin: 5px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sidebar-list li a {
    color: rgb(255, 255, 255);
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    display: block;
    padding: 12px 0px;
    border-radius: 8px;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
}

.sidebar-list li:hover {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar-list li a:hover {
    color: rgba(173, 211, 204, 1);
    transform: translateX(5px);
}

.sidebar-list li:last-child {
    margin-top: 5px;
    border-radius: 8px;
}

/* Mobile Responsive Styles */
@media only screen and (max-width: 600px) {
    .hamburger {
        display: flex;
    }
    
    .mobile-sidebar {
        display: block;
    }

    .mobile-profile {
        display: block;
    }
    
    .navbar {
        display: none !important;
        visibility: hidden;
        opacity: 0;
    }
    
    nav {
        display: none !important;
    }
    
    .nav-list {
        display: none !important;
    }

    .logo {
        display: none !important;
    }
    
    .logo img {
        display: none !important;
    }

    .profile {
        display: none !important;
    }

    .top-bar {
        display: none !important;
    }
    
    .main-layout {
        margin-top: 0%x;
    }

    body.sidebar-active {
        overflow: hidden;
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
    const regex = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\[\]{};\'",.<>?`~\\|])(?=.*\d).{8,20}$/;
    warning.style.display = regex.test(pwd) ? 'none' : 'block';
}

function checkPasswordMatch() {
    const pwd = document.getElementById('newPassword').value;
    const confirmPwd = document.getElementById('confirmNewPassword').value;
    const mismatch = document.getElementById('passwordMismatch');
    mismatch.style.display = (pwd && confirmPwd && pwd !== confirmPwd) ? 'block' : 'none';
}

// Mobile menu functionality
function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    body.classList.toggle('sidebar-active');
    
    // Close profile dropdown when opening sidebar
    const profileDropdown = document.getElementById('mobileProfileDropdown');
    if (profileDropdown.classList.contains('active')) {
        profileDropdown.classList.remove('active');
    }
}

// Mobile profile dropdown functionality
function toggleMobileProfileDropdown(event) {
    // Only allow toggling if sidebar is not active
    const sidebar = document.getElementById('mobileSidebar');
    if (!sidebar.classList.contains('active')) {
        const dropdown = document.getElementById('mobileProfileDropdown');
        dropdown.classList.toggle('active');
        event.stopPropagation();
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('mobileSidebar');
    const hamburger = document.querySelector('.hamburger');
    const profileDropdown = document.getElementById('mobileProfileDropdown');
    const mobileProfile = document.querySelector('.mobile-profile');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Close sidebar if clicking overlay
    if (event.target === overlay) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-active');
    }
    
    // Close profile dropdown if clicking outside (only if sidebar is not active)
    if (!sidebar.classList.contains('active') && 
        !mobileProfile.contains(event.target) && 
        profileDropdown.classList.contains('active')) {
        profileDropdown.classList.remove('active');
    }
});
</script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/editprofile_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
