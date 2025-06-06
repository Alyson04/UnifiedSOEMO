<?php 
session_start();
$title = "Login"; 
$style = "login_styles.css"; 
include '../includes/header.php'; 
?>

<!-- Show session messages -->
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
            <input type="email" class="input-field" name="email" placeholder="Enter your email" required>
            
            <label>Password:</label>
            <input type="password" class="input-field" name="password" placeholder="Enter your password" required>
            
            <button type="submit" class="btn">Log In</button>
            
            <p class="forgot-password" id="openForgotModal"><a href="#">Forgot Password?</a></p>
        </form>

        <p class="terms">
            By using this service, you understand and agree to the PUP Online Services 
            <a href="https://www.pup.edu.ph/terms/">Terms of Use</a> and 
            <a href="https://www.pup.edu.ph/privacy/">Privacy Statement</a>.
        </p>
    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeForgotModal">&times;</span>
    <h2>Forgot Password</h2>
    <p>Enter your email to receive a reset link:</p>
    <!-- This form submits normally and reloads page -->
    <form action="../forgotpassword/sendresetlink.php" method="POST">
      <input type="email" id="forgotEmail" name="email" placeholder="Your email" required>
      <button type="submit">Send Reset Link</button>
    </form>
  </div>
</div>

<style>
/* Your existing styles for session alerts, modal, buttons etc... */

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

/* Modal container */
.modal {
  display: none; 
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.5); /* dark background */
}

/* Modal content box */
.modal-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #fff;
  padding: 20px;
  border-radius: 6px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

/* Close button */
.close {
  float: right;
  font-size: 22px;
  cursor: pointer;
}

/* Basic form elements */
input[type="email"] {
  width: 100%;
  margin-top: 10px;
  box-sizing: border-box;
}

button {
  margin-top: 12px;
  padding: 10px;
  width: 100%;
  cursor: pointer;
  background: linear-gradient(135deg, #36577d, #2A4365);
  color: white;
  border: none;
  border-radius: 4px;
}
</style>

<script>
  // Session alert fade out
  const alertBox = document.querySelector('.session-alert');
  if (alertBox) {
    setTimeout(() => {
      alertBox.style.transition = 'opacity 0.5s ease';
      alertBox.style.opacity = '0';
      setTimeout(() => alertBox.remove(), 500);
    }, 4000);
  }

  // Open forgot password modal
  document.getElementById('openForgotModal').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('forgotModal').style.display = 'block';
  });

  // Close forgot password modal
  document.getElementById('closeForgotModal').addEventListener('click', function() {
    document.getElementById('forgotModal').style.display = 'none';
  });

  // Close modal if clicking outside modal content
  window.addEventListener('click', function(e) {
    const modal = document.getElementById('forgotModal');
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
</script>
