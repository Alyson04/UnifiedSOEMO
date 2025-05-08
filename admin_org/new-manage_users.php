<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

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
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>UID-01</td>
                        <td>Jerome Abarca</td>
                        <td>abarca@gmail.com</td>
                        <td class="active">Active</td>
                    </tr>
                    <tr>
                        <td>UID-02</td>
                        <td>Clifford Balana</td>
                        <td>balana@gmail.com</td>
                        <td class="inactive">Inactive</td>
                    </tr>
                    <tr>
                        <td>UID-03</td>
                        <td>Janna Caballero</td>
                        <td>caballero@gmail.com</td>
                        <td class="active">Active</td>
                    </tr>
                    <tr>
                        <td>UID-04</td>
                        <td>Ashanti Caculba</td>
                        <td>caculba@gmail.com</td>
                        <td class="inactive">Inactive</td>
                    </tr>
                    <tr>
                        <td>UID-05</td>
                        <td>Alyson Calimag</td>
                        <td>calimag@gmail.com</td>
                        <td class="active">Active</td>
                    </tr>
                    <tr>
                        <td>UID-06</td>
                        <td>Jusphine Lacano</td>
                        <td>lacano@gmail.com</td>
                        <td class="inactive">Inactive</td>
                    </tr>
                    <tr>
                        <td>UID-07</td>
                        <td>Rica Mae Malgapo</td>
                        <td>malgapo@gmail.com</td>
                        <td class="active">Active</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


<?php include '../includes/footer.php'; ?>
