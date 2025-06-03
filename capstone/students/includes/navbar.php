<?php
// Include necessary files and start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default values
$display_name = 'Guest';
$role_label = '';
$profile_img = '../assets/uploads_pfp/profile.png';

// If user is logged in
if (isset($_SESSION['user_id'])) {
    include '../config/db_conn.php';
    $user_id = $_SESSION['user_id'];

    // Fetch user info
    $stmt = $conn->prepare("SELECT fullName, role, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $display_name = ucwords(strtolower($row['fullName']));
        $role = $row['role'];
        $role_label = $role === 'student' ? 'Student' : '';

        // Check for profile picture
        if (!empty($row['profile_picture'])) {
            $uploaded_path = "../assets/uploads_pfp/" . $row['profile_picture'];
            if (file_exists($uploaded_path)) {
                $profile_img = $uploaded_path;
            }
        }
    }
    $stmt->close();
}
?>

<!-- Mobile Menu Button -->
<button class="hamburger-menu" type="button" aria-label="Menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>

<!-- Mobile Sidebar -->
<div class="sidebar">
    <div class="sidebar-profile">
        <img src="<?= htmlspecialchars($profile_img); ?>" alt="Profile Picture">
        <div class="sidebar-profile-info">
            <strong><?= htmlspecialchars($display_name); ?></strong>
            <span><?= htmlspecialchars($role_label); ?></span>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php">HOME</a></li>
        <li><a href="organizations.php">ORGANIZATIONS</a></li>
        <li><a href="new-post.php">POSTS</a></li>
        <li><a href="events.php">EVENTS</a></li>
        <li><a href="about_us.php">ABOUT US</a></li>
        <li><a href="edit_profile.php">EDIT PROFILE</a></li>
        <li><a href="../api/logout.php">LOGOUT</a></li>
    </ul>
</div>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">
        <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo">
    </div>
    <ul class="nav-list">
        <li><a href="dashboard.php">HOME</a></li>
        <li><a href="organizations.php">ORGANIZATIONS</a></li>
        <li><a href="new-post.php">POSTS</a></li>
        <li><a href="events.php">EVENTS</a></li>
        <li><a href="about_us.php">ABOUT US</a></li>
    </ul>
    <div class="search-profile">
        <div class="profile" onclick="toggleProfileDropdown()">
            <img src="<?= htmlspecialchars($profile_img); ?>" alt="Profile Picture">
            <div class="profile-info">
                <span><?= htmlspecialchars($display_name); ?></span>
                <p><?= htmlspecialchars($role_label); ?></p>
            </div>
            <div class="dropdown-tray" id="profileDropdown">
                <a href="edit_profile.php">Edit Profile</a>
                <a href="../api/logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- Include mobile styles and script -->
<link rel="stylesheet" href="../assets/stylesheets/student_mobile.css">
<script src="../assets/js/student_mobile.js" defer></script> 