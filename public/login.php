<?php $title = "Login"; $style = "login_styles.css"; include '../includes/header.php'; ?>

    <div class="login-container">
        <div class="login-form">
            <h2 class="title">LOG IN</h2>
            <p class="signup">Doesn’t have an account? <a href="register.php">Sign up</a></p>
            
            <form action="../api/login.php" method="POST">
                <label>Email Address:</label>
                <input type="email" class="input-field" name="email" placeholder="Enter your email"required>
                
                <label>Password:</label>
                <input type="password" class="input-field" name="password" placeholder="Enter your password"required>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember Me</label>
                </div>
                
                <button type="submit" class="btn">Log In</button>
                
                <p class="forgot-password"><a href="#">Forgot Password?</a></p>
            </form>
            
            <p class="terms">By using this service, you understood and agree to the PUP Online Services <a href="#">Terms of Use</a> and <a href="#">Privacy Statement</a></p>
        </div>
    </div>


<?php include '../includes/footer.php'; ?>