<?php 
require '../api/auth.php';
checkUserRole('admin'); // Ensure only admins can access this page

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;

// Fetch all organizations
$sql = "SELECT name, description, created_at FROM organizations ORDER BY created_at ASC";
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

$title = "Manage Organizations"; 
$style = "manageorg_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<div class="main-layout">
    <!-- Sidebar -->
    <?php
    include '../includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="page-title">Manage Organizations</h2>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" placeholder="Search Organizations...">
            <button>🔍</button>
        </div>

        <!-- Organizations Table -->
        <div class="org-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($organizations)) : ?>
                        <?php foreach ($organizations as $org) : ?>
                            <tr>
                                <td><?= htmlspecialchars($org['name']) ?></td>
                                <td><?= htmlspecialchars($org['description']) ?></td>
                                <td><?= date("M d, Y", strtotime($org['created_at'])) ?></td>
                                <td><button class="edit-btn">Edit</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="5">No organizations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
