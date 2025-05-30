<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch all organizations
$sql = "
    SELECT o.name, o.description, o.created_at 
    FROM organizations o
    INNER JOIN users u ON o.id = u.ID
    WHERE u.status != 'deleted'
    ORDER BY o.created_at ASC
";

$result = $conn->query($sql);

$organizations = [];
while ($row = $result->fetch_assoc()) {
    $organizations[] = $row;
}

// Fetch admin's full name from database
if ($admin_id) {
    $sql_admin = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $admin_name = ucwords(strtolower($result_admin->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "new-manage_events.css"; // Reuse manage events style
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="content">
    <h2 class="page-title">Manage Organizations</h2>

    <!-- Organizations Table -->
    <div class="event-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($organizations)) : ?>
                    <?php foreach ($organizations as $org) : ?>
                        <tr>
                            <td><?= htmlspecialchars($org['name']) ?></td>
                            <td><?= htmlspecialchars($org['description']) ?></td>
                            <td><?= date("M d, Y", strtotime($org['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">No organizations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="../assets/scripts/notif_script.js"></script>

<?php include '../includes/footer.php'; ?>
