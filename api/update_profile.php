<?php
require '../config/db_conn.php';
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;

if (!$student_id) {
    header("Location: ../public/login.php");
    exit();
}

// Get password fields
$old_password = $_POST['oldPassword'] ?? '';
$new_password = $_POST['newPassword'] ?? '';
$confirm_password = $_POST['confirmNewPassword'] ?? '';

$errors = [];

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

// Password validation
$password_updated = false;
if (!empty($old_password) || !empty($new_password) || !empty($confirm_password)) {
    // All fields must be filled
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = "All password fields are required.";
        $_SESSION['error'] = "All password fields are required!";
    } else {
        // Fetch user's current password
        $stmt = $conn->prepare("SELECT password FROM newusers WHERE ID = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->bind_result($current_hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($old_password, $current_hashed_password)) {
            $errors[] = "Old password is incorrect.";
            $_SESSION['error'] = "Old password is incorrect!";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New password and confirm password do not match.";
            $_SESSION['error'] = "New password and confirm password do not match!";
        } else {
            $password_updated = true;
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
}

// Check if there are any errors
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Construct SQL
if ($password_updated && !empty($profile_picture_name)) {
    $sql = "UPDATE newusers SET password = ?, profile_picture = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $hashedPassword, $profile_picture_name, $student_id);

} elseif ($password_updated) {
    $sql = "UPDATE newusers SET password = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashedPassword, $student_id);

} elseif (!empty($profile_picture_name)) {
    $sql = "UPDATE newusers SET profile_picture = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $profile_picture_name, $student_id);

} else {
    $_SESSION['error'] = "Nothing to update!";
    header("Location: ../students/edit_profile.php");
    exit();
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
