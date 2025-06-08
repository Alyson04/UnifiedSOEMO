<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Validate POST input
$id = $_POST['id'] ?? null;
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Ensure proper data type for the fields
$allowed_fields = ['firstName', 'middleName', 'lastName', 'studentNumber', 'course', 'year', 'section', 'email', 'role', 'status', 'graduated', 'applicationStatus'];
$currentYear = (int)date('Y');

// Check if the necessary data is provided
if (!$id || !in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Initialize success as false
$success = false;

// Validate specific fields
if ($field === 'role') {
    $allowed_roles = ['admin', 'student', 'orgAdmin'];
    if (!in_array($value, $allowed_roles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role value.']);
        exit;
    }
}

if ($field === 'status') {
    $allowed_statuses = ['active', 'disabled', 'renewal', 'approved', 'rejected'];
    if (!in_array($value, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit;
    }
}

if ($field === 'graduated') {
    $allowed_graduated = ['yes', 'no', 'N/A'];
    $value = trim($value);  // Ensure there are no extra spaces
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

// Updating application status in the join_org table
if ($field === 'applicationStatus') {
    // Update the application status in join_org table
    $stmt = $conn->prepare("UPDATE join_org SET status = ? WHERE student_id = ?");
    $stmt->bind_param("si", $value, $id); // $value is the new status, $id is the student_id
    $stmt->execute();
    $stmt->close();

    // If the application status is 'approved', perform specific actions
    if ($value === 'approved') {
        // Get the org_id from join_org table
        $stmt = $conn->prepare("SELECT org_id FROM join_org WHERE student_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $org_id = $result->fetch_assoc()['org_id'];

            // Check if the user is already a member of the organization
            $stmt = $conn->prepare("SELECT 1 FROM organization_members WHERE user_id = ? AND organization_id = ?");
            $stmt->bind_param("ii", $id, $org_id);
            $stmt->execute();
            $existing_member = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // If not already a member, insert the user into the organization_members table
            if (!$existing_member) {
                $stmt = $conn->prepare("INSERT INTO organization_members (user_id, organization_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $id, $org_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Send an approval message to the notifications table
        $message = "Your application has been approved. Welcome to the organization!";
        
        // Insert the approval message into the notifications table
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, ?, NOW())");
        $is_read = 0; // Set the notification as unread
        $stmt->bind_param("isi", $id, $message, $is_read);
        $stmt->execute();
        $stmt->close();
    }

    // If the application status is 'rejected', insert a generic rejection message
    if ($value === 'rejected') {
        $message = "Your application has been rejected.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, ?, NOW())");
        $is_read = 0; // Set the notification as unread
        $stmt->bind_param("isi", $id, $message, $is_read);
        $stmt->execute();
        $stmt->close();
    }

    // Set success to true after successful application status update
    $success = true;
}

// Proceed to update the user info for other fields (but NOT applicationStatus in newusers)
if ($field !== 'applicationStatus') {
    $stmt = $conn->prepare("UPDATE newusers SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $id);
    $success = $stmt->execute();
    $stmt->close();
}

$conn->close();

echo json_encode(['success' => $success]);
?>
