<?php
include '../functions/db_conn.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $is_approved = $_POST['is_approved'];

    $sql = "INSERT INTO users (fullName, username, password, Role, is_approved) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullName, $username, $password, $role, $is_approved);

    if ($stmt->execute()) {
        echo "<script>console.log('success')</script>";
        $_SESSION['success'] = "Record added successfully";
        header("refresh:3, admin_dashboard.php");
    } else {
        $_SESSION['error'] = "Error adding record: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADD A RECORD</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
            
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <!-- <li><a href="#" class="active">Create Admin Account</a></li> -->
                <li><a href="admin_dashboard.php" class="active">Manage Users</a></li>
                <li><a href="#">Organizations</a></li>
                <li><a href="#">Events</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="../functions/logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
        
            <div id="addRecordSection">
            <!-- Add Record Form -->
            <div class="card mb-4">
                <div class="card-header">
                <a href="admin_dashboard.php" style="font-size:16px; padding: 10px 15px; background-color: #f44336; color: white; text-decoration: none; border-radius: 5px;">
                Back
                </a>
                    <h3>Add a Record</h3>
                </div>
                <div class="card-body">
                    <form action="" method="POST">

                        <label for="fullName">Fullname:</label>
                        <input type="text" id="fulName" name="fullName" required><br>

                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required><br>

                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required><br>

                        <input type="hidden" id="role" name="role" value="admin" required><br>

                        <input type="hidden" id="is_approved" name="is_approved" value="approved" required><br>

                        <button type="submit" class="btn btn-success">Add Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>