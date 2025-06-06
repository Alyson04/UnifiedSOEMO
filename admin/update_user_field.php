<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Validate POST input
$id = $_POST['id'] ?? null;
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

$allowed_fields = ['firstName', 'middleName', 'lastName', 'studentNumber', 'course', 'year', 'section', 'email', 'role', 'status', 'graduated'];
$currentYear = (int)date('Y');

if (!$id || !in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Validate specific fields
if ($field === 'role') {
    $allowed_roles = ['admin', 'student', 'orgAdmin'];
    if (!in_array($value, $allowed_roles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role value.']);
        exit;
    }
}

if ($field === 'status') {
    $allowed_statuses = ['active', 'disabled', 'renewal'];
    if (!in_array($value, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit;
    }
}

if ($field === 'graduated') {
    $allowed_graduated = ['yes', 'no', 'N/A'];
    if (!in_array($value, $allowed_graduated)) {
        echo json_encode(['success' => false, 'message' => 'Invalid graduated value.']);
        exit;
    }
}

if ($field === 'studentNumber') {
    if (!preg_match('/^\d{4}/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Invalid student number format.']);
        exit;
    }
    $yearPrefix = (int)substr($value, 0, 4);
    if ($yearPrefix > $currentYear) {
        echo json_encode(['success' => false, 'message' => 'Student number year cannot be in the future.']);
        exit;
    }
    if (($currentYear - $yearPrefix) >= 4) {
        echo json_encode(['success' => false, 'message' => 'Cannot update: student already graduated based on student number.']);
        exit;
    }
}

if ($field === 'email') {
    $stmt = $conn->prepare("SELECT role FROM newusers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $role = $user['role'];
        if ($role === 'admin' && !preg_match('/@pup\.edu\.ph$/', $value)) {
            echo json_encode(['success' => false, 'message' => 'Admin email must end with @pup.edu.ph']);
            exit;
        }
        if (($role === 'student' || $role === 'orgAdmin') && !preg_match('/@iskolarngbayan\.pup\.edu\.ph$/', $value)) {
            echo json_encode(['success' => false, 'message' => 'Email must end with @iskolarngbayan.pup.edu.ph for students/orgAdmins']);
            exit;
        }
    }
}

// Proceed to update
$stmt = $conn->prepare("UPDATE newusers SET $field = ? WHERE id = ?");
$stmt->bind_param("si", $value, $id);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
