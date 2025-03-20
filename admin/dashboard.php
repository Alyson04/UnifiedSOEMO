<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins
?>

<?php $title = "Admin Dashboard"; include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>
<p>EXAMPLE LOGIN</p>

<?php include '../includes/footer.php'; ?>
