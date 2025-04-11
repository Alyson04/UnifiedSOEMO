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

<?php $title = "Manage Organizations"; $style = "admindashboard_styles.css"; include '../includes/header.php'; include '../includes/navbar.php'; ?>

<a href="dashboard.php"><h1>Admin Dashboard</h1></a>
<main>
    <section class="dashboard">
        <div class="card"> <a href="manage_users.php"> <p>Manage Users</p> </a> </div>
        <div class="card"> <a href="manage_organizations.php"> <p>Organizations</p> </a> </div>
        <div class="card"> <a href="manage_events.php"> <p>Events</p> </a> </div>
        <div class="card"> <a href="settings.php"> <p>Settings</p> </a> </div>
    </section>

    <section class="organizations">
        <h2>Organizations</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($organizations)) : ?>
                    <?php foreach ($organizations as $org) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($org['id']); ?></td>
                            <td><?php echo htmlspecialchars($org['name']); ?></td>
                            <td><?php echo htmlspecialchars($org['description']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($org['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="4">No organizations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
