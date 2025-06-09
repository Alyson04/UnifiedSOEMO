<?php 
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';

require '../config/db_conn.php';

// Fetch user's full name from newusers table
if ($student_id) {
    $sql_student = "SELECT firstName, middleName, lastName FROM newusers WHERE id = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

    if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();
        $firstName = $row['firstName'] ?? '';
        $middleName = $row['middleName'] ?? '';
        $lastName = $row['lastName'] ?? '';
        $student_name = ucwords(strtolower(trim("$firstName $middleName $lastName")));
    }

    $stmt->close();
}

$conn->close();

$title = "About Us"; 
$style = "about_us_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-lines">&#9776;</div>
</div>

<!-- Mobile Profile -->
<div class="mobile-profile" onclick="toggleMobileProfileDropdown()">
    <img src="<?= $profile_img ?>" alt="Profile Picture">
    <div class="mobile-dropdown-tray" id="mobileProfileDropdown">
        <a href="edit_profile.php">Edit Profile</a>
        <a href="../api/logout.php">Logout</a>
    </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="new-post.php">Posts</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="about_us.php">About Us</a></li>
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
    font-size: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

.mobile-profile {
    display: none;
    position: fixed;
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
}

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
}

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
    padding: 25px 0;
    margin: 0;
}

.sidebar-list li {
    padding: 0;
    margin: 5px 0px;
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
    margin-top: 5px;
    border-radius: 8px;
}

/* Mobile Responsive Styles */
@media only screen and (max-width: 600px) {
    .hamburger {
        display: flex;
    }
    
    .mobile-sidebar {
        display: block;
    }

    .mobile-profile {
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
    
    .about-wrapper {
        margin-top: 0px;
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

<div class="about-wrapper">
    <section class="about-us-section">
        <h2 class="section-title">ABOUT US</h2>
        <div class="about-us-content">
            <div class="about-text-box">
                <p><strong>Unified SOEMO</strong> is a dynamic platform designed to connect students and organizations, fostering collaboration and engagement. Our goal is to empower individuals to discover opportunities, participate in meaningful activities, and build lasting connections within their academic and social communities.</p>
            </div>
            <div class="about-image-box">
                <img src="../assets/pictures/about-us.png" alt="About Us">
            </div>
        </div>
    </section>

    <section class="mission-vision-section">
        <h2 class="section-title">MISSION and VISION</h2>
        <div class="mission-vision-grid">
            <div class="mission-box">
                <h3>Mission</h3>
                <p>At Unified SOEMO, our mission is to empower students by connecting them with organizations that foster growth, learning, and community involvement. We strive to create an inclusive platform where every student can easily discover and engage with groups that align with their passions and aspirations, helping them maximize their potential and enrich their campus experience.</p>
            </div>
            <div class="vision-box">
                <h3>Vision</h3>
                <p>Our vision is to be the leading platform for student engagement, fostering a vibrant and interconnected campus community. We aim to inspire students to build meaningful connections, develop lifelong skills, and contribute to a culture of collaboration and inclusivity within their universities and beyond.</p>
            </div>
        </div>
    </section>

    <section class="contact-us-section">
        <h2 class="section-title">CONTACT US</h2>
        <div class="contact-content">
            <div class="contact-text-box">
                <h4>Contact Us</h4>
                <p>We'd love to hear from you! Whether you have questions, feedback, or need assistance, the Unified SOEMO team is here to help. Feel free to reach out to us through any of the following channels:</p>
                <p><strong>Email</strong><br>
                📧 <a href="mailto:support@unsoemo.com">support@unsoemo.com</a><br>
                Send us an email, and we'll get back to you within 24–48 hours.</p>

              <p><strong>Social Media</strong><br>
            Connect with us on our social platforms for updates and quick assistance:<br>
            Facebook: <a href="https://www.facebook.com/UnifiedSoemo" target="_blank">UnifiedSoemo Official</a><br>
            Twitter: <a href="https://twitter.com/UnifiedSoemo" target="_blank">@UnifiedSoemo</a><br>
            Instagram: <a href="https://instagram.com/UnifiedSoemo" target="_blank">@UnifiedSoemo</a>
            </p>

            </div>
            <div class="contact-image-box">
                <img src="../assets/pictures/contact.png" alt="Contact Image">
            </div>
        </div>
    </section>
</div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
