<?php
session_start();
require '../config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../admin/create_new_user.php');
    exit;
}

function sanitize($conn, $input) {
    return htmlspecialchars(trim($conn->real_escape_string($input)));
}

function normalizeValue($value) {
    return trim($value) === '' ? 'N/A' : $value;
}

$role = $_POST['role'] ?? '';
$firstName = normalizeValue(sanitize($conn, $_POST['firstName'] ?? ''));
$middleName = normalizeValue(sanitize($conn, $_POST['middleName'] ?? ''));
$lastName = normalizeValue(sanitize($conn, $_POST['lastName'] ?? ''));
$email = normalizeValue(sanitize($conn, $_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Always attempt to grab student-related data
$studentNumber = normalizeValue(sanitize($conn, $_POST['studentNumber'] ?? ''));
$course = normalizeValue(sanitize($conn, $_POST['course'] ?? ''));
$year = normalizeValue(sanitize($conn, $_POST['year'] ?? ''));
$section = normalizeValue(sanitize($conn, $_POST['section'] ?? ''));

$missingFields = [];
if (!$firstName) $missingFields[] = 'First Name';
if (!$middleName) $missingFields[] = 'Middle Name';
if (!$lastName) $missingFields[] = 'Last Name';
if (!$email) $missingFields[] = 'Email';
if (!$password) $missingFields[] = 'Password';
if (!$confirmPassword) $missingFields[] = 'Confirm Password';
if (!$role) $missingFields[] = 'Role';

// Additional role-based requirements
if ($role === 'student') {
    if (!$studentNumber || $studentNumber === 'N/A') $missingFields[] = 'Student Number';
    if (!$course || $course === 'N/A') $missingFields[] = 'Course';
    if (!$year || $year === 'N/A') $missingFields[] = 'Year';
    if (!$section || $section === 'N/A') $missingFields[] = 'Section';
} elseif ($role === 'orgAdmin') {
    if (!$studentNumber) $missingFields[] = 'Student Number';
    if (!$course) $missingFields[] = 'Course';
    if (!$year) $missingFields[] = 'Year';
    if (!$section) $missingFields[] = 'Section';
} else {
    $studentNumber = $course = $section = 'N/A';
    $year = null;
}

if (!empty($missingFields)) {
    $_SESSION['error'] = 'Missing fields: ' . implode(', ', $missingFields);
    header('Location: ../admin/create_new_user.php');
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: ../admin/create_new_user.php');
    exit;
}
if ($role === 'admin' && !preg_match("/@pup\.edu\.ph$/", $email)) {
    $_SESSION['error'] = 'Admin email must end with @pup.edu.ph';
    header('Location: ../admin/create_new_user.php');
    exit;
}
if (in_array($role, ['student', 'orgAdmin']) && !preg_match("/@iskolarngbayan\.pup\.edu\.ph$/", $email)) {
    $_SESSION['error'] = 'School email must end with @iskolarngbayan.pup.edu.ph';
    header('Location: ../admin/create_new_user.php');
    exit;
}

// Password validation
if ($password !== $confirmPassword) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: ../admin/create_new_user.php');
    exit;
}
$lengthValid = strlen($password) >= 8 && strlen($password) <= 20;
$hasNumbers = preg_match_all('/\d/', $password) >= 2;
$hasSpecials = preg_match_all('/[^A-Za-z0-9]/', $password) >= 2;
if (!$lengthValid || !$hasNumbers || !$hasSpecials) {
     $_SESSION['error'] = 'Password must be 8–20 characters long and include at least 2 numbers and 2 special characters.';
    header('Location: ../admin/create_new_user.php');
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Validate student number logic
if ($studentNumber !== 'N/A') {
    if (!preg_match('/^\d{4}-\d{5}-MN-0$/', $studentNumber)) {
        $_SESSION['error'] = 'Invalid student number format. Use YYYY-NNNNN-MN-0';
        header('Location: ../admin/create_new_user.php');
        exit;
    }

    // Check uniqueness
    $checkStmt = $conn->prepare("SELECT id FROM newusers WHERE studentNumber = ?");
    $checkStmt->bind_param("s", $studentNumber);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = 'Student Number already exists.';
        header('Location: ../admin/create_new_user.php');
        exit;
    }
    $checkStmt->close();
}

$graduated = 'no';
if ($role === 'admin') {
    $graduated = 'N/A';
} elseif ($studentNumber !== 'N/A') {
    $entryYear = (int)substr($studentNumber, 0, 4);
    $currentYear = (int)date('Y');

    if ($entryYear > $currentYear) {
        $_SESSION['error'] = 'Invalid student number: entry year cannot be in the future.';
        header('Location: ../admin/create_new_user.php');
        exit;
    }

    if (($currentYear - $entryYear) >= 4) {
        $_SESSION['error'] = 'This user cannot register anymore because they have already graduated.';
        header('Location: ../admin/create_new_user.php');
        exit;
    }
}

// Insert into DB
$sql = "INSERT INTO newusers (firstName, middleName, lastName, email, password, role, studentNumber, course, year, section, graduated)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssss",
    $firstName,
    $middleName,
    $lastName,
    $email,
    $hashedPassword,
    $role,
    $studentNumber,
    $course,
    $year,
    $section,
    $graduated
);

if ($stmt->execute()) {
    $_SESSION['success'] = 'User successfully created.';
    header('Location: ../admin/new-manage_users.php');
} else {
    $_SESSION['error'] = 'Database error: ' . $stmt->error;
    header('Location: ../admin/create_new_user.php');
}

$stmt->close();
$conn->close();
?>
