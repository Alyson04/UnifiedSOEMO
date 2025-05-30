<?php
require '../api/auth.php';
checkUserRole('org_admin');

require '../config/db_conn.php';
header('Content-Type: application/json');

$org_id = $_SESSION['org_id'] ?? null;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Total count
$sql_count = "SELECT COUNT(*) as total FROM events WHERE org_id = ?";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Fetch events
$sql = "SELECT id, title, event_date, created_at FROM events WHERE org_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $org_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['events' => $events, 'total' => $total, 'perPage' => $limit]);
