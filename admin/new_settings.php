<?php 
require '../api/auth.php';

checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get total users excluding admin
$sql = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

// Get total organizations
$sql_orgs = "SELECT COUNT(*) AS total_organizations FROM organizations";
$result_orgs = $conn->query($sql_orgs);
$total_organizations = $result_orgs->fetch_assoc()['total_organizations'];

// Get upcoming events
$sql_events = "SELECT COUNT(*) AS total_events FROM events WHERE event_date >= CURDATE()";
$result_events = $conn->query($sql_events);
$total_events = $result_events->fetch_assoc()['total_events'];

$sql_past = "SELECT COUNT(*) AS past_events FROM events WHERE event_date < CURDATE()";
$result_past = $conn->query($sql_past);
$past_events = $result_past->fetch_assoc()['past_events'];

// Get recent events
$sql_recent_events = "SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 5";
$result_recent_events = $conn->query($sql_recent_events);

$recent_events = [];
while ($row = $result_recent_events->fetch_assoc()) {
    $recent_events[] = $row;
}

// Get logged-in admin's data
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';
$admin_email = '';
$admin_profile_picture = '../assets/uploads_pfp/profile.png'; // default image

if ($admin_id) {
    $sql_admin = "SELECT fullName, email, profile_picture FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $admin_data = $result_admin->fetch_assoc();
        $admin_name = ucwords(strtolower($admin_data['fullName']));
        $admin_email = strtolower($admin_data['email']);
        $pfp_filename = $admin_data['profile_picture'];
        $pfp_path = "../assets/uploads_pfp/" . $pfp_filename;
        if (!empty($pfp_filename) && file_exists($pfp_path)) {
            $admin_profile_picture = $pfp_path;
        }
    }
    $stmt->close();
}

$conn->close();
    
$title = "Unified SOEMO Dashboard";
$style = "new_settings.css";
include '../includes/header.php';
?>

<!-- Hamburger Menu -->
<button class="hamburger-menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="outer-box">
    <h2 class="section-title">EDIT PROFILE</h2>

<form action="../api/admin-setting.php" method="POST" enctype="multipart/form-data">
    <div class="inner-card">
      <div class="card-section upload-section">
        <h3>Organization Logo</h3>
        <div class="upload-frame">
            <label for="logo-upload" class="upload-label">
                <!-- Show organization logo from DB or default -->
                <img src="<?= htmlspecialchars($admin_profile_picture) ?>" alt="Admin Profile Picture" />
                <span class="upload-text">Upload Media</span>
            </label>
            <input type="file" id="logo-upload" name="logo" accept="image/*" />
        </div>
      </div>

      <div class="card-section">
        <h3>Account Settings</h3>

        <!-- Full Name -->
        <div class="card-row" data-field="fullName">
          <div class="card-label">Full Name:</div>
          <div class="card-value" id="display-fullName"><?= htmlspecialchars($admin_name) ?></div>
          <textarea class="card-input d-none" id="input-fullName" name="fullName" rows="2"><?= htmlspecialchars($admin_name) ?></textarea>
          <div class="card-action">
            <span class="edit-text" onclick="startEdit('fullName')" role="button" tabindex="0">[Change Full Name] ✎</span>
          </div>
        </div>

        <!-- Email -->
        <div class="card-row" data-field="email">
          <div class="card-label">Email:</div>
          <div class="card-value" id="display-email"><?= htmlspecialchars($admin_email) ?></div>
          <input type="email" class="card-input d-none" id="input-email" name="email" value="<?= htmlspecialchars($admin_email) ?>" />
          <div class="card-action">
            <span class="edit-text" onclick="startEdit('email')" role="button" tabindex="0">[Change Email] ✎</span>
          </div>
        </div>

        <!-- Password -->
        <div class="card-row" data-field="password">
          <div class="card-label">Password:</div>
          <div class="card-value" id="display-password">••••••••</div>
          <input type="password" class="card-input d-none" id="input-password" name="password" placeholder="Enter new password" />
          <div class="card-action">
            <span class="edit-text" onclick="startEdit('password')" role="button" tabindex="0">[Change Password] ✎</span>
          </div>
        </div>

      </div>      

    </div>

    <!-- Global Save/Cancel Buttons -->
    <div class="action-buttons">
      <button class="save-btn" type="submit">Save Changes</button>
      <button class="cancel-btn" type="button" onclick="window.location.reload()">Cancel</button>
    </div>
  </form>
</div>

<script>
  function elems(field) {
    return {
      display: document.getElementById('display-' + field),
      input: document.getElementById('input-' + field),
      editText: document.querySelector(`.card-row[data-field="${field}"] .edit-text`),
    };
  }

  function startEdit(field) {
    const { display, input, editText } = elems(field);
    display.classList.add('d-none');
    input.classList.remove('d-none');
    editText.classList.add('d-none');
    input.focus();
  }

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
  .d-none { display: none; }
  .edit-text {
    cursor: pointer;
    color: #007bff;
    user-select: none;
    font-family: monospace;
  }
  .edit-text:hover, .edit-text:focus {
    text-decoration: underline;
    outline: none;
  }
  .card-row {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
  }
  .card-label {
    width: 120px;
    font-weight: 600;
  }
  .card-value, .card-input {
    flex: 1;
  }
  .card-input {
    font-size: 1rem;
    padding: 5px;
    resize: vertical;
  }
  .card-action {
    margin-left: 15px;
    min-width: 160px;
  }
  .card-action button {
    margin-right: 5px;
  }
  .action-buttons {
    margin-top: 30px;
  }
  .action-buttons button {
    padding: 10px 20px;
    margin-right: 10px;
  }
  .session-alert {
    position: fixed;
    top: 20px;
    left: 55%;
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
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<script src="../assets/scripts/hamburger.js"></script>
<?php include '../includes/footer.php'; ?>
