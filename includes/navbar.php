<?php
// Set default values
$display_name = 'Guest';
$role_label = '';
$profile_img = '../assets/pictures/profile.png';

// Set based on session
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        $display_name = ucfirst(strtolower($admin_name ?? 'Admin')); // From DB
        $role_label = 'Admin';
    } else {
        $display_name = ucfirst(strtolower($student_name ?? 'Student')); // From DB
        $role_label = 'Student';
    }
}
?>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="logo"> 
        <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo"> 
    </div>

    <div class="search-profile">
        <input type="text" placeholder="Search">
        <img src="../assets/pictures/bell.png" alt="Bell Icon"> <!-- Notification Icon -->
        <div class="profile">
            <img src="<?= htmlspecialchars($profile_img); ?>" alt="Profile">
            <div class="profile-text">
                <span><?= htmlspecialchars($display_name); ?></span>
                <p><?= $role_label; ?></p>
            </div>
        </div>
    </div>
</nav>
