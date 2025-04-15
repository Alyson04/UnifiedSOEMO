<?php 
require '../config/db_conn.php';
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

$sql = "SELECT id, fullName, email, is_approved, created_at FROM users WHERE role != 'admin'";
$result = $conn->query($sql);

$title = "Manage Users";
$style = "manageuser_styles.css";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="main-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php"><img src="dashboard-icon.png" alt=""> Dashboard</a></li>
            <li><a href="manage_users.php" class="active"><img src="user-icon.png" alt=""> Manage Users</a></li>
            <li><a href="manage_organizations.php"><img src="org-icon.png" alt=""> Organizations</a></li>
            <li><a href="manage_events.php"><img src="event-icon.png" alt=""> Events</a></li>
            <li><a href="settings.php"><img src="settings-icon.png" alt=""> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">Manage Users</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                        <td><?= htmlspecialchars($user['id']); ?></td>
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
