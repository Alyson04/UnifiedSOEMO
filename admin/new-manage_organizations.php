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

$conn->close();
    
$title = "Unified SOEMO Dashboard";
$style = "new-manage_organizations.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

  <!-- Main Panel -->
<main class="main-content">
<?php
include '../includes/navbar.php';
?>

<!-- Main Content -->
<div class="content">
        <h2 class="page-title">Manage Organizations</h2>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" placeholder="Search Events...">
            <button>
                <img src="search-icon.png" alt="Search" style="width: 20px; height: 20px;" />
            </button>
            </div>

        <!-- Organizations Table -->
        <div class="org-table">
            <table>
                <thead>
                    <tr>
                        <th>Org ID</th>
                        <th>Name</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>OID-01</td>
                        <td>PUP Sentral na Konseho ng Mag-aaral</td>
                        <td>Student Councils</td>
                    </tr>
                    <tr>
                        <td>OID-02</td>
                        <td>PUP Institute of Technology Student Council</td>
                        <td>Student Councils</td>
                    </tr>
                    <tr>
                        <td>OID-03</td>
                        <td>PUP The Programmers' Club</td>
                        <td>Academic Org</td>
                    </tr>
                    <tr>
                        <td>OID-04</td>
                        <td>PUP SANDIWA</td>
                        <td>Advocacy Groups</td>
                    </tr>
                    <tr>
                        <td>OID-05</td>
                        <td>HATAW PUP</td>
                        <td>Advocacy Groups</td>
                    </tr>
                    <tr>
                        <td>OID-06</td>
                        <td>PUP Sintang Pusa</td>
                        <td>Animal Welfare</td>
                    </tr>
                    <tr>
                        <td>OID-07</td>
                        <td>Youth for Animals PUP</td>
                        <td>Animal Welfare</td>
                    </tr>
                    <tr>
                        <td>OID-08</td>
                        <td>PUP Polysound Band</td>
                        <td>Arts and Culture</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


<?php include '../includes/footer.php'; ?>
