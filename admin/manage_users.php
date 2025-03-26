<?php 

require '../config/db_conn.php';
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

$sql = "SELECT id, fullName, email, is_approved, created_at FROM users WHERE role != 'admin'";
$result = $conn->query($sql);

$title = "Manage Users";
$style = "admindashboard_styles.css";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<h1>Admin Dashboard</h1>
<main>
<section class="dashboard">
    <div class="card"> <a href = "manage_users.php"> <p>Manage Users</p> </a> </div>
    <div class="card"> <a href = "manage_organizations.php"> <p>Organizations</p> </a> </div>
    <div class="card"> <a href = "manage_events.php"> <p>Events</p> </a> </div>
    <div class="card"> <a href = "settings.php"> <p>Settings</p> </a> </div>
</section>
</main>
<h2>Manage Users</h2>
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
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($user['is_approved'])); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                <td>
                    <form action="../api/process_application.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="action" value="accept">Accept</button>
                    </form>
                    <form action="../api/process_application.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="action" value="decline">Decline</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>