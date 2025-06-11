<?php
// Default values
$display_name = 'Guest';
$role_label = 'Guest';
$profile_img = '../assets/uploads_pfp/profile.png'; // fallback image
$role = 'guest';
$is_guest = true;

// If user is logged in
if (isset($_SESSION['user_id'])) {
    include '../config/db_conn.php';
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT firstName, middleName, lastName, role, profile_picture FROM newusers WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $firstName = $row['firstName'] ?? '';
        $middleName = $row['middleName'] ?? '';
        $lastName = $row['lastName'] ?? '';
        $display_name = ucwords(strtolower(trim("$firstName " . ($middleName && strtolower($middleName) !== 'n/a' ? "$middleName " : '') . $lastName)));

        $role = $row['role'];
        $role_label = $role === 'admin' ? 'Admin' : ($role === 'student' ? 'Student' : 'Org Admin');
        $is_guest = false;

        // Profile picture logic
        if (!empty($row['profile_picture'])) {
            $candidate_path = '../assets/uploads_pfp/' . $row['profile_picture'];
            if (file_exists($candidate_path)) {
                $profile_img = $candidate_path;
            }
        }
    }

    $stmt->close();
}

// Determine home page link based on role
$home_link = 'index.php';
if (!$is_guest && $role === 'student') {
    $home_link = 'dashboard.php';
}
?>

<!-- Unified Header -->
<header class="top-bar <?php if (!$is_guest && ($role === 'admin' || $role === 'org_admin')) echo 'admin-navbar'; ?>">
    <?php if ($role === 'student' || $is_guest): ?>
        <div class="logo">
            <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo">
        </div>
        <ul class="nav-list">
            <li><a href="<?= $home_link ?>">HOME</a></li>
            <li><a href="organizations.php">ORGANIZATIONS</a></li>
            <?php if (!$is_guest): ?>
                <li><a href="new-post.php">POSTS</a></li>
                <li><a href="events.php">EVENTS</a></li>
            <?php endif; ?>
            <li><a href="about_us.php">ABOUT US</a></li>
        </ul>
    <?php else: ?>
        <section class="dashboard-header">
            <h1>UNIFIED SOEMO</h1>
            <p>DISCOVER, JOIN, ENGAGE</p>
        </section>
    <?php endif; ?>

    <div class="top-right">
        <?php if (!$is_guest) include 'notification_modal.php'; ?>
        <div class="profile" onclick="toggleProfileDropdown()">
            <img src="<?= htmlspecialchars($profile_img); ?>" alt="profile picture" />
            <div class="profile-info">
                <strong><?= htmlspecialchars($display_name); ?></strong>
                <span><?= htmlspecialchars($role_label); ?></span>
            </div>
            <div class="dropdown-tray" id="profileDropdown">
                <?php if ($role === 'student'): ?>
                    <a href="edit_profile.php">Edit Profile</a>
                <?php endif; ?>
                <?php if (!$is_guest): ?>
                    <a href="../api/logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
