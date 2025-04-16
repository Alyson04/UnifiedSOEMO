<?php $title = "Register"; $style = "register_styles.css"; include '../includes/header.php'; ?>


<!-- Left Side - Signup Form -->
<div class="left-container">
    <div class="signup-form">
        <h1 class="title">SIGN UP</h1>

        <!-- Display error messages -->
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <p class="subtitle">Already have an account? <a href="login.php">Log in</a></p>

        <form action="../api/register.php" method="POST">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullName" class="input-field" placeholder="Enter your full name" required>

            <label for="email">School Email:</label>
            <input type="email" id="email" name="email" class="input-field" placeholder="you@example.com" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="input-field" placeholder="Enter 6 characters or more" required>

            <button type="submit" class="btn">Sign Up</button>
        </form>

        <hr>
        <button class="google-btn" onclick="window.location.href='../api/google_login.php'">Register with Google</button>

        <p class="terms">
            By using this service, you understand and agree to the PUP Online Services 
            <a href="terms.html">Terms of Use</a> and <a href="privacy.html">Privacy Statement</a>.
        </p>
    </div>
</div>

<!-- Right Side - Background Image -->
<div class="right-container"></div>

<?php include '../includes/footer.php'; ?>
