<?php
// Set default values
$display_name = 'Guest';
$role_label = '';
$profile_img = '../assets/pictures/profile.png';

// Set based on session
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        $display_name = ucwords(strtolower($admin_name ?? 'Admin')); // From DB
        $role_label = 'Admin';
    } else if($_SESSION['role'] === 'student') {
        $display_name = ucwords(strtolower($student_name ?? 'Student')); // From DB
        $role_label = 'Student';
    } else {
        $display_name = ucwords(strtolower($admin_name ?? 'Org Admin')); // From DB
        $role_label = 'Org Admin';
    }
}
?>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="logo"> 
        <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo"> 
    </div>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
            <ul class="nav-list">
                <li><a href="dashboard.php">HOME</a></li>
                <li><a href="organizations.php">ORGANIZATIONS</a></li>
                <li><a href="events.php">EVENTS</a></li>
                <li><a href="about_us.php">ABOUT US</a></li>
            </ul>
        <?php endif; ?>
        
    <div class="search-profile">
        <input type="text" placeholder="Search">
        <img src="../assets/pictures/bell.png" alt="Bell Icon"> <!-- Notification Icon -->
        <div class="profile" onclick="toggleProfileDropdown()">
            <img src="<?= htmlspecialchars($profile_img); ?>" alt="Profile">
            <div class="profile-text">
                <span><?= htmlspecialchars($display_name); ?></span>
                <p><?= $role_label; ?></p>
            </div>
            <div class="dropdown-tray" id="profileDropdown">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                <a href="../students/edit_profile.php">Edit Profile</a>
                <?php endif; ?>
                <a href="../api/logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
