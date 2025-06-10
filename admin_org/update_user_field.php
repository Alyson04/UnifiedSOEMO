<?php 
require '../api/auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Get org admin's organization ID
$org_id = $_SESSION['org_id'] ?? null;
if (!$org_id) {
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id) {
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
}

if (!$org_id) {
    echo json_encode(['success' => false, 'message' => 'Organization not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = $_POST['id'] ?? '';
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Validate that the user belongs to the org admin's organization
$check_sql = "SELECT u.id FROM newusers u 
              INNER JOIN organization_members om ON u.id = om.user_id 
              WHERE u.id = ? AND om.organization_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $id, $org_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found in your organization']);
    exit;
}
$check_stmt->close();

// List of fields that can be updated
$allowed_fields = [
    'firstName' => 'newusers',
    'middleName' => 'newusers',
    'lastName' => 'newusers',
    'studentNumber' => 'newusers',
    'course' => 'newusers',
    'year' => 'newusers',
    'section' => 'newusers',
    'email' => 'newusers',
    'applicationStatus' => 'join_org',
    'status' => 'newusers'
];

if (!array_key_exists($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field']);
    exit;
}

// Validate email if it's being updated
if ($field === 'email') {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    if (!preg_match("/@iskolarngbayan\.pup\.edu\.ph$/", $value)) {
        echo json_encode(['success' => false, 'message' => 'Email must be a valid PUP email address (@iskolarngbayan.pup.edu.ph)']);
        exit;
    }
}

// Validate student number if it's being updated
if ($field === 'studentNumber') {
    if (!preg_match('/^\d{4}-\d{5}-MN-0$/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Invalid student number format. Use YYYY-NNNNN-MN-0']);
        exit;
    }
}

// Validate status values
if ($field === 'status') {
    $valid_statuses = ['active', 'renewal', 'disabled'];
    if (!in_array($value, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }
}

// Validate application status values
if ($field === 'applicationStatus') {
    $valid_app_statuses = ['pending', 'approved', 'rejected'];
    if (!in_array($value, $valid_app_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid application status value']);
        exit;
    }
}

try {
    $conn->begin_transaction();

    if ($allowed_fields[$field] === 'newusers') {
        $sql = "UPDATE newusers SET $field = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $id);
    } else if ($allowed_fields[$field] === 'join_org') {
        $sql = "UPDATE join_org SET status = ? WHERE student_id = ? AND org_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $value, $id, $org_id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to update $field");
    }

    // If updating application status to 'approved', ensure organization_members entry exists
    if ($field === 'applicationStatus' && $value === 'approved') {
        $check_member_sql = "SELECT 1 FROM organization_members WHERE user_id = ? AND organization_id = ?";
        $check_member_stmt = $conn->prepare($check_member_sql);
        $check_member_stmt->bind_param("ii", $id, $org_id);
        $check_member_stmt->execute();
        
        if ($check_member_stmt->get_result()->num_rows === 0) {
            $insert_member_sql = "INSERT INTO organization_members (user_id, organization_id, joined_at) VALUES (?, ?, NOW())";
            $insert_member_stmt = $conn->prepare($insert_member_sql);
            $insert_member_stmt->bind_param("ii", $id, $org_id);
            if (!$insert_member_stmt->execute()) {
                throw new Exception("Failed to add user to organization members");
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Update successful']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 