<?php
require '../api/auth.php';
require '../config/db_conn.php';

$student_id = $_SESSION['user_id'] ?? null;
$org_name = '';
$org_id = $_GET['id'] ?? null;

$firstName = '';
$middleName = '';
$lastName = '';
$email = '';
$studentNumber = '';
$course = '';
$year = '';
$section = '';

if ($student_id) {
    $stmt = $conn->prepare("SELECT firstName, middleName, lastName, email, studentNumber, course, year, section FROM newusers WHERE ID = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $firstName = $row['firstName'] ?? '';
        $middleName = $row['middleName'] ?? '';
        $lastName = $row['lastName'] ?? '';
        $email = $row['email'] ?? '';
        $studentNumber = $row['studentNumber'] ?? '';
        $course = $row['course'] ?? '';
        $year = $row['year'] ?? '';
        $section = $row['section'] ?? '';
    }
    $stmt->close();
}

if ($org_id) {
    $stmt = $conn->prepare("SELECT name FROM neworganizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_name = $result->fetch_assoc()['name'];
    }
    $stmt->close();
}

$conn->close();
$title = "Join Organization";
$style = "joinorg_styles.css";
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
  <div class="join-container">
      <h2 class="section-title">Join Organization</h2>
      <form action="../api/submit_join.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">

          <label>Organization Name:</label>
          <input type="text" value="<?= htmlspecialchars($org_name) ?>" readonly>

          <label>First Name:</label>
          <input type="text" name="firstName" value="<?= htmlspecialchars($firstName) ?>" readonly>

          <label>Middle Name:</label>
          <input type="text" name="middleName" value="<?= htmlspecialchars($middleName) ?>" readonly>

          <label>Last Name:</label>
          <input type="text" name="lastName" value="<?= htmlspecialchars($lastName) ?>" readonly>

          <label>Email:</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>

          <label>Student Number:</label>
          <input type="text" name="studentNumber" value="<?= htmlspecialchars($studentNumber) ?>" readonly>

          <label>Course:</label>
          <input type="text" name="course" value="<?= htmlspecialchars($course) ?>" readonly>

          <label>Year:</label>
          <input type="text" name="year" value="<?= htmlspecialchars($year) ?>" readonly>

          <label>Section:</label>
          <input type="text" name="section" value="<?= htmlspecialchars($section) ?>" readonly>

          <!-- <label for="portfolio">Upload Portfolio / Required Files (PDF, DOCX, JPG, PNG):</label>
          <input type="file" name="portfolio_file" id="portfolio" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"> -->

          <div class="form-buttons">
              <button type="submit">Submit Application</button>
              <a href="org_page.php?id=<?= urlencode($org_id) ?>" class="cancel-button">Cancel</a>
          </div>
      </form>
  </div>
</div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
