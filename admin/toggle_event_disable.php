<?php
require '../api/auth.php';
checkUserRole('admin');
require '../config/db_conn.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$disable = isset($data['disable']) ? (bool)$data['disable'] : null;

if (!$id || !is_bool($disable)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$sql = "UPDATE events SET is_disabled = ? WHERE id = ?";
$is_disabled_int = $disable ? 1 : 0;
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $is_disabled_int, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
