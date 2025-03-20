<?php
require '../config/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $conn->real_escape_string($_POST['fullName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'student'; // Default role

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email='$email'";
    $result = $conn->query($check_email);
 
    if ($result->num_rows > 0) {
        header("Location: ../public/register.php?error=Email already registered");
        exit();
    }

    $sql = "INSERT INTO users (fullName, email, password, role) VALUES ('$fullName', '$email', '$password', '$role')";
    
    if ($conn->query($sql)) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['fullName'] = $fullName;
        $_SESSION['role'] = $role;
        
        exit();
    } else {
        header("Location: ../public/register.php?error=Registration failed");
        exit();
    }
}

$conn->close();
?>
