<nav>
    <ul>
        <li><a href="../public/index.php">Home</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="../students/dashboard.php">Dashboard</a></li>
            <li><a href="../api/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="../public/login.php">Login</a></li>
            <li><a href="../public/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>


