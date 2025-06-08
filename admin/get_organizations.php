<?php
require '../config/db_conn.php';

// Set renewal status if the renewal date is in the past
$updateStatusSQL = "
    UPDATE neworganizations
    SET status = 'renewal'
    WHERE status = 'active'
    AND renewal_date <= NOW();
";
$conn->query($updateStatusSQL);

// Set expired status if the expiry date is in the past
$updateExpiredStatusSQL = "
    UPDATE neworganizations
    SET status = 'expired'
    WHERE status = 'renewal'
    AND expiry_date <= NOW();
";
$conn->query($updateExpiredStatusSQL);

// Transition from revalidation to active, updating renewal and expiry dates only if status is revalidation
$updateRevalidationToActiveSQL = "
    UPDATE neworganizations
    SET status = 'active',
        renewal_date = DATE_ADD(NOW(), INTERVAL 11 MONTH),
        expiry_date = DATE_ADD(NOW(), INTERVAL 12 MONTH),
        last_updated = NOW()
    WHERE status = 'revalidation' AND id = ?;
";

if (isset($organizationId)) {
    $stmt = $conn->prepare($updateRevalidationToActiveSQL);
    $stmt->bind_param("i", $organizationId); // Ensure $organizationId is set to the specific organization ID
    $stmt->execute();
    $stmt->close();
}

// Update status from renewal to revalidation when explicitly set, only updating last_updated
$updateRenewalToRevalidationSQL = "
    UPDATE neworganizations
    SET status = 'revalidation',
        last_updated = NOW()
    WHERE status = 'renewal' AND id = ?;
";

if (isset($organizationId)) {
    $stmt = $conn->prepare($updateRenewalToRevalidationSQL);
    $stmt->bind_param("i", $organizationId); // Ensure $organizationId is set to the specific organization ID
    $stmt->execute();
    $stmt->close();
}

// Pagination settings
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Get status filter from request, if any
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get total number of organizations, filtered by status
$sqlCount = "SELECT COUNT(*) AS total FROM neworganizations";
if (!empty($status)) {
    $sqlCount .= " WHERE status = ?";
}

$stmtCount = $conn->prepare($sqlCount);
if (!empty($status)) {
    $stmtCount->bind_param("s", $status); // Bind dynamic parameters for status
}
$stmtCount->execute();
$countResult = $stmtCount->get_result();
$total = 0;
if ($countResult) {
    $row = $countResult->fetch_assoc();
    $total = (int) $row['total'];
}
$stmtCount->close();

// Get paginated organizations from neworganizations table
// Get paginated organizations from neworganizations table, along with the user name from newusers table
$sql = "
    SELECT 
        no.id, 
        no.name, 
        no.description, 
        no.mission, 
        no.vision, 
        no.objective, 
        no.how_to_join, 
        no.requirements, 
        no.status, 
        no.image_path, 
        no.created_at, 
        no.renewal_date, 
        no.expiry_date, 
        no.last_updated, 
        no.user_id,
        nu.firstName,   -- Get first name of the user
        nu.middleName,  -- Get middle name of the user
        nu.lastName     -- Get last name of the user
    FROM neworganizations no
    LEFT JOIN newusers nu ON no.user_id = nu.id
";


if (!empty($status)) {
    $sql .= " WHERE status = ?";
}

$sql .= " ORDER BY created_at DESC LIMIT ?, ?";

// Prepare and execute the query for organizations
$stmt = $conn->prepare($sql);
if (!empty($status)) {
    $stmt->bind_param("ssi", $status, $offset, $perPage);  // Bind status along with pagination parameters
} else {
    $stmt->bind_param("ii", $offset, $perPage);  // Bind pagination parameters only
}
$stmt->execute();
$result = $stmt->get_result();

$organizations = [];
while ($row = $result->fetch_assoc()) {
    $organizations[] = $row;
}

$stmt->close();
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'total' => $total,
    'perPage' => $perPage,
    'organizations' => $organizations,
]);
?>
