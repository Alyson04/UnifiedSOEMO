<?php 
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
$tutorial_seen = 0;

require '../config/db_conn.php';

if ($student_id) {
    $sql_student = "SELECT firstName, middleName, lastName, tutorial_seen FROM newusers WHERE id = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

    if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();

        $first = $row['firstName'] ?? '';
        $middle = $row['middleName'] ?? '';
        $last = $row['lastName'] ?? '';
        $student_name = ucwords(strtolower(trim("$first $middle $last")));

        $tutorial_seen = $row['tutorial_seen'] ?? 0;
    }

    $stmt->close();
}

$conn->close();

$title = "Student Dashboard";
$style = "studentdashboard_styles.css";
include '../includes/header.php';
include '../includes/navbar.php'; 
?>


<?php if (!$tutorial_seen): ?>
<div class="popup-overlay" id="tutorial">
    <div class="popup" id="popupBox">
        <button class="close-btn" onclick="closeTutorial()">&times;</button>
        <img id="tutorial-image" class="tutorial-image" src="" alt="Tutorial Step">
        <div id="tutorial-text">Welcome to our site! Let's take a quick tour.</div>
        <div class="button-container">
            <button class="prev" onclick="prevStep()" disabled>Previous</button>
            <button class="next" onclick="nextStep()">Next</button>
            <button class="skip" onclick="closeTutorial()">Skip</button>
            <button class="never-show-btn" onclick="neverShowAgain()">Don't show again</button>
        </div>
    </div>
</div>
<?php endif; ?>
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

<section class="welcome">
        <div class="text">
            <h1>WELCOME TO UNIFIED SOEMO!</h1>
            <p>Your ultimate gateway to connecting with student organizations and discovering tailored opportunities. Dive into a dynamic community, stay informed with announcements, and engage in events that spark your interests. Sign in to unlock a world of connections and start your journey with us today!</p>
        </div>
        <div class="image">
            <img src="../assets/pictures/WelcomeIMG.png" alt="University Image">
        </div>
    </section>

 <section class="organizations">
        <h2>STUDENT ORGANIZATION</h2>
        <div class="org-container" id="orgContainer">
        </div>
        <button class="discover-btn" onclick="window.location.href='organizations.php'">DISCOVER MORE</button>
    </section>

<section class="services">
    <h2>OUR SERVICES</h2>
    <div class="services-container">
        <div class="services-image">
            <img src="../assets/pictures/servicesimg.png" alt="Services Image">
        </div>
        <div class="services-text">
            <p>
                Our platform offers a centralized directory of student organizations, real-time updates on campus events, 
                and tools to streamline communication and engagement. It helps students discover opportunities, 
                connect with communities, and stay informed about activities that align with their interests.
            </p>
            <a href="../students/about_us.php" style="text-decoration: none" button class="learn-more-btn">LEARN MORE</button></a>
        </div>
    </div>
</section>

<script>
async function fetchOrgs() {
    try {
        const res = await fetch('../api/get_orgs.php');
        const orgs = await res.json();
        return orgs;
    } catch (error) {
        console.error("Failed to fetch orgs:", error);
        return [];
    }
}

function shuffle(array) {
    return array.sort(() => 0.5 - Math.random());
}

function displayOrgs(orgs) {
    const container = document.getElementById("orgContainer");
    const imagePath = "../assets/uploads_organizations/";
    container.innerHTML = "";

    const selected = shuffle([...orgs]).slice(0, 3);
    selected.forEach(org => {
        const card = document.createElement("div");
        card.className = "org-card";
        card.innerHTML = `
            <div class="org-content">
                <img src="${imagePath + org.img}" alt="${org.name}">
                <h3>${org.name}</h3>
                <p>${org.desc}</p>
            </div>
            <a class="join-btn" href="org_page.php?id=${encodeURIComponent(org.id)}">JOIN NOW</a>
        `;
        container.appendChild(card);
    });
}


let cachedOrgs = [];

async function initOrgs() {
    cachedOrgs = await fetchOrgs();
    displayOrgs(cachedOrgs);
    setInterval(() => displayOrgs(cachedOrgs), 5000);
}

document.addEventListener("DOMContentLoaded", initOrgs);
const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
</script>

<style>
    .session-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50; /* Green by default for success */
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
    background-color: #f44336; /* Red for error */
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

.welcome {
    margin-top: 15px;
}

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

 color: #E74C3C;;
}

.mobile-sidebar {
    display: none;
    position: fixed;
    top: 0;
    left: -280px;
    width: 240px;
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
    padding: 2px 0;
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
    
    .welcome {
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

<script src="../assets/scripts/studentdashboard_script.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>


<?php include '../includes/footer.php'; ?>
