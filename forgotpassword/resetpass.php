<?php
session_start();
include '../config/db_conn.php';

$token = $_GET['token'] ?? '';

// Validate token
$sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Invalid or expired token.';
    header('Location: forgotpass.php');
    exit;
}

$resetData = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update user password
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashedPassword, $resetData['email']);
    $stmt->execute();

    // Delete token after successful reset
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param("s", $resetData['email']);
    $stmt->execute();

    $_SESSION['success'] = 'Your password has been reset. You can now log in.';
    header('Location: ../public/login.php');
    exit;
}
?>

<h2>Reset Your Password</h2>
<form method="POST">
    <label>New Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Reset Password</button>
</form>
