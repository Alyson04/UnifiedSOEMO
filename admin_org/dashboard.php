<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

// Get total users excluding admin
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

// Get upcoming events
$sql_events = "SELECT COUNT(*) AS total_events FROM events WHERE event_date >= CURDATE()";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

// Get recent events
$sql_recent_events = "SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);

$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}
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
    
$title = "Organizations Dashboard";
$style = "admindashboard_styles.css";
include '../includes/header.php';
?>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo"> 
            <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo"> 
        </div>
    
        <div class="search-profile">
            <input type="text" placeholder="Search">
            <img src="../assets/pictures/bell.png" alt="Bell Icon"> <!-- Notification Icon -->
            <div class="profile">
                <img src="../assets/pictures/profile.png" alt="Admin Profile">
                <div class="profile-text">
                    <span><?php echo htmlspecialchars($admin_name ?: 'Admin'); ?></span>
                    <p>Admin</p>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Section -->
    <section class="main-container">
        <div class="grid-container">
            <!-- Manage Users -->
            <a href="manage_users.php" class="grid-link">
                <div class="grid-item"> 
                    <img src="../assets/pictures/user-icon.png" alt="Manage Users"> 
                    <p>Manage Users</p> 
                </div>
            </a>

            <!-- Events -->
            <a href="manage_events.php" class="grid-link">
                <div class="grid-item"> 
                    <img src="../assets/pictures/event-icon.png" alt="Events"> 
                    <p>Events</p> 
                </div>
            </a>

            <!-- Settings -->
            <a href="settings.php" class="grid-link">
                <div class="grid-item"> 
                    <img src="../assets/pictures/settings-icon.png" alt="Settings"> 
                    <p>Settings</p> 
                </div>
            </a>
        </div>
    </section>

    <!-- Dashboard Summary -->
    <section class="summary">
        <h2>Dashboard Summary</h2>
        <div class="stat">
            <span>Total Users: <?php echo $total_users; ?></span>
            <div class="progress"><div style="width: <?php echo min($total_users, 100); ?>%;"></div></div>
        </div>
        <div class="stat">
            <span>Upcoming Events: <?php echo $total_events; ?></span>
            <div class="progress"><div style="width: <?php echo min($total_events, 100); ?>%;"></div></div>
        </div>
        <div class="stat">
            <span>Recent Events:</span>
            <ul>
                <?php 
                $has_past_events = false;

                if (!empty($recent_events)) {
                    foreach ($recent_events as $event) {
                        $event_date = strtotime($event['event_date']);
                        $today = strtotime(date("Y-m-d"));

                        if ($event_date <= $today) {
                            echo "<li>" . htmlspecialchars($event['title']) . " - " . date("M d, Y", $event_date) . "</li>";
                            $has_past_events = true;
                        }
                    }

                    if (!$has_past_events) {
                        echo "<li>No recent events</li>";
                    }
                } else {
                    echo "<li>No recent events</li>";
                }
                ?>
            </ul>
        </div>

    </section>

<?php include '../includes/footer.php'; ?>
