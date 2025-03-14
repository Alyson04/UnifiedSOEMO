<?php
require '../config/google_config.php';
require '../config/db_conn.php';
session_start();

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $oauth = new Google\Service\Oauth2($client);
    $google_user = $oauth->userinfo->get();

    $email = $google_user->email;
    $fullName = $google_user->name;

    $sql = "SELECT id, role FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullName'] = $fullName;
        $_SESSION['role'] = $user['role'];

        header("Location: ../students/dashboard.php");
        exit();
    } else {
        $sql = "INSERT INTO users (fullName, email, role) VALUES ('$fullName', '$email', 'student')";
        if ($conn->query($sql)) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['fullName'] = $fullName;
            $_SESSION['role'] = 'student';

            header("Location: ../students/complete_profile.php");
            exit();
        }
    }
}

$conn->close();
?>
