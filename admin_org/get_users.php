<?php
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow org_admins

require '../config/db_conn.php';

header('Content-Type: application/json');

// Get session values
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id) {
    echo json_encode(['users' => [], 'total' => 0, 'perPage' => 5]);
    exit;
}

// Filters & Pagination
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Count total records (for pagination)
$count_sql = "
    SELECT COUNT(*) AS total 
    FROM org_applications a 
    JOIN users u ON a.student_id = u.id 
    WHERE u.role = 'student' AND u.status != 'deleted' AND a.org_id = ?
";
$params = [$org_id];
$types = 'i';

if (!empty($status_filter)) {
    $count_sql .= " AND a.application_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_users = $count_result->fetch_assoc()['total'] ?? 0;
$count_stmt->close();

// Fetch users
$sql = "
    SELECT u.id, u.fullName, u.email, u.status AS user_status,
           a.application_status, a.applied_at, a.org_id
    FROM org_applications a
    JOIN users u ON a.student_id = u.id
    WHERE u.role = 'student' AND u.status != 'deleted' AND a.org_id = ?
";
$params = [$org_id];
$types = 'i';

if (!empty($status_filter)) {
    $sql .= " AND a.application_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['users' => $users, 'total' => $total_users, 'perPage' => $limit]);
