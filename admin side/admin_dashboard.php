<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ..functions/login.php");
    exit;
}

require '../functions/db_conn.php';

// Fetch total users
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #1e2a47;
            color: #fff;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, #b3f0c2, #8ec5fc);
            padding: 15px 30px;
        }
        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        .nav-links li a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        .search-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-profile input {
            padding: 5px;
            border-radius: 5px;
            border: none;
        }
        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .dashboard {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .dashboard a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        .card {
            background: #e0dfca;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            width: 150px;
            color: black;
        }
        .summary {
            background: #2c3e50;
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
        }
        .progress {
            background: #ddd;
            border-radius: 5px;
            overflow: hidden;
            height: 10px;
            margin: 5px 0;
        }
        .progress div {
            background: #00c3ff;
            height: 100%;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">LOGO</div>
            <ul class="nav-links">
                <li><a href="#">HOME</a></li>
                <li><a href="#">ORGANIZATION</a></li>
                <li><a href="#">EVENTS</a></li>
                <li><a href="#">ABOUT US</a></li>
            </ul>
            <div class="search-profile">
                <input type="text" placeholder="Search...">
                <div class="profile">
                    <img src="profile.jpg" alt="User">
                    <span>Monti Roy</span>
                </div>
            </div>
        </nav>
    </header>
    
    <main>
        <section class="dashboard">
            <div class="card"> <a href = "manage_users.php"> <p>Manage Users</p> </a> </div>
            <div class="card"> <a href = "#"> <p>Organizations</p> </a> </div>
            <div class="card"> <a href = "#"> <p>Events</p> </a> </div>
            <div class="card"> <a href = "#"> <p>Settings</p> </a> </div>
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
</body>
</html>
