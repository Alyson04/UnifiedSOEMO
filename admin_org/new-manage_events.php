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
$style = "new-manage_events.css";
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
        <h2 class="page-title">MANAGE EVENTS</h2>

        <!-- Search Bar -->
        <!-- Search Bar -->
    <div class="search-bar">
    <input type="text" placeholder="Search Events...">
    <button>
        <img src="../fromOtherBranches/pics/search-icon.png" alt="Search" style="width: 20px; height: 20px;" />
    </button>
    </div>


        <!-- Events Table -->
        <div class="event-table">
            <table>
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>EID-01</td>
                        <td>Hackathon</td>
                        <td>01-01-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                    <tr>
                        <td>EID-02</td>
                        <td>Animal Saving</td>
                        <td>01-02-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                    <tr>
                        <td>EID-03</td>
                        <td>Charity</td>
                        <td>01-06-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                    <tr>
                        <td>EID-04</td>
                        <td>Coding Club</td>
                        <td>01-07-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                    <tr>
                        <td>EID-05</td>
                        <td>Coding Club</td>
                        <td>01-08-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                    <tr>
                        <td>EID-06</td>
                        <td>Community Concert</td>
                        <td>01-09-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                    <tr>
                        <td>EID-07</td>
                        <td>Animal Welfare</td>
                        <td>01-10-2025</td>
                        <td><button class="edit-btn">Edit/Cancel</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


<?php include '../includes/footer.php'; ?>
