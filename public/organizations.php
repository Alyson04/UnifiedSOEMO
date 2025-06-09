<?php 
session_start();
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

// Fetch student's full name if logged in
if ($student_id) {
    $sql_student = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();
    if ($result_student->num_rows > 0) {
        $student_name = ucwords(strtolower($result_student->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

$title = "Organizations";
$style = "organizations_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-lines">&#9776;</div>
</div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="index.php">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="about_us.php">About Us</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="../api/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</div>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<section class="background">
    <div class="search-container">
        <input type="text" class="search-bar" id="orgSearch" placeholder="Search Organizations...">
    </div>
</section>

<section class="student-org">
    <h2 class="section-title">STUDENT ORGANIZATION</h2>
    <div class="student-org-wrapper">
        <div class="student-org-container" id="orgResults">
        
        <?php
        $sql = "
            SELECT DISTINCT o.id, o.name, o.description, o.image_path, o.created_at
            FROM neworganizations o
            ORDER BY o.created_at ASC
        ";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $name = htmlspecialchars($row['name']);
                $description = htmlspecialchars($row['description']);
                $imagePath = !empty($row['image_path']) 
                    ? "../assets/uploads_organizations/" . htmlspecialchars($row['image_path']) 
                    : "../assets/pictures/default.jpg";
        ?>
            <div class="student-org-card">
                <img src="<?= $imagePath ?>" alt="<?= $name ?>">
                <h4><?= $name ?></h4>
                <p><?= $description ?></p>
                <a href="org_page.php?id=<?= $row['id'] ?>" class="join-btn">LEARN MORE</a>
            </div>
        <?php
            endwhile;
        else:
            echo '<p style="color: white;">No organizations found.</p>';
        endif;
        ?>
        </div>
    </div>
</section>

<style>
/* Same style block */
.session-alert {
    position: fixed;
    top: 100px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50;
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    max-width: 80%;
    text-align: center;
    animation: fadeInSlideDown 0.4s ease-in-out;
}
.session-alert.error {
    background-color: #f44336;
}
@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

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
    
    .search-container {
        margin-top: 60px;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("orgSearch");
    const resultsContainer = document.getElementById("orgResults");

    function fetchOrgs(query) {
        let url = 'search_orgs.php';
        if (query.trim() !== '') {
            url += '?q=' + encodeURIComponent(query.trim());
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", url, true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                resultsContainer.innerHTML = xhr.responseText;
            } else {
                resultsContainer.innerHTML = "<p style='color: white;'>Something went wrong.</p>";
            }
        };

        xhr.send();
    }

    fetchOrgs('');

    searchInput.addEventListener("input", () => {
        fetchOrgs(searchInput.value);
    });

    const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
});

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

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php 
$conn->close(); 
include '../includes/footer.php'; 
?>
