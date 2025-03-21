<?php 
require '../config/db_conn.php';
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

$sql = "SELECT id, fullName, email, created_at FROM users WHERE role != 'admin'";
$result = $conn->query($sql);

$title = "Manage Users"; include '../includes/header.php';

include '../includes/navbar.php'; ?>

<table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
</table>

<?php include '../includes/footer.php'; ?>