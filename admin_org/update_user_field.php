<?php 
require '../api/auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Get org admin's organization ID
$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id && $admin_id) {
    $sql_org = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_id = $result->fetch_assoc()['id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt->close();
}

if (!$org_id) {
    echo json_encode(['success' => false, 'message' => 'Organization not found']);
    exit;
}

// Validate POST input
$id = $_POST['id'] ?? null;
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Ensure proper data type for the fields (only allow updating basic info)
$allowed_fields = ['firstName', 'middleName', 'lastName', 'studentNumber', 'course', 'year', 'section', 'email'];
$currentYear = (int)date('Y');

// Check if the necessary data is provided
if (!$id || !in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Verify that the user belongs to the org admin's organization
$verify_sql = "
    SELECT u.id 
    FROM newusers u
    INNER JOIN organization_members om ON u.id = om.user_id
    WHERE u.id = ? AND om.organization_id = ?
";
$stmt = $conn->prepare($verify_sql);
$stmt->bind_param("ii", $id, $org_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found in your organization']);
    $stmt->close();
    exit;
}
$stmt->close();

// Validate specific fields
if ($field === 'studentNumber') {
    if (!preg_match('/^\d{4}/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Invalid student number format']);
        exit;
    }
    $yearPrefix = (int)substr($value, 0, 4);
    if ($yearPrefix > $currentYear) {
        echo json_encode(['success' => false, 'message' => 'Student number year cannot be in the future']);
        exit;
    }
}

if ($field === 'email') {
    if (!preg_match('/@iskolarngbayan\.pup\.edu\.ph$/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Email must end with @iskolarngbayan.pup.edu.ph']);
        exit;
    }

    // Check if email is already in use by another user
    $stmt = $conn->prepare("SELECT id FROM newusers WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $value, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use']);
        $stmt->close();
        exit;
    }
    $stmt->close();
}

if ($field === 'year') {
    $allowed_years = ['1', '2', '3'];
    if (!in_array($value, $allowed_years)) {
        echo json_encode(['success' => false, 'message' => 'Invalid year value']);
        exit;
    }
}

if ($field === 'course') {
    $allowed_courses = ['DCvET', 'DCET', 'DEET', 'DECET', 'DIT', 'DMET', 'DOMT', 'DRET'];
    if (!in_array($value, $allowed_courses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid course']);
        exit;
    }
}

// Update the user information
$stmt = $conn->prepare("UPDATE newusers SET $field = ? WHERE id = ?");
$stmt->bind_param("si", $value, $id);
$success = $stmt->execute();
$stmt->close();

$conn->close();

echo json_encode(['success' => $success]);
?> 