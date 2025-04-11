<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get total users excluding admin
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

// Get total organizations
$sql_orgs = "SELECT COUNT(*) AS total_organizations FROM organizations";
$result_orgs = $conn->query($sql_orgs);
$total_organizations = $result_orgs->fetch_assoc()['total_organizations'];

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified SOEMO Dashboard</title>
    <link rel="stylesheet" href="../assets/stylesheets/admindashboard_styles.css">
</head>
<body>

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
                    <span>Moni Roy</span>
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

            <!-- Organizations -->
            <a href="manage_organizations.php" class="grid-link">
                <div class="grid-item"> 
                    <img src="../assets/pictures/org-icon.png" alt="Organizations"> 
                    <p>Organizations</p> 
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
            <span>Active Organizations: <?php echo $total_organizations; ?></span>
            <div class="progress"><div style="width: <?php echo min($total_organizations, 100); ?>%;"></div></div>
        </div>
        <div class="stat">
            <span>Upcoming Events: <?php echo $total_events; ?></span>
            <div class="progress"><div style="width: <?php echo min($total_events, 100); ?>%;"></div></div>
        </div>
        <div class="stat">
            <span>Recent Events:</span>
            <ul>
                <?php 
                if (!empty($recent_events)) {
                    foreach ($recent_events as $event) {
                        echo "<li>" . htmlspecialchars($event['title']) . " - " . date("M d, Y", strtotime($event['event_date'])) . "</li>";
                    }
                } else {
                    echo "<li>No recent events</li>";
                }
                ?>
            </ul>
        </div>
    </section>

</body>
</html>
