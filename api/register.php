<?php
require '../config/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim inputs
    $lastName = trim($_POST['studentLastName']);
    $firstName = trim($_POST['studentFirstName']);
    $middleName = trim($_POST['studentMiddleName']);
    $studentNumber = trim($_POST['studentNumber']);
    $course = trim($_POST['studentCourse']);
    $year = (int) $_POST['studentYear'];
    $section = trim($_POST['studentSection']);
    $email = trim($_POST['studentEmail']);
    $password = $_POST['studentPassword'];
    $confirmPassword = $_POST['studentConfirmPassword'];

    // Validate passwords match
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../public/register.php");
        exit();
    }

    // Validate password format (2 digits, 2 special chars, 8–20 chars)
    if (!preg_match('/^(?=(?:.*\d){2,})(?=(?:.*[!@#$%^&*()_+\[\]{};:\'",.<>?`~\\|]).{2,}).{8,20}$/', $password)) {
        $_SESSION['error'] = "Password format invalid. Must include at least 2 digits, 2 special characters, and be 8–20 characters long.";
        header("Location: ../public/register.php");
        exit();
    }

    // Check if student number or email already exists
    $check_sql = "SELECT id FROM newusers WHERE studentNumber = ? OR email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $studentNumber, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email or student number already exists.";
        $stmt->close();
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $insert_sql = "INSERT INTO newusers 
        (firstName, middleName, lastName, studentNumber, course, year, section, email, password, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')";

    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param(
        "sssssisss",
        $firstName,
        $middleName,
        $lastName,
        $studentNumber,
        $course,
        $year,
        $section,
        $email,
        $hashedPassword
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful!";
        $stmt->close();
        header("Location: ../public/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Something went wrong during registration.";
        $stmt->close();
        header("Location: ../public/register.php");
        exit();
    }

} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../public/register.php");
    exit();
}
?>
