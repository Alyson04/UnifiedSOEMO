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

    // Debugging: Check if Google provided an email
    if (!isset($email) || empty($email)) {
        die("Google OAuth did not return an email.");
    }

    // Check if the user exists
    $sql = "SELECT id, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, log them in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullName'] = $fullName;
        $_SESSION['role'] = $user['role'];

        // Debugging: Check session before redirecting
        if (!isset($_SESSION['user_id'])) {
            die("Session user_id is not set. Check login process.");
        }

        header("Location: ../students/dashboard.php");
        exit();
    } else {
        // Insert new user
        $sql = "INSERT INTO users (fullName, email, role) VALUES (?, ?, 'student')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fullName, $email);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['fullName'] = $fullName;
            $_SESSION['role'] = 'student';

            // Debugging: Ensure session is set before redirecting
            if (!isset($_SESSION['user_id'])) {
                die("Session user_id is not set after inserting user.");
            }

            header("Location: ../students/complete_profile.php");
            exit();
        } else {
            die("Error inserting user: " . $conn->error);
        }
    }
}

$conn->close();
?>
