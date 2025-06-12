<?php
require '../api/auth.php';
checkUserRole('orgAdmin');
require '../config/db_conn.php';

// Get org admin's organization ID from session or fetch it
$user_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id && $user_id) {
    $sql_org = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_id = $result->fetch_assoc()['id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt->close();
}

if (!$org_id) {
    echo json_encode([
        'users' => [],
        'total' => 0,
        'perPage' => 5
    ]);
    exit;
}

// Auto-update graduation
$currentYear = (int)date('Y');
$cutoffYear = $currentYear - 4;

$auto_update_sql = "
    UPDATE newusers u
    INNER JOIN organization_members om ON u.id = om.user_id
    SET u.graduated = 'yes'
    WHERE om.organization_id = ?
    AND u.role = 'student'
    AND LENGTH(u.studentNumber) >= 4
    AND CAST(LEFT(u.studentNumber, 4) AS UNSIGNED) <= ?
    AND u.graduated != 'yes'
";
$stmt_auto = $conn->prepare($auto_update_sql);
$stmt_auto->bind_param("ii", $org_id, $cutoffYear);
$stmt_auto->execute();
$stmt_auto->close();

// Fetch users
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$status_filter = $_GET['status'] ?? '';
$limit = 5;
$offset = ($page - 1) * $limit;

$params = [$org_id];
$types = 'i';
$conditions = ["om.organization_id = ?"];

if (!empty($status_filter)) {
    $conditions[] = "u.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// SQL to fetch users who are members of the organization
$sql = "
    SELECT DISTINCT
        u.id, u.lastName, u.firstName, u.middleName, u.studentNumber, 
        u.course, u.year, u.section, u.email, u.role, u.created_at, 
        u.status, u.graduated, 
        jo.status AS applicationStatus,
        no.name AS orgName
    FROM newusers u
    LEFT JOIN join_org jo ON u.id = jo.student_id
    LEFT JOIN organization_members om ON u.id = om.user_id
    LEFT JOIN neworganizations no ON om.organization_id = no.id
";

$count_sql = "
    SELECT COUNT(DISTINCT u.id) as total 
    FROM newusers u
    LEFT JOIN organization_members om ON u.id = om.user_id
";

if (!empty($conditions)) {
    $whereClause = " WHERE " . implode(' AND ', $conditions);
    $sql .= $whereClause;
    $count_sql .= $whereClause;
}

$sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Validate year values
foreach ($users as &$user) {
    $validYears = ['1', '2', '3'];
    $user['year'] = in_array((string)$user['year'], $validYears) ? (string)$user['year'] : '';
}

// Get total count
$stmt_count = $conn->prepare($count_sql);
if (!empty($conditions)) {
    $stmt_count->bind_param(substr($types, 0, strlen($types) - 2), ...array_slice($params, 0, -2));
}
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$total_users = $count_result['total'];
$stmt_count->close();

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'users' => $users,
    'total' => $total_users,
    'perPage' => $limit
]);
