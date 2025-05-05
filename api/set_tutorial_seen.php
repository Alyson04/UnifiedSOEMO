<?php
require '../config/db_conn.php';
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;

if ($student_id) {
    $stmt = $conn->prepare("UPDATE users SET tutorial_seen = 1 WHERE ID = ?");
    $stmt->bind_param("i", $student_id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
