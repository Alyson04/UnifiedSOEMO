<?php
require "db_conn.php";

// $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "INSERT INTO users (fullName, username, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullName, $username, $password);

    if ($stmt->execute()) {
        echo "Record added successfully";
    } else {
        echo "Error adding record: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
</head>
<body>
    <h1>Sign Up</h1>

    <form method="POST" action="">
        
        <label for="fullName">Full Name:</label>
        <input type="text" name="fullName" required><br><br>
        
        <label for="username">Username:</label>
        <input type="text" name="username" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required><br><br>

        <input type="hidden" name="role" value="student">

        <button type="submit">Sign Up</button>
    </form>

    <div>
        <h4>
        Have an Account? <a href="login.php">Login</a>
        </h4>
    </div>

</body>
</html>