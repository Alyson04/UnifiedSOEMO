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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset Password</title>
<style>
  /* Full page container with flex to center content */
  body, html {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f5f5;
  }
  .container {
    height: 100%;
    display: flex;
    justify-content: center; /* horizontal center */
    align-items: center;     /* vertical center */
  }
  form {
    background: white;
    padding: 30px 40px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    width: 320px;
    box-sizing: border-box;
  }
  h2 {
    text-align: center;
    margin-bottom: 20px;
  }
  label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
  }
  input[type="password"] {
    width: 100%;
    padding: 8px 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
  }
  button {
    width: 100%;
    background: linear-gradient(135deg, #36577d, #2A4365);
    color: white;
    padding: 10px 0;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
  }
  button:hover {
    background-color: #0056b3;
  }
</style>
</head>
<body>

<div class="container">
  <form method="POST">
      <h2>Reset Your Password</h2>
      <label for="password">New Password:</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Reset Password</button>
  </form>
</div>

</body>
</html>
