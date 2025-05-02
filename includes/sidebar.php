<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? null;
?>

<div class="sidebar">
    <ul>
        <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"><img src="dashboard-icon.png" alt=""> Dashboard</a></li>
        <li><a href="manage_users.php" class="<?= $current_page === 'manage_users.php' ? 'active' : '' ?>"><img src="user-icon.png" alt=""> Manage Users</a></li>
        
        <?php if ($user_role === 'admin'): ?>
            <li><a href="manage_organizations.php" class="<?= $current_page === 'manage_organizations.php' ? 'active' : '' ?>"><img src="org-icon.png" alt=""> Organizations</a></li>
        <?php endif; ?>
        
        <li><a href="manage_events.php" class="<?= $current_page === 'manage_events.php' ? 'active' : '' ?>"><img src="event-icon.png" alt=""> Events</a></li>
        <li><a href="settings.php" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>"><img src="settings-icon.png" alt=""> Settings</a></li>
    </ul>
</div>
