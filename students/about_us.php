<?php 
require '../api/auth.php';
?>

<?php $title = "About Us"; $style = "about_us_styles.css"; include '../includes/header.php'; ?>

<header class="header">
        <div class="logo">
            <img src="../assets/pictures/logo.png" alt="PUP Logo">
        </div>
        <?php include '../includes/navbar.php'; ?>
        <div class="search-profile">
            <div class="search-container">
                <input type="text" class="search" placeholder="Search">
                <img src="IMG/search-icon.png" class="search-icon" alt="Search">
            </div>
            <span class="notification"><i class="fa-solid fa-bell"></i></span>
            <div class="profile">
                <img src="../assets/pictures/moni roy.png" alt="Profile">
                <div class="profile-info">
                    <span class="profile-name">Moni Roy</span>
                    <span class="profile-role">Student</span>
                </div>
            </div>
        </div>
</header>

<?php include '../includes/footer.php'; ?>