<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];
$conn->close();
?>

<?php $title = "Admin Dashboard";$style = "admindashboard_styles.css"; include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<h1>Admin Dashboard</h1>
<main>
        <section class="dashboard">
            <div class="card"> <a href = "manage_users.php"> <p>Manage Users</p> </a> </div>
            <div class="card"> <a href = "manage_organizations.php"> <p>Organizations</p> </a> </div>
            <div class="card"> <a href = "manage_events.php"> <p>Events</p> </a> </div>
            <div class="card"> <a href = "settings.php"> <p>Settings</p> </a> </div>
        </section>
        
        <section class="summary">
            <h2>Dashboard Summary</h2>
            <div class="stat">
                <span>Total Users: <?php echo $total_users; ?></span>
                <div class="progress"><div style="width: <?php echo min($total_users, 100); ?>%;"></div></div>
            </div>
            <div class="stat">
                <span>Active Organizations: 50</span>
                <div class="progress"><div style="width: 50%;"></div></div>
            </div>
            <div class="stat">
                <span>Upcoming Events: 15</span>
                <div class="progress"><div style="width: 15%;"></div></div>
            </div>
            <div class="stat">
                <span>Recent Activities: None</span>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>
