<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? null;
?>

<aside class="sidebar">
  <button class="hamburger-menu" aria-label="Toggle menu">
    <span></span>
    <span></span>
    <span></span>
  </button>
  <div class="logo-container">
    <a href="dashboard.php">
      <img src="../fromOtherBranches/pics/logo.png" alt="Logo" class="logo"/>
    </a>
  </div>

  <nav class="nav-menu">
    <a href="dashboard.php" class="nav-item <?= in_array($current_page, ['dashboard.php', 'Active_org.php', 'past.php', 'upcoming.php']) ? 'active' : '' ?>">
      <img src="../fromOtherBranches/pics/dashboard-icon.png" alt="Dashboard Icon" />
      Dashboard
    </a>

    <a href="new-manage_users.php" class="nav-item <?= $current_page === 'new-manage_users.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/pics/user-icon.png" alt="Users Icon" />
      Manage Users
    </a>

    <?php if ($user_role === 'admin'): ?>
      <a href="new-manage_organizations.php" class="nav-item <?= $current_page === 'new-manage_organizations.php' ? 'active' : '' ?>">
        <img src="../fromOtherBranches/pics/org-icon.png" alt="Organizations Icon" />
        Organizations
      </a>

      <a href="manage-post.php" class="nav-item <?= $current_page === 'manage-post.php' ? 'active' : '' ?>">
        <img src="../assets/pictures/download.png" alt="Manage Posts Icon" />
        Manage Posts
      </a>
    <?php endif; ?>

    <a href="new-manage_events.php" class="nav-item <?= $current_page === 'new-manage_events.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/pics/event-icon.png" alt="Events Icon" />
      Events
    </a>

    <?php if ($user_role === 'orgAdmin'): ?>
      <a href="new-post.php" class="nav-item <?= $current_page === 'new-post.php' ? 'active' : '' ?>">
        <img src="../assets/pictures/download.png" alt="Post Icon" />
        Posts
      </a>
    <?php endif; ?>

    <a href="new_settings.php" class="nav-item <?= $current_page === 'new_settings.php' ? 'active' : '' ?>">
      <img src="../fromOtherBranches/pics/settings-icon.png" alt="Settings Icon" />
      Settings
    </a>
  </nav>
</aside>
