<?php
session_start();
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org admins

require '../config/db_conn.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Not logged in, redirect to login page or show error
    header('Location: ../login.php');
    exit;
}

// Prepare to update fields
$errors = [];
$success = false;

// Get POST data safely
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Don't trim password to keep spaces if any

// Validate inputs (basic example, expand as needed)
if (empty($fullName)) {
    $errors[] = "Full Name cannot be empty.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

// Password is optional (only update if not empty)
$updatePassword = false;
if (!empty($password)) {
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } else {
        $updatePassword = true;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    }
}

// Handle logo upload if file uploaded
$logoPath = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['logo']['tmp_name'];
    $fileName = basename($_FILES['logo']['name']);
    $fileSize = $_FILES['logo']['size'];
    $fileType = $_FILES['logo']['type'];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = "Only JPG, PNG, and GIF files are allowed for the logo.";
    } else {
        $uploadDir = '../assets/uploads_organizations/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // To avoid overwriting, prefix with user id and timestamp
        $newFileName = 'org_' . $user_id . '_' . time() . '_' . $fileName;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $logoPath = $newFileName; // store filename, not full path
        } else {
            $errors[] = "There was an error uploading the logo.";
        }
    }
}

if (empty($errors)) {
    // Update users table (fullName, email, password if any)
    $params = [];
    $types = '';
    $sqlParts = [];

    $sqlParts[] = 'fullName = ?';
    $params[] = $fullName;
    $types .= 's';

    $sqlParts[] = 'email = ?';
    $params[] = $email;
    $types .= 's';

    if ($updatePassword) {
        $sqlParts[] = 'password = ?';
        $params[] = $hashedPassword;
        $types .= 's';
    }

    $sql = "UPDATE users SET " . implode(', ', $sqlParts) . " WHERE ID = ?";
    $params[] = $user_id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // If logo uploaded, update organizations table
    if ($logoPath !== null) {
        // First find org_id for this user
        $sqlOrg = "SELECT org_id FROM users WHERE ID = ?";
        $stmtOrg = $conn->prepare($sqlOrg);
        $stmtOrg->bind_param('i', $user_id);
        $stmtOrg->execute();
        $resultOrg = $stmtOrg->get_result();
        if ($resultOrg->num_rows > 0) {
            $orgRow = $resultOrg->fetch_assoc();
            $org_id = $orgRow['org_id'];

            // Update image_path in organizations table
            $sqlUpdateLogo = "UPDATE organizations SET image_path = ? WHERE id = ?";
            $stmtUpdateLogo = $conn->prepare($sqlUpdateLogo);
            $stmtUpdateLogo->bind_param('si', $logoPath, $org_id);
            $stmtUpdateLogo->execute();
            $stmtUpdateLogo->close();
        }
        $stmtOrg->close();
    }

    $stmt->close();
    $success = true;
}

$conn->close();

// Redirect back with success or error messages
if ($success) {
    $_SESSION['success_message'] = "Profile updated successfully.";
} else {
    $_SESSION['error_message'] = implode(' ', $errors);
}

header('Location: ../admin_org/new_settings.php'); // or your settings page path
exit;
