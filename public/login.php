<?php $title = "Login"; include '../includes/header.php'; ?>

<form action="../api/login.php" method="POST">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>



<?php include '../includes/footer.php'; ?>