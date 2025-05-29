<?php
require 'auth.php';
checkUserRole('admin'); // Only admins allowed

require '../config/db_conn.php';

session_start();
$admin_id = $_SESSION['user_id'] ?? null;

if (!$admin_id) {
    die('Unauthorized access.');
}

// Get POST data safely
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate basic fields (You can add more validation if needed)
if (empty($fullName) || empty($email)) {
    header("Location: ../admin/new_settings.php?error=missing_fields");
    exit;
}

// Optional password hash
$password_sql = "";
$bind_password = false;
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $password_sql = ", password = ?";
    $bind_password = true;
}

// Handle profile picture upload
$pfp_filename = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/uploads_pfp/';
    $tmp_name = $_FILES['logo']['tmp_name'];
    $original_name = basename($_FILES['logo']['name']);
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // Generate unique filename
    $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
    $target_file = $upload_dir . $new_filename;

    // Only allow image types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed_types)) {
        header("Location: ../admin/new_settings.php?error=invalid_filetype");
        exit;
    }

    if (!move_uploaded_file($tmp_name, $target_file)) {
        header("Location: ../admin/new_settings.php?error=upload_failed");
        exit;
    }

    $pfp_filename = $new_filename;
}

// Build SQL query dynamically
$sql = "UPDATE users SET fullName = ?, email = ?";
$params = [$fullName, $email];
$types = "ss";

if ($bind_password) {
    $sql .= $password_sql;
    $params[] = $hashed_password;
    $types .= "s";
}

if ($pfp_filename) {
    $sql .= ", profile_picture = ?";
    $params[] = $pfp_filename;
    $types .= "s";
}

$sql .= " WHERE ID = ?";
$params[] = $admin_id;
$types .= "i";

// Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    header("Location: ../admin/new_settings.php?success=1");
} else {
    header("Location: ../admin/new_settings.php?error=db_error");
}

$stmt->close();
$conn->close();
