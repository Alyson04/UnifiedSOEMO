<?php
// Default values
$display_name = 'Guest';
$role_label = '';
$profile_img = '../assets/uploads_pfp/profile.png'; // Default profile pic

// If user is logged in
if (isset($_SESSION['user_id'])) {
    include '../config/db_conn.php';
    $user_id = $_SESSION['user_id'];

    // Fetch user info
    $stmt = $conn->prepare("SELECT fullName, role, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $display_name = ucwords(strtolower($row['fullName']));
        $role = $row['role'];
        $role_label = $role === 'admin' ? 'Admin' : ($role === 'student' ? 'Student' : 'Org Admin');

        // Use profile picture only if student and picture exists
        if ($role === 'student' && !empty($row['profile_picture'])) {
            $uploaded_path = "../assets/uploads_pfp/" . $row['profile_picture'];
            if (file_exists($uploaded_path)) {
                $profile_img = $uploaded_path;
            }
        }
    }

    $stmt->close();
}
?>

<!-- Unified Header -->
<header class="top-bar <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'org_admin') echo 'admin-navbar'; ?>">

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <div class="logo">
            <img src="../assets/pictures/logo.png" alt="Unified SOEMO Logo">
        </div>
        <ul class="nav-list">
            <li><a href="dashboard.php">HOME</a></li>
            <li><a href="organizations.php">ORGANIZATIONS</a></li>
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
                <span><?= $role_label; ?></span>
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
