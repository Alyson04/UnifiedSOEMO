<?php 
require '../api/auth.php';
?>

<?php $title = "Manage Events"; $style = "admindashboard_styles.css"; include '../includes/header.php'; include '../includes/navbar.php'; ?>

<main>
<section class="dashboard">
    <div class="card"> <a href = "manage_users.php"> <p>Manage Users</p> </a> </div>
    <div class="card"> <a href = "manage_organizations.php"> <p>Organizations</p> </a> </div>
    <div class="card"> <a href = "manage_events.php"> <p>Events</p> </a> </div>
    <div class="card"> <a href = "settings.php"> <p>Settings</p> </a> </div>
</section>
</main>

<?php include '../includes/footer.php'; ?>