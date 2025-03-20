<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP Student Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2 class="title">LOG IN</h2>
            <p class="signup">Doesn’t have an account? <a href="#">Sign up</a></p>
            
            <form>
                <label>Email Address:</label>
                <input type="email" class="input-field" placeholder="Enter your email">
                
                <label>Password:</label>
                <input type="password" class="input-field" placeholder="Enter your password">
                
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
</body>
</html>
