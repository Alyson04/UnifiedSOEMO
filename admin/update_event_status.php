<?php
require '../api/auth.php';
checkUserRole('admin');
require '../config/db_conn.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Extract fields
$id = $data['id'] ?? null;
$title = $data['title'] ?? null;
$description = $data['description'] ?? null;
$org_id = $data['org_id'] ?? null;
$event_date = $data['event_date'] ?? null;
$status = $data['status'] ?? null;

// Allowed statuses
$allowedStatuses = ['under review', 'accepted', 'rejected'];

// Validate input
if (!$id || !$title || !$org_id || !$event_date || !in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Prepare SQL
$sql = "UPDATE events SET title = ?, description = ?, org_id = ?, event_date = ?, status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssissi', $title, $description, $org_id, $event_date, $status, $id);

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
