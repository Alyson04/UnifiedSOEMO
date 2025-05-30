<?php
require '../config/db_conn.php';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$role_filter = $_GET['role'] ?? '';
$limit = 5;
$offset = ($page - 1) * $limit;

$params = [];
$sql = "SELECT id, fullName, email, role, created_at FROM users WHERE role != 'admin' AND status = 'active'";
$count_sql = "SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND status = 'active'";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $count_sql .= " AND role = ?";
    $params[] = $role_filter;
}

$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param("s", ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total count for pagination
$stmt_count = $conn->prepare($count_sql);
if (!empty($params)) $stmt_count->bind_param("s", ...$params);
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$total_users = $count_result['total'];
$stmt_count->close();

$conn->close();

echo json_encode([
    'users' => $users,
    'total' => $total_users,
    'perPage' => $limit,
]);
?>
