<?php
include '../config/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $is_approved = $_POST['is_approved'];

    // Hash the password before storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullName, email, password, Role, is_approved) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullName, $email, $hashedPassword, $role, $is_approved);

    if ($stmt->execute()) {
        echo "<script>console.log('success')</script>";
        $_SESSION['success'] = "Record added successfully";
        echo "created successfully";
        header("refresh:3, ../admin/dashboard.php");
    } else {
        $_SESSION['error'] = "Error adding record: " . $stmt->error;
    }

    $stmt->close();
}
?>