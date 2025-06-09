<?php
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Pagination settings
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Get total count of events
$sqlCount = "SELECT COUNT(*) AS total FROM events";
$countResult = $conn->query($sqlCount);
$total = 0;
if ($countResult) {
    $total = $countResult->fetch_assoc()['total'] ?? 0;
}

// Fetch paginated events with organization name and new fields
$sql = "
    SELECT e.id, e.title, e.description, e.event_date, e.created_at, 
           e.is_disabled, e.status, 
           no.name AS org_name
    FROM events e
    INNER JOIN neworganizations no ON e.org_id = no.id
    ORDER BY e.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $row['is_disabled'] = (bool)$row['is_disabled']; // Cast to boolean for clarity
    $events[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'events' => $events,
    'total' => (int)$total,
    'perPage' => (int)$perPage,
]);

$conn->close();
