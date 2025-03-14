<?php 
require '../api/auth.php';
checkUserRole('students'); // Only allow students
?>

<?php $title = "Student Dashboard"; include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<p>EXAMPLE LOGIN</p>

<?php include '../includes/footer.php'; ?>
