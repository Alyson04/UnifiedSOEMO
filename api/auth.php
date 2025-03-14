<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php?error=Please log in first");
    exit();
}

// Optional: Restrict access based on user role
function checkUserRole($required_role) {
    if ($_SESSION['role'] !== $required_role) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../students/dashboard.php");
        }
        exit();
    }
}
?>
