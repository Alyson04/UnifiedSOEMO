<?php
require '../config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method');
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
    exit('Missing fields: ' . implode(', ', $missingFields));
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Invalid email format');
}
if ($role === 'admin' && !preg_match("/@pup\.edu\.ph$/", $email)) {
    exit('Admin email must end with @pup.edu.ph');
}
if (in_array($role, ['student', 'orgAdmin']) && !preg_match("/@iskolarngbayan\.pup\.edu\.ph$/", $email)) {
    exit('School email must end with @iskolarngbayan.pup.edu.ph');
}

// Password validation
if ($password !== $confirmPassword) {
    exit('Passwords do not match');
}
$lengthValid = strlen($password) >= 8 && strlen($password) <= 20;
$hasNumbers = preg_match_all('/\d/', $password) >= 2;
$hasSpecials = preg_match_all('/[^A-Za-z0-9]/', $password) >= 2;
if (!$lengthValid || !$hasNumbers || !$hasSpecials) {
    exit('Password does not meet complexity requirements');
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Validate student number logic
if ($studentNumber !== 'N/A') {
    if (!preg_match('/^\d{4}-\d{5}-MN-0$/', $studentNumber)) {
        exit('Invalid student number format. Use YYYY-NNNNN-MN-0');
    }

    // Check uniqueness
    $checkStmt = $conn->prepare("SELECT id FROM newusers WHERE studentNumber = ?");
    $checkStmt->bind_param("s", $studentNumber);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        exit('Student Number already exists');
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
        exit('Invalid student number: entry year cannot be in the future.');
    }

    if (($currentYear - $entryYear) >= 4) {
        exit('This user cannot register anymore because they have already graduated.');
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
    echo 'User successfully created';
} else {
    echo 'Database error: ' . $stmt->error;
}

$stmt->close();
$conn->close();
?>
