<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
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

$sql = "SELECT id, fullName, email, is_approved, created_at FROM users WHERE role = 'student' AND org_id = ?";
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
<?php
include '../includes/navbar.php';
?>

<div class="content">
        <h2 class="page-title">MANAGE USERS</h2>

        <div class="search-bar">
            <input type="text" placeholder="Search Events...">
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
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['fullName']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                                if ($user['is_approved'] === "approved") {
                                    echo "Approved";
                                } elseif ($user['is_approved'] === "declined") {
                                    echo "Declined";
                                } else {
                                    echo "Pending";
                                }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <?php if ($user['is_approved'] === "approved" || $user['is_approved'] === "declined"): ?>
                                <form action="../api/process_application.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <button type="submit" name="action" value="accept" disabled>Accept</button>
                                </form>
                                <form action="../api/process_application.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <button type="submit" name="action" value="decline" disabled>Decline</button>
                                </form>
                            <?php else: ?>
                                <form action="../api/process_application.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
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


<?php include '../includes/footer.php'; ?>
