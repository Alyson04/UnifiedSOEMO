<?php 
session_start();
$title = "Login"; $style = "login_styles.css"; include '../includes/header.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
    <div class="login-container">
        <div class="login-form">
            <h2 class="title">LOG IN</h2>
            <p class="signup">Doesn’t have an account? <a href="register.php">Sign up</a></p>
            
            <form action="../api/login.php" method="POST">
                <label>Email Address:</label>
                <input type="email" class="input-field" name="email" placeholder="Enter your email"required>
                
                <label>Password:</label>
                <input type="password" class="input-field" name="password" placeholder="Enter your password"required>
                
                <button type="submit" class="btn">Log In</button>
                
                <p class="forgot-password"><a href="../forgotpassword/forgotpass.php">Forgot Password?</a></p>
            </form>
            <button class="google-btn" onclick="window.location.href='../api/google_login.php'">
            <img src="../assets/pictures/google_icons.png" alt="Google Login" class="google-icon">
            Login with Google
        </button>
           <p class="terms">
               By using this service, you understand and agree to the PUP Online Services 
            <a href="https://www.pup.edu.ph/terms/">Terms of Use</a> and 
             <a href="https://www.pup.edu.ph/privacy/">Privacy Statement</a>.
            </p>

        </div>
    </div>

    <style>
.session-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50; /* Green by default for success */
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    max-width: 80%;
    text-align: center;
    animation: fadeInSlideDown 0.4s ease-in-out;
}

.session-alert.error {
    background-color: #f44336; /* Red for error */
}

@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}
    </style>

    <script>
        const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
    </script>