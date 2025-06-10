<?php 

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

// Fetch student's full name from database
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

$org_id = $_GET['id'] ?? null;
$org = null;

if ($org_id) {
    $stmt = $conn->prepare("
        SELECT o.id, o.name, o.description, o.image_path, o.created_at,
               o.objective, o.how_to_join, o.requirements, o.mission, o.vision
        FROM neworganizations o
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $org = $result->fetch_assoc();
    }

    $stmt->close();
}

$conn->close();

$title = "Organizations";
$style = "orgpage_styles.css"; 
include '../includes/header.php';
include '../includes/navbar.php'; 
?>

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-lines">&#9776;</div>
</div>


<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

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

<style> 
/* Mobile Menu Styles */
.hamburger {
    display: none;
    position: fixed;
    top: 15px;
    left: 30px;
    z-index: 1002;
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

/* .mobile-profile {
    display: none;
    position: absolute;
    top: 15px;
    right: 30px;
    z-index: 1001;
    cursor: pointer;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1001;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
    display: block;
    opacity: 1;
} */
/* 
.mobile-profile img {
    width: 35px;
    height: 35px;
    border-radius: 6px;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.mobile-dropdown-tray {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    width: 150px;
    z-index: 1001;
}

.mobile-dropdown-tray.active {
    display: block;
}

.mobile-dropdown-tray a {
    display: block;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.mobile-dropdown-tray a:hover {
    background: #f5f5f5;
}

.mobile-dropdown-tray a:last-child {
    border-top: 1px solid #eee;
    color: #E74C3C;
} */

.mobile-sidebar {
    display: none;
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: #1e3a4f;
    z-index: 1002;
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
    padding: 20px 0;
    margin: 0;
}

.sidebar-list li {
    padding: 0;
    margin: 5px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sidebar-list li a {
    color: rgb(255, 255, 255);
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    display: block;
    padding: 12px 0px;
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
    
    .outer-container {
        margin-top: 60px;
    }

    body.sidebar-active {
        overflow: hidden;
    }
}
</style>

<script>
// Mobile menu functionality
function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    body.classList.toggle('sidebar-active');
    
    // Close profile dropdown when opening sidebar
    const profileDropdown = document.getElementById('mobileProfileDropdown');
    if (profileDropdown.classList.contains('active')) {
        profileDropdown.classList.remove('active');
    }
}

// Mobile profile dropdown functionality
function toggleMobileProfileDropdown(event) {
    // Only allow toggling if sidebar is not active
    const sidebar = document.getElementById('mobileSidebar');
    if (!sidebar.classList.contains('active')) {
        const dropdown = document.getElementById('mobileProfileDropdown');
        dropdown.classList.toggle('active');
        event.stopPropagation();
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('mobileSidebar');
    const hamburger = document.querySelector('.hamburger');
    const profileDropdown = document.getElementById('mobileProfileDropdown');
    const mobileProfile = document.querySelector('.mobile-profile');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Close sidebar if clicking overlay
    if (event.target === overlay) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-active');
    }
    
    // Close profile dropdown if clicking outside (only if sidebar is not active)
    if (!sidebar.classList.contains('active') && 
        !mobileProfile.contains(event.target) && 
        profileDropdown.classList.contains('active')) {
        profileDropdown.classList.remove('active');
    }
});
</script>

<div class="outer-container">
<?php if ($org): ?>
    <div class="org-container">
        <div class="corner-top-right"></div>
        <div class="corner-bottom-left"></div>
        
        <?php 
        
        // Prepare image path: prepend folder path if image exists, else default
        if (!empty($org['image_path'])) {
            $imagePath = "../assets/uploads_organizations/" . htmlspecialchars($org['image_path']);
        } else {
            $imagePath = "../assets/pictures/default.jpg";
        }
        ?>
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($org['name']) ?> Logo" class="org-logo">

        <h1><?= htmlspecialchars($org['name']) ?></h1>

        <?php if (!empty($org['description'])): ?>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($org['description'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($org['objective'])): ?>
            <div class="details-box">
                <details>
                    <summary>🎯 Objective</summary>
                    <p><?= nl2br(htmlspecialchars($org['objective'])) ?></p>
                </details>
            </div>
        <?php endif; ?>

        <?php if (!empty($org['how_to_join'])): ?>
            <div class="details-box">
                <details>
                    <summary>📝 How to Join</summary>
                    <p><?= nl2br(htmlspecialchars($org['how_to_join'])) ?></p>
                </details>
            </div>
        <?php endif; ?>

        <?php if (!empty($org['requirements'])): ?>
            <div class="details-box">
                <details>
                    <summary>📌 Requirements</summary>
                    <ul>
                        <?php
                        $requirements = explode(',', $org['requirements']);
                        foreach ($requirements as $req):
                        ?>
                            <li><?= htmlspecialchars(trim($req)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            </div>
        <?php endif; ?>

        <div class="join-section">
            <a href="register.php" class="join-button">Join Now</a>
        </div>
    </div>

<?php else: ?>
    <p>Organization not found.</p>
<?php endif; ?>
</div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
