<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPmailer/PHPMailer.php';
require '../PHPmailer/SMTP.php';
require '../PHPmailer/Exception.php';

$config = require '../config/secret_config.php';
require '../config/db_conn.php'; // $conn = new mysqli(...);

// Get submitted email
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    $_SESSION['error'] = "Invalid email address.";
    header("Location: ../public/login.php");
    exit;
}

// Check if email exists in database
$stmt = $conn->prepare("SELECT * FROM newusers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Email not found.";
    header("Location: ../public/login.php");
    exit;
}

// Generate token
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", time() + 3600); // 1 hour

// Store token in password_resets table
$insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$insert->bind_param("sss", $email, $token, $expires_at);
$insert->execute();

// Prepare reset link
$resetLink = "http://localhost/unifiedSOEMO/forgotpassword/resetpass.php?token=$token";

// Send the email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $config['email']['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['email']['username'];
    $mail->Password   = $config['email']['password'];
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom($config['email']['from'], $config['email']['from_name']);
    $mail->addAddress($email); // Send to the user

    $mail->isHTML(true);
    $mail->Subject = 'Reset Your Password';
    $mail->Body    = "Click the link below to reset your password:<br><a href=\"$resetLink\">$resetLink</a>";

    $mail->send();
    $_SESSION['success'] = "A password reset link has been sent to your email.";
    header("Location: ../public/login.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
    header("Location: ../public/login.php");
    exit;
}
