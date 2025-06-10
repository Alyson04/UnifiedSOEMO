<?php 

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';
// Fetch admin's full name from database
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

$conn->close();

$title = "About Us"; 
$style = "about_us_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php' 
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
    
    .about-wrapper {
        margin-top: 0px;
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
