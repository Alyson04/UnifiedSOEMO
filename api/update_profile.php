<?php
require '../config/db_conn.php';
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;

if (!$student_id) {
    header("Location: ../public/login.php");
    exit();
}

// Sanitize and validate inputs
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Validate required fields
if (empty($fullName)) {
    $errors[] = "Full name is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if (!empty($errors)) {
    // Optionally store errors in session or display directly
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Prepare SQL
if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET fullName = ?, email = ?, password = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullName, $email, $hashedPassword, $student_id);
} else {
    $sql = "UPDATE users SET fullName = ?, email = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $fullName, $email, $student_id);
}

if ($stmt->execute()) {
    // Optionally update session values here
    $_SESSION['success_message'] = "Profile updated successfully.";
    header("Location: ../students/edit_profile.php");
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
}

$stmt->close();
$conn->close();
