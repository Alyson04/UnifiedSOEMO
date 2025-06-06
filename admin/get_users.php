<?php
require '../config/db_conn.php';

// Auto-update: change status to 'renewal' if created_at is older than 11 months
$updateRenewals = "
    UPDATE newusers
    SET status = 'renewal'
    WHERE status = 'active'
    AND created_at <= DATE_SUB(NOW(), INTERVAL 11 MONTH)
";
$conn->query($updateRenewals);

$disableExpiredRenewals = "
    UPDATE newusers
    SET status = 'disabled'
    WHERE status = 'renewal'
    AND created_at <= DATE_SUB(NOW(), INTERVAL 1 YEAR)
";
$conn->query($disableExpiredRenewals);

// Auto-update graduation
$currentYear = (int)date('Y');
$cutoffYear = $currentYear - 4;

$auto_update_sql = "
    UPDATE newusers
    SET graduated = 'yes'
    WHERE role IN ('student', 'orgAdmin')
    AND LENGTH(studentNumber) >= 4
    AND CAST(LEFT(studentNumber, 4) AS UNSIGNED) <= ?
    AND graduated != 'yes'
";
$stmt_auto = $conn->prepare($auto_update_sql);
$stmt_auto->bind_param("i", $cutoffYear);
$stmt_auto->execute();
$stmt_auto->close();

// Fetch users
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$role_filter = $_GET['role'] ?? '';
$limit = 5;
$offset = ($page - 1) * $limit;

$params = [];
$types = '';
$conditions = [];

$sql = "SELECT id, lastName, firstName, middleName, studentNumber, course, year, section, email, role, created_at, status, graduated FROM newusers";
$count_sql = "SELECT COUNT(*) as total FROM newusers";

if (!empty($role_filter)) {
    $conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if (!empty($conditions)) {
    $whereClause = " WHERE " . implode(' AND ', $conditions);
    $sql .= $whereClause;
    $count_sql .= $whereClause;
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($users as &$user) {
    $validYears = ['1', '2', '3'];
    $user['year'] = in_array((string)$user['year'], $validYears) ? (string)$user['year'] : '';
}

$stmt_count = $conn->prepare($count_sql);
if (!empty($conditions)) {
    $stmt_count->bind_param(substr($types, 0, strlen($types) - 2), ...array_slice($params, 0, -2));
}
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$total_users = $count_result['total'];
$stmt_count->close();

echo json_encode([
    'users' => $users,
    'total' => $total_users,
    'perPage' => $limit
]);
?>
