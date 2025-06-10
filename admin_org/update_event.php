<?php
require '../api/auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Extract fields
$id = $data['id'] ?? null;
$title = $data['title'] ?? null;
$description = $data['description'] ?? null;
$event_date = $data['event_date'] ?? null;

// Get the org admin's organization ID
$user_id = $_SESSION['user_id'] ?? null;

// Validate input
if (!$id || !$title || !$event_date || !$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Verify event belongs to org admin's organization
$verify_sql = "
    SELECT e.id, e.org_id 
    FROM events e
    INNER JOIN neworganizations o ON e.org_id = o.id
    WHERE e.id = ? AND o.user_id = ?
";

$stmt = $conn->prepare($verify_sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this event']);
    $stmt->close();
    exit;
}

$event_data = $result->fetch_assoc();
$stmt->close();

// Prepare update SQL
$sql = "UPDATE events SET title = ?, description = ?, event_date = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('sssi', $title, $description, $event_date, $id);

// Execute
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?> 