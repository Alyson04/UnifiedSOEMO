<?php
require '../config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "UPDATE users SET password='$password' WHERE id='$user_id'";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Profile updated successfully"]);
        header("Location: ../students/dashboard.php");
    } else {
        echo json_encode(["error" => "Failed to update profile"]);
    }
}

$conn->close();
?>