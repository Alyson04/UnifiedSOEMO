<?php
require '../config/db_conn.php';
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;

if (!$student_id) {
    header("Location: ../public/login.php");
    exit();
}

$oldPassword = $_POST['oldPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

if (empty($oldPassword) || empty($newPassword) || empty($confirmNewPassword)) {
    $_SESSION['error'] = "All password fields are required!";
    header("Location: ../students/edit_profile.php");
    exit();
}

// Fetch current password hash
$stmt = $conn->prepare("SELECT password FROM newusers WHERE ID = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../students/edit_profile.php");
    exit();
}

$row = $result->fetch_assoc();
$currentHash = $row['password'];

// Check if old password is correct
if (!password_verify($oldPassword, $currentHash)) {
    $_SESSION['error'] = "Old password is incorrect!";
    header("Location: ../students/edit_profile.php");
    exit();
}

// Check if new password matches confirmation
if ($newPassword !== $confirmNewPassword) {
    $_SESSION['error'] = "New passwords do not match!";
    header("Location: ../students/edit_profile.php");
    exit();
}

// Check password complexity
$lengthValid = strlen($newPassword) >= 8 && strlen($newPassword) <= 20;
$hasNumbers = preg_match_all('/\d/', $newPassword) >= 2;
$hasSpecials = preg_match_all('/[^A-Za-z0-9]/', $newPassword) >= 2;

if (!$lengthValid || !$hasNumbers || !$hasSpecials) {
    $_SESSION['error'] = "Password must be 8–20 characters, include at least 2 numbers and 2 special characters.";
    header("Location: ../students/edit_profile.php");
    exit();
}

// Update password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $conn->prepare("UPDATE newusers SET password = ? WHERE ID = ?");
$updateStmt->bind_param("si", $hashedPassword, $student_id);

if ($updateStmt->execute()) {
    $_SESSION['success'] = "Password updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update password.";
}

$updateStmt->close();
$conn->close();

header("Location: ../students/edit_profile.php");
exit();
