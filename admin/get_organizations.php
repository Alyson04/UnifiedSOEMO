<?php
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Pagination settings
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Get total number of organizations (excluding deleted users)
$sqlCount = "
    SELECT COUNT(*) AS total 
    FROM organizations o
    INNER JOIN users u ON o.id = u.ID
    WHERE u.status != 'deleted'
";
$resultCount = $conn->query($sqlCount);
$total = 0;
if ($resultCount) {
    $row = $resultCount->fetch_assoc();
    $total = (int) $row['total'];
}

// Get paginated organizations
$sql = "
    SELECT o.name, o.description, o.created_at
    FROM organizations o
    INNER JOIN users u ON o.id = u.ID
    WHERE u.status != 'deleted'
    ORDER BY o.created_at ASC
    LIMIT ?, ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();

$organizations = [];
while ($row = $result->fetch_assoc()) {
    $organizations[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'total' => $total,
    'perPage' => $perPage,
    'organizations' => $organizations,
]);
