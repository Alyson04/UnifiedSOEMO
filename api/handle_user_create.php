<?php
require 'auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Get org admin's organization ID
$user_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id && $user_id) {
    $sql_org = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_id = $result->fetch_assoc()['id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    // Convert any case-insensitive match of 'n/a' to uppercase 'N/A'
    if (strtolower($middleName) === 'n/a') {
        $middleName = 'N/A';
    }
    $lastName = $_POST['lastName'] ?? '';
    $studentNumber = $_POST['studentNumber'] ?? '';
    $course = $_POST['course'] ?? '';
    $year = $_POST['year'] ?? '';
    $section = $_POST['section'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($studentNumber) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    // Validate password
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    if (!preg_match("/[A-Z]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one uppercase letter.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    if (!preg_match("/[a-z]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one lowercase letter.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    if (!preg_match("/[0-9]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one number.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    if (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one special character.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    // Validate PUP email domain
    if (!preg_match("/@iskolarngbayan\.pup\.edu\.ph$/", $email)) {
        $_SESSION['error'] = "Email must be a valid PUP email address (@iskolarngbayan.pup.edu.ph).";
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if user already exists
        $check_sql = "SELECT id FROM newusers WHERE studentNumber = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $studentNumber, $email);
        $check_stmt->execute();
        $exists = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();

        if ($exists) {
            throw new Exception("A user with this student number or email already exists.");
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Set role explicitly to 'student'
        $role = 'student';
        $status = 'active';

        // Create new user
        $insert_sql = "INSERT INTO newusers (firstName, middleName, lastName, studentNumber, course, year, section, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssssssssss", $firstName, $middleName, $lastName, $studentNumber, $course, $year, $section, $email, $hashedPassword, $role, $status);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create user.");
        }
        
        $new_user_id = $conn->insert_id;
        $insert_stmt->close();

        // Add user to organization_members
        $member_sql = "INSERT INTO organization_members (user_id, organization_id, joined_at) VALUES (?, ?, NOW())";
        $member_stmt = $conn->prepare($member_sql);
        $member_stmt->bind_param("ii", $new_user_id, $org_id);
        
        if (!$member_stmt->execute()) {
            throw new Exception("Failed to add user to organization.");
        }
        
        $member_stmt->close();

        // Add entry to join_org table with accepted status
        $join_sql = "INSERT INTO join_org (student_id, org_id, status, application_date) VALUES (?, ?, 'accepted', NOW())";
        $join_stmt = $conn->prepare($join_sql);
        $join_stmt->bind_param("ii", $new_user_id, $org_id);
        
        if (!$join_stmt->execute()) {
            throw new Exception("Failed to create join record.");
        }
        
        $join_stmt->close();

        $conn->commit();
        $_SESSION['success'] = "New member created successfully.";
        header("Location: ../admin_org/new-manage_users.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../admin_org/create_new_user.php");
        exit;
    }
} else {
    header("Location: ../admin_org/create_new_user.php");
    exit;
}
?>
