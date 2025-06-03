<?php
// Default values
$display_name = 'Guest';
$role_label = '';
$profile_img = '../assets/uploads_pfp/profile.png'; // Default profile pic

// Check if hamburger menu has already been rendered
if (!defined('HAMBURGER_RENDERED')):
    define('HAMBURGER_RENDERED', true);
    ?>
    <!-- Add Mobile Sidebar CSS, Font Awesome and Sidebar JavaScript -->
    <link rel="stylesheet" href="../assets/stylesheets/mobile_sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/sidebar.js" defer></script>
    <?php
endif;

// If user is logged in
if (isset($_SESSION['user_id'])) {
    include '../config/db_conn.php';
    $user_id = $_SESSION['user_id'];

    // Fetch user info and role
    $stmt = $conn->prepare("SELECT fullName, role, profile_picture, org_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $display_name = ucwords(strtolower($row['fullName']));
        $role = $row['role'];
        $user_org_id = $row['org_id'] ?? null;  // renamed here
        $role_label = $role === 'admin' ? 'Admin' : ($role === 'student' ? 'Student' : 'Org Admin');

        // For admin and student, check if profile picture exists
        if (!empty($row['profile_picture'])) {
            $uploaded_path = "../assets/uploads_pfp/" . $row['profile_picture'];
            if (file_exists($uploaded_path)) {
                $profile_img = $uploaded_path;
            }
        } elseif ($role === 'org_admin' && $user_org_id !== null) {
            // Fetch organization's profile picture filename
            $stmt_org = $conn->prepare("SELECT image_path FROM organizations WHERE id = ?");
            $stmt_org->bind_param("i", $user_org_id);  // use renamed variable here
            $stmt_org->execute();
            $result_org = $stmt_org->get_result();

            if ($result_org && $org_row = $result_org->fetch_assoc()) {
                $org_img_file = $org_row['image_path'];
                $org_img_path = "../assets/uploads_organizations/" . $org_img_file;
                if (!empty($org_img_file) && file_exists($org_img_path)) {
                    $profile_img = $org_img_path;
                }
            }
            $stmt_org->close();
        }
    }
    $stmt->close();
}

// Show mobile menu and sidebar only for students
if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
    <!-- Mobile Menu Button -->
    <button class="hamburger" onclick="toggleSidebar()" type="button" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="sidebar-close" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </div>
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars($profile_img); ?>" alt="profile picture" />
            <div class="sidebar-profile-info">
                <strong><?= htmlspecialchars($display_name); ?></strong>
                <span><?= htmlspecialchars($role_label); ?></span>
            </div>
        </div>
        <ul class="sidebar-list">
            <li><a href="dashboard.php">HOME</a></li>
            <li><a href="organizations.php">ORGANIZATIONS</a></li>
            <li><a href="new-post.php">POSTS</a></li>
            <li><a href="events.php">EVENTS</a></li>
            <li><a href="about_us.php">ABOUT US</a></li>
            <li><a href="../students/edit_profile.php">Edit Profile</a></li>
            <li><a href="../api/logout.php">Logout</a></li>
        </ul>
    </div>
<?php endif; ?>

<!-- Unified Header -->
<header class="top-bar <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'org_admin')) echo 'admin-navbar'; ?>">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <div class="logo">
            <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo">
        </div>
        <ul class="nav-list">
            <li><a href="dashboard.php">HOME</a></li>
            <li><a href="organizations.php">ORGANIZATIONS</a></li>
            <li><a href="new-post.php">POSTS</a></li>
            <li><a href="events.php">EVENTS</a></li>
            <li><a href="about_us.php">ABOUT US</a></li>
        </ul>
    <?php else: ?>
        <section class="dashboard-header">
            <h1>UNIFIED SOEMO</h1>
            <p>DISCOVER, JOIN, ENGAGE</p>
        </section>
    <?php endif; ?>

    <div class="top-right">
        <?php include 'notification_modal.php'; ?>
        <div class="profile" onclick="toggleProfileDropdown()">
            <img src="<?= htmlspecialchars($profile_img); ?>" alt="profile picture" />
            <div class="profile-info">
                <strong><?= htmlspecialchars($display_name); ?></strong>
                <span><?= htmlspecialchars($role_label); ?></span>
            </div>
            <div class="dropdown-tray" id="profileDropdown" style="display: none;">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                    <a href="../students/edit_profile.php">Edit Profile</a>
                <?php endif; ?>
                <a href="../api/logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>
