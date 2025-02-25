<?php
// login.php
session_start();

require 'db_conn.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Temporarily using plain-text password comparison
        if ($user && $password === $user['password']) {
            $_SESSION['user'] = [
                'ID' => $user['ID'],
                'username' => $user['username'],
                'role' => $user['role']
            ];

            if ($user['role'] === 'admin') {
                header("Location: ../admin side/admin_dashboard.php");
            } elseif ($user['role'] === 'student') {
                header("Location: ../student side/student_dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Login</h1>

    <?php if ($error): ?>
        <p style="color: red;"> <?= htmlspecialchars($error) ?> </p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <div>
        <h4>
        No Account Yet? <a href="signup.php">Sign Up</a>
        </h4>
    </div>
    
</body>
</html>
