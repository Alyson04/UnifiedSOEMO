<?php
require '../config/db_conn.php';
session_start(); // Start session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, fullName, password, role, org_id FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullName'] = $user['fullName'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else if ($user['role' === 'student']) {
                header("Location: ../students/dashboard.php");
            } else {
                $_SESSION['org_id'] = $user['org_id'];
                header("Location: ../admin_org/dashboard.php");
            }
            exit();
        } else {
            header("Location: ../public/login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: ../public/login.php?error=User not found");
        exit();
    }
}

$conn->close();
?>
