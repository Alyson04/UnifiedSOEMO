<?php
require '../config/db_conn.php';
session_start(); // Start session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare statement to fetch user by email
    $stmt = $conn->prepare("SELECT id, fullName, password, role, org_id, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if user status is deleted
        if ($user['status'] === 'deleted') {
            $_SESSION['error'] = "Account Deactivated";
            header("Location: ../public/login.php?error=Your account has been deactivated.");
            exit();
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullName'] = $user['fullName'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['org_id'] = $user['org_id'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                $_SESSION['success'] = "Log In Success";
                header("Location: ../admin/dashboard.php");
            } else if ($user['role'] === 'student') {
                $_SESSION['success'] = "Log In Success";
                header("Location: ../students/dashboard.php");
            } else {
                $_SESSION['success'] = "Log In Success";
                header("Location: ../admin_org/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid Password";
            header("Location: ../public/login.php?error=Invalid password");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid Email";
        header("Location: ../public/login.php?error=User not found");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
