<?php
session_start();
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow org admins

require '../config/db_conn.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Not logged in, redirect to login page or show error
    header('Location: ../public/login.php');
    exit;
}

// Prepare to update fields
$errors = [];
$success = false;

// Get POST data safely
$firstName = trim($_POST['firstName'] ?? '');
$middleName = trim($_POST['middleName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Don't trim password to keep spaces if any
$confirmPassword = $_POST['confirmPassword'] ?? ''; // NEW LINE

// Validate inputs (basic example, expand as needed)
if (empty($firstName) || empty($lastName)) {
    $_SESSION['error'] = "First Name and Last Name cannot be empty!";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // $errors[] = "Please enter a valid email address.";
    $_SESSION['error'] = "Enter valid email address!";
}

// Password is optional (only update if not empty)
$updatePassword = false;
if (!empty($password)) {
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters!";
    } elseif ($password !== $confirmPassword) { // NEW CONDITION
        $_SESSION['error'] = "Passwords do not match!";
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
        $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed for the logo.";
        header('Location: ../admin_org/new_settings.php');
        exit;
    }

    $uploadDir = '../assets/uploads_organizations/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename with org_id
    $newFileName = 'org_' . $user_id . '_' . time() . '_' . $fileName;
    $destPath = $uploadDir . $newFileName;

    // Delete old logo if exists
    $sql_old = "SELECT image_path FROM neworganizations WHERE user_id = ?";
    $stmt_old = $conn->prepare($sql_old);
    $stmt_old->bind_param("i", $user_id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    if ($row_old = $result_old->fetch_assoc()) {
        $old_logo = $row_old['image_path'];
        if ($old_logo && file_exists($uploadDir . $old_logo)) {
            unlink($uploadDir . $old_logo);
        }
    }
    $stmt_old->close();

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $logoPath = $newFileName;
    } else {
        $_SESSION['error'] = "There was an error uploading the logo.";
        header('Location: ../admin_org/new_settings.php');
        exit;
    }
}

if (empty($errors)) {
    // Update users table
    $params = [];
    $types = '';
    $sqlParts = [];

    $sqlParts[] = 'firstName = ?';
    $params[] = $firstName;
    $types .= 's';

    $sqlParts[] = 'middleName = ?';
    $params[] = $middleName;
    $types .= 's';

    $sqlParts[] = 'lastName = ?';
    $params[] = $lastName;
    $types .= 's';

    $sqlParts[] = 'email = ?';
    $params[] = $email;
    $types .= 's';

    if ($updatePassword) {
        $sqlParts[] = 'password = ?';
        $params[] = $hashedPassword;
        $types .= 's';
    }

    $sql = "UPDATE newusers SET " . implode(', ', $sqlParts) . " WHERE ID = ?";
    $params[] = $user_id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    // If logo uploaded, update organizations table
    if ($logoPath !== null) {
        $sqlUpdateLogo = "UPDATE neworganizations SET image_path = ? WHERE user_id = ?";
        $stmtUpdateLogo = $conn->prepare($sqlUpdateLogo);
        $stmtUpdateLogo->bind_param('si', $logoPath, $user_id);
        $stmtUpdateLogo->execute();
        $stmtUpdateLogo->close();
    }

    $_SESSION['success'] = "Profile updated successfully.";
} else {
    $_SESSION['error'] = implode(' ', $errors);
}

header('Location: ../admin_org/new_settings.php');
exit;
