<?php $title = "Register"; include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="register-container">
    <h2>Register</h2>

    <!-- Display error messages -->
    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <form action="../api/register.php" method="POST">
        <label>Full Name:</label>
        <input type="text" name="fullName" placeholder="Enter your full name" required>

        <label>Email:</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        <label>Password:</label>
        <input type="password" name="password" placeholder="Enter your password" required>

        <button type="submit">Register</button>
    </form>

    <hr>
    <button class="google-btn" onclick="window.location.href='../api/google_login.php'">Register with Google</button>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<?php include '../includes/footer.php'; ?>
