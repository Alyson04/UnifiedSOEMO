<?php
session_start();
require '../config/db_conn.php';
require '../api/auth.php';

checkUserRole('admin'); // Restrict to admin

$admin_id = $_SESSION['user_id'] ?? null;
if (!$admin_id) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../admin/new_settings.php");
    exit;
}

// Sanitize inputs
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validate required fields
if (!$firstName || !$lastName || !$email) {
    $_SESSION['error'] = "First name, last name, and email are required.";
    header("Location: ../admin/new_settings.php");
    exit;
}

// Validate password match if updating password
if (!empty($password) || !empty($confirmPassword)) {
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../admin/new_settings.php");
        exit;
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
} else {
    $hashedPassword = null; // Don't update password
}

// Handle logo upload
$logoFileName = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../assets/uploads_pfp/";
    $fileTmp = $_FILES['logo']['tmp_name'];
    $fileName = basename($_FILES['logo']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowedExt)) {
        $_SESSION['error'] = "Invalid file type for logo.";
        header("Location: ../admin/new_settings.php");
        exit;
    }

    $newFileName = uniqid("logo_", true) . "." . $fileExt;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $destination)) {
        $_SESSION['error'] = "Failed to upload image.";
        header("Location: ../admin/new_settings.php");
        exit;
    }

    $logoFileName = $newFileName;
}

// Prepare update query
$fields = "firstName=?, lastName=?, middleName=?, email=?";
$params = [$firstName, $lastName, $middleName, $email];

if ($hashedPassword) {
    $fields .= ", password=?";
    $params[] = $hashedPassword;
}
if ($logoFileName) {
    $fields .= ", profile_picture=?";
    $params[] = $logoFileName;
}

$params[] = $admin_id;

$sql = "UPDATE newusers SET $fields WHERE ID=?";
$stmt = $conn->prepare($sql);
$types = str_repeat("s", count($params) - 1) . "i";
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully.";
} else {
    $_SESSION['error'] = "Update failed. Please try again.";
}

$stmt->close();
$conn->close();

header("Location: ../admin/new_settings.php");
exit;
