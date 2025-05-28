<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
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
$sql = "SELECT id, fullName, email, is_approved, created_at FROM users";
$result = $conn->query($sql);

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
            <input type="text" placeholder="Search Users...">
            <button>
                <img src="../fromOtherBranches/pics/search-icon.png" alt="Search" style="width: 20px; height: 20px;" />
            </button>
            </div>

        <div class="top-actions">
    <a href="create_org_admin.php" class="action-btn">+ Create Org Admin</a>
    <a href="create_admin.php" class="action-btn">+ Create Admin</a>
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
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
