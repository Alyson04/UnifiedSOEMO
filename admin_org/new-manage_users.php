<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID and org ID
$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;
$admin_name = '';

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

// Fetch students who applied to this org
$sql = "
    SELECT 
        u.id, 
        u.fullName, 
        u.email, 
        a.application_status, 
        a.applied_at 
    FROM org_applications a
    JOIN users u ON a.student_id = u.id
    WHERE u.role = 'student' AND a.org_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "new-manage_user.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div class="content">
    <h2 class="page-title">MANAGE USERS</h2>

    <div class="search-bar">
        <input type="text" placeholder="Search Users...">
        <button>
            <img src="../fromOtherBranches/pics/search-icon.png" alt="Search" style="width: 20px; height: 20px;" />
        </button>
    </div>

    <!-- Users Table -->
    <div class="user-table">
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Applied At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
                <?php $status = $user['application_status']; ?>
                <tr>
                    <td><?= htmlspecialchars($user['fullName']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= ucfirst($status) ?: 'Pending'; ?></td>
                    <td><?= htmlspecialchars($user['applied_at']); ?></td>
                    <td>
    <?php if ($status === "approved" || $status === "declined"): ?>
        <form action="../api/process_application.php" method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
            <input type="hidden" name="org_id" value="<?= $org_id; ?>">  <!-- Added here -->
            <button type="submit" name="action" value="accept" disabled>Accept</button>
        </form>
        <form action="../api/process_application.php" method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
            <button type="submit" name="action" value="decline" disabled>Decline</button>
        </form>
    <?php else: ?>
        <form action="../api/process_application.php" method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
            <input type="hidden" name="org_id" value="<?= $org_id; ?>">  <!-- Added here -->
            <button type="submit" name="action" value="accept">Accept</button>
        </form>
        <form action="../api/process_application.php" method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
            <button type="submit" name="action" value="decline">Decline</button>
        </form>
    <?php endif; ?>
</td>

                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
