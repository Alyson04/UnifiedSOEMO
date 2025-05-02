<?php
require '../config/db_conn.php';
session_start();

// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'org_admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$fullName = $_SESSION['fullName'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Check if email already exists (other than the current user's email)
    $check_email = "SELECT id FROM users WHERE email = '$email' AND id != '$admin_id'";
    $result = $conn->query($check_email);
 
    if ($result->num_rows > 0) {
        header("Location: ../public/settings.php?error=Email already in use");
        exit();
    }

    // Update user details (email and password if provided)
    if ($password) {
        $sql = "UPDATE users SET email = '$email', password = '$password' WHERE id = '$admin_id'";
    } else {
        $sql = "UPDATE users SET email = '$email' WHERE id = '$admin_id'";
    }

    if ($conn->query($sql)) {
        $_SESSION['email'] = $email;
    
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/settings.php?success=Settings updated successfully");
        } elseif ($_SESSION['role'] === 'org_admin') {
            header("Location: ../admin_org/settings.php?success=Settings updated successfully");
        }
    
        exit();
    } else {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/settings.php?error=Failed to update settings");
        } elseif ($_SESSION['role'] === 'org_admin') {
            header("Location: ../admin_org/settings.php?error=Failed to update settings");
        }
    
        exit();
    }
    
}

$conn->close();
?>
