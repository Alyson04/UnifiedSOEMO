<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? null;
?>

<aside class="sidebar">
  <div class="logo-container">
    <img src="../fromOtherBranches/Revised admin/pics/logo.png" alt="Logo" class="logo"/>
  </div>
  <nav class="nav-menu">
    <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' || $current_page === 'Active_org.php' || $current_page === 'past.php' || $current_page === 'upcoming.php'  ? 'active' : '' ?>">
      <img src="../fromOtherBranches/Revised admin/pics/dashboard-icon.png" alt="Dashboard Icon" />
      Dashboard
    </a>
    <a href="new-manage_users.php" class="nav-item <?= $current_page === 'new-manage_users.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/Revised admin/pics/user-icon.png" alt="Users Icon" />
      Manage Users
    </a>
    <?php if ($user_role === 'admin'): ?>
    <a href="new-manage_organizations.php" class="nav-item <?= $current_page === 'new-manage_organizations.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/Revised admin/pics/org-icon.png" alt="Organizations Icon" />
      Organizations
    </a>
    <?php endif; ?>
    <a href="new-manage_events.php" class="nav-item <?= $current_page === 'new-manage_events.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/Revised admin/pics/event-icon.png" alt="Events Icon" />
      Events
    </a>
    <?php if ($user_role === 'org_admin'): ?>
    <a href="post.php" class="nav-item <?= $current_page === 'post.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/Revised admin/pics/post.png" alt="Post Icon" />
      Posts
    </a>
    <?php endif; ?>
    <a href="new_settings.php" class="nav-item <?= $current_page === 'new_settings.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/Revised admin/pics/settings-icon.png" alt="Settings Icon" />
      Settings
    </a>
  </nav>
</aside>
