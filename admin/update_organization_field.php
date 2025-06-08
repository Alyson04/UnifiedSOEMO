<?php
require '../config/db_conn.php';

// Get POST data
$id = $_POST['id'] ?? null;
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Define allowed fields
$allowed_fields = ['name', 'description', 'mission', 'vision', 'status', 'objective', 'how_to_join', 'requirements', 'image_path', 'user_id'];
if (!$id || !in_array($field, $allowed_fields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field or ID']);
    exit;
}

$value = htmlspecialchars($value, ENT_QUOTES);

// Handle status updates with extra fields
if ($field === 'status') {
    $sql = "UPDATE neworganizations SET status = ?, last_updated = NOW()";

    // Add renewal and expiry date only if status becomes active
    if ($value === 'active') {
        $sql .= ", renewal_date = DATE_ADD(NOW(), INTERVAL 11 MONTH), expiry_date = DATE_ADD(NOW(), INTERVAL 12 MONTH)";
    }

    $sql .= " WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL preparation failed']);
        exit;
    }

    $stmt->bind_param("si", $value, $id);
} else {
    // Normal single-field update
    $sql = "UPDATE neworganizations SET $field = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL preparation failed']);
        exit;
    }

    $stmt->bind_param("si", $value, $id);
}

// Execute and respond
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Organization field updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update field']);
}

$stmt->close();
$conn->close();
?>
