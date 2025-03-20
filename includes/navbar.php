<nav>
    <ul>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] !== 'admin'): ?>
            
            <nav class="nav-links">
                <a href="../students/dashboard.php">Home</a>
                <a href="../students/organizations.php">Organizations</a>
                <a href="../students/events.php">Events</a>
                <a href="../students/about_us.php">About Us</a>
            </nav>

            <?php endif; ?>
            
            <li><a href="../api/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="../public/login.php">Login</a></li>
            <li><a href="../public/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>


