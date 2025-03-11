<?php
// admin_dashboard.php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ..functions/login.php");
    exit;
}

require '../functions/db_conn.php';

// Fetch pending applications
$sql = "SELECT * FROM users WHERE is_approved = 'pending'";
$result = $conn->query($sql);
$pendingApplications = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<!-- Modal Structure -->
    
    <div class="container">
        <div id="myModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <div id="modal-body">Loading...</div>
            </div>
        </div>
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <!-- <li><a href="create_admin.php">Create Admin Account</a></li> -->
                <li><a href="#" class="active">Manage Users</a></li>
                <li><a href="">Organizations</a></li>
                <li><a href="">Events</a></li>
                <li><a href="">Settings</a></li>
                <li><a href="../functions/logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="card">
                    <div class="card-header">
                        <h2>Pending Applications</h2>
                        </div>
                        <div class="card-body">
                        <div class="action-buttons" style="margin-bottom: 15px; display: flex; gap: 10px;">
                        <button onclick="location.href='create_admin.php'" title="Add User" style="border: none; background: none; cursor: pointer;">
                            <i class="fas fa-user-plus" style="font-size: 20px; color:green;"></i>
                        </button>
                        <button onclick="editUser()" title="Edit User" style="border: none; background: none; cursor: pointer;">
                            <i class="fas fa-edit" style="font-size: 20px; color: green;"></i>
                        </button>
                        <button onclick="deleteUser()" title="Delete User" style="border: none; background: none; cursor: pointer;">
                            <i class="fas fa-trash-alt" style="font-size: 20px; color: red;"></i>
                        </button>
                        </div>
                    
                    

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingApplications as $applicant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($applicant['fullName']) ?></td>
                                    <td>
                                        <form action="process_application.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $applicant['ID'] ?>">
                                            <button type="submit" name="action" value="accept">Accept</button>
                                        </form>
                                        <form action="process_application.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $applicant['ID'] ?>">
                                            <button type="submit" name="action" value="decline">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    </script>
</body>
</html>
