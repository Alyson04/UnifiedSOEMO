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

    // Store form data in session for error cases
    $_SESSION['form_data'] = [
        'studentLastName' => $lastName,
        'studentFirstName' => $firstName,
        'studentMiddleName' => $middleName,
        'studentNumber' => $studentNumber,
        'studentCourse' => $course,
        'studentYear' => $year,
        'studentSection' => $section,
        'studentEmail' => $email
    ];

    // Validate passwords match
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../public/register.php");
        exit();
    }

    // Validate password format
    if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\[\]{};\'",.<>?`~\\|])(?=.*\d).{8,20}$/', $password)) {
        $_SESSION['error'] = "Password must be 8-20 characters long and include at least one uppercase letter, one special character, and one number.";
        header("Location: ../public/register.php");
        exit();
    }

    if (!preg_match("/@iskolarngbayan\.pup\.edu\.ph$/", $email)) {
        $_SESSION['error'] = 'School email must end with @iskolarngbayan.pup.edu.ph';
        header('Location: ../public/register.php');
        exit;
    }

    // Check if student number or email already exists
    $check_sql = "SELECT id FROM newusers WHERE studentNumber = ? OR email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $studentNumber, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "A user with this student number or email already exists.";
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->close();

    // If we get here, clear the form data from session as registration will proceed
    unset($_SESSION['form_data']);

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user
    $sql = "INSERT INTO newusers (lastName, firstName, middleName, studentNumber, course, year, section, email, password, role, graduated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 'no')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $lastName, $firstName, $middleName, $studentNumber, $course, $year, $section, $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! You can now login.";
        header("Location: ../public/login.php");
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../public/register.php");
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../public/register.php");
    exit();
}
?>
