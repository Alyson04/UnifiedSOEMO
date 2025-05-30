<?php
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Pagination settings
$perPage = 5; // events per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Get total count of events (for pagination)
$sqlCount = "
    SELECT COUNT(*) as total
    FROM events e
    INNER JOIN organizations o ON e.org_id = o.id
    INNER JOIN users u ON o.id = u.ID
    WHERE u.status != 'deleted'
";
$countResult = $conn->query($sqlCount);
$total = 0;
if ($countResult) {
    $total = $countResult->fetch_assoc()['total'] ?? 0;
}

// Fetch paginated events
$sql = "
    SELECT e.id, e.title, e.event_date, o.name AS org_name, e.created_at
    FROM events e
    INNER JOIN organizations o ON e.org_id = o.id
    INNER JOIN users u ON o.id = u.ID
    WHERE u.status != 'deleted'
    ORDER BY e.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'events' => $events,
    'total' => (int)$total,
    'perPage' => (int)$perPage,
]);

$conn->close();
