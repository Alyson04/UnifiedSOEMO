<?php
session_start();
$title = "Forgot Password";
include '../includes/header.php';
?>

<h2>Forgot Password</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<form action="sendresetlink.php" method="POST">
    <label>Enter your email address:</label>
    <input type="email" name="email" required placeholder="Your email">
    <button type="submit">Send Reset Link</button>
</form>
