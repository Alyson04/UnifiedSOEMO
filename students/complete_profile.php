<?php 
require '../api/auth.php'; 
?>
<?php $title = "Complete Information"; include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="register-container">
    <h2>Complete Your Profile</h2>
    <form action="update_profile.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

        <label>Password:</label>
        <input type="password" name="password" placeholder="Enter a new password" required>

        <button type="submit">Save</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>