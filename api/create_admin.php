<?php
include '../config/db_conn.php';
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