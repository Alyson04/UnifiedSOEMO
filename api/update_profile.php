<?php
require '../config/db_conn.php';
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;

if (!$student_id) {
    header("Location: ../public/login.php");
    exit();
}

$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

if (empty($fullName)) {
    $errors[] = "Full name is required.";
    $_SESSION['error'] = "Full name is required!";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
    $_SESSION['error'] = "Invalid email address!";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// File upload handling
$profile_picture_name = '';
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['profile_pic']['tmp_name'];
    $file_name = basename($_FILES['profile_pic']['name']);
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_file_name = 'pfp_' . uniqid() . '.' . $file_ext;
    $destination = '../assets/uploads_pfp/' . $new_file_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        $profile_picture_name = $new_file_name;
    } else {
        $errors[] = "Failed to upload profile picture.";
        $_SESSION['error'] = "Failed to upload profile picture!";
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Construct SQL
if (!empty($password) && !empty($profile_picture_name)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET fullName = ?, email = ?, password = ?, profile_picture = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fullName, $email, $hashedPassword, $profile_picture_name, $student_id);

} elseif (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET fullName = ?, email = ?, password = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullName, $email, $hashedPassword, $student_id);

} elseif (!empty($profile_picture_name)) {
    $sql = "UPDATE users SET fullName = ?, email = ?, profile_picture = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullName, $email, $profile_picture_name, $student_id);

} else {
    $sql = "UPDATE users SET fullName = ?, email = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $fullName, $email, $student_id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully.";
    header("Location: ../students/edit_profile.php");
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
}

$stmt->close();
$conn->close();
