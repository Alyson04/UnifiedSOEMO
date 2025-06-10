<?php
 
require '../api/auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';
header('Content-Type: application/json');
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $sql = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $org = $result->fetch_assoc();
    $_SESSION['org_id'] = $org['id'] ?? null; // Save org_id to session
}
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Total count of events under orgAdmin's organizations
$sql_count = "
    SELECT COUNT(*) AS total
    FROM events e
    INNER JOIN neworganizations o ON e.org_id = o.id
    WHERE o.user_id = ?
";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Fetch events with pagination
$sql = "
    SELECT e.id, e.description, e.title, e.event_date, e.created_at
    FROM events e
    INNER JOIN neworganizations o ON e.org_id = o.id
    WHERE o.user_id = ?
    ORDER BY e.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();
$conn->close();

// ... your existing PHP code ...

echo json_encode([
    'user_id' => $user_id,   // Add this line
    'events' => $events,
    'total' => $total,
    'perPage' => $limit
]);

