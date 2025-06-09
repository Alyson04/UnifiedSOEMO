<?php 
require '../api/auth.php';
require '../config/db_conn.php';

$title = "Posts";
$style = "student-new-post.css";
include '../includes/header.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-lines">&#9776;</div>
</div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="new-post.php">Posts</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="about_us.php">About Us</a></li>
        <li><a href="../api/logout.php">Logout</a></li>
    </ul>
</div>

<div id="postsWrapper">
  <div id="postsContainer">
    <?php
    // Query posts excluding deleted users/orgs
    $query = "
  SELECT posts.*, users.fullName, organizations.image_path AS org_image_path
  FROM posts
  LEFT JOIN users ON posts.user_id = users.id
  LEFT JOIN organizations ON users.org_id = organizations.id
  WHERE users.status != 'deleted'
  ORDER BY posts.created_at DESC
";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $content = htmlspecialchars($row['content']);
            $username = htmlspecialchars($row['fullName'] ?? 'Unknown');

            $default_img = '../assets/pictures/icon.png';
            $profile_img = $default_img;

            if (!empty($row['org_image_path'])) {
                $possible_path = '../assets/uploads_organizations/' . $row['org_image_path'];
                if (file_exists($possible_path)) {
                    $profile_img = $possible_path;
                }
            }

            echo "<div class='post-card'>
                    <div class='post-header'>
                      <img src='{$profile_img}' alt='Profile picture of {$username}' />
                      <span class='username'>{$username}</span>
                    </div>
                    <div class='post-content'>{$content}</div>";

            if (!empty($row['image_path'])) {
                $postImage = "../uploads/" . htmlspecialchars($row['image_path']);
                echo "<div class='post-image'>
                        <img src='{$postImage}' alt='Post Image' style='max-width: 100%; border-radius: 10px; margin-top: 10px;' />
                      </div>";
            }

            echo "</div>";
        }
    } else {
        echo "<p class='no-post'>No posts available.</p>";
    }
    ?>
  </div>
</div>

<script src="../assets/scripts/inactive.js"></script>

<style>
/* Mobile Menu Styles */
.hamburger {
    display: none;
    position: fixed;
    top: 15px;
    left: 30px;
    z-index: 1000;
    cursor: pointer;
    background: #1e3a4f;
    width: 35px;
    height: 35px;
    border-radius: 6px;
    justify-content: center;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    padding: 0;
}

.hamburger-lines {
    color: #fff;
    font-size: 20px;
    line-height: 35px;
    text-align: center;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.mobile-sidebar {
    display: none;
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: #1e3a4f;
    z-index: 999;
    transition: all 0.3s ease-in-out;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.mobile-sidebar.active {
    left: 0;
}

.sidebar-list {
    list-style: none;
    padding: 50px 0;
    margin: 0;
}

.sidebar-list li {
    padding: 0;
    margin: 5px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sidebar-list li a {
    color: rgb(255, 255, 255);
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    display: block;
    padding: 12px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
}

.sidebar-list li:hover {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar-list li a:hover {
    color: rgba(173, 211, 204, 1);
    transform: translateX(5px);
}

.sidebar-list li:last-child {
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 0;
}

.sidebar-list li:last-child a {
    color: #E74C3C;
}

.sidebar-list li:last-child:hover {
    background: rgba(231, 76, 60, 0.15);
}

.sidebar-list li:last-child a:hover {
    color: #ff6b6b;
}

/* Mobile Responsive Styles */
@media only screen and (max-width: 600px) {
    .hamburger {
        display: block;
    }
    
    .mobile-sidebar {
        display: block;
    }
    
    .navbar {
        display: none !important;
        visibility: hidden;
        opacity: 0;
    }
    
    nav {
        display: none !important;
    }
    
    .nav-list {
        display: none !important;
    }

    .logo {
        display: none !important;
    }
    
    .logo img {
        display: none !important;
    }

    .profile {
        display: none !important;
    }

    .top-bar {
        display: none !important;
    }
    
    #postsWrapper {
        margin-top: 60px;
    }
}
</style>

<script>
// Mobile menu functionality
function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('mobileSidebar');
    const hamburger = document.querySelector('.hamburger');
    
    if (!sidebar.contains(event.target) && !hamburger.contains(event.target) && sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
