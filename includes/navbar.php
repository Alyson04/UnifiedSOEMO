<nav class="nav-links">
    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] !== 'admin'): ?>
                <li><a href="../students/dashboard.php">Home</a></li>
                <li><a href="../students/organizations.php">Organizations</a></li>
                <li><a href="../students/events.php">Events</a></li>
                <li><a href="../students/about_us.php">About Us</a></li>
            <?php endif; ?>
            <li><a href="../api/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="../public/login.php">Login</a></li>
            <li><a href="../public/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>