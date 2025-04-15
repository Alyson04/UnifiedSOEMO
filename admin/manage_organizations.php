<?php 
require '../api/auth.php';
checkUserRole('admin'); // Ensure only admins can access this page

require '../config/db_conn.php';

// Fetch all organizations
$sql = "SELECT id, name, description, created_at FROM organizations ORDER BY created_at DESC";
$result = $conn->query($sql);

$organizations = [];
while ($row = $result->fetch_assoc()) {
    $organizations[] = $row;
}

$conn->close();
?>

<?php 
$title = "Manage Organizations"; 
$style = "manageorg_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<div class="main-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php"><img src="dashboard-icon.png" alt=""> Dashboard</a></li>
            <li><a href="manage_users.php"><img src="user-icon.png" alt=""> Manage Users</a></li>
            <li><a href="manage_organizations.php" class="active"><img src="org-icon.png" alt=""> Organizations</a></li>
            <li><a href="manage_events.php"><img src="event-icon.png" alt=""> Events</a></li>
            <li><a href="settings.php"><img src="settings-icon.png" alt=""> Settings</a></li>
        </ul>
    </div>

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
                        <th>Org ID</th>
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
                                <td><?= htmlspecialchars($org['id']) ?></td>
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

<?php include '../includes/footer.php'; ?>
