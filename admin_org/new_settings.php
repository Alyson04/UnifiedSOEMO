<?php 
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow org admins

require '../config/db_conn.php';

// Fetch the logged-in user's ID
$user_id = $_SESSION['user_id'] ?? null;

$org_logo = '../assets/default-logo.png'; // Default logo if none found

if ($user_id) {
    // Get the organization logo for the user's organization
    // Assumes neworganizations.user_id links to newusers.ID
    $sql_org = "
        SELECT o.image_path 
        FROM neworganizations o
        JOIN newusers u ON o.user_id = u.ID
        WHERE u.ID = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_org = $stmt->get_result();

    if ($result_org->num_rows > 0) {
        $row = $result_org->fetch_assoc();
        if (!empty($row['image_path'])) {
            $logo_path_candidate = '../assets/uploads_organizations/' . basename($row['image_path']);
            if (file_exists($logo_path_candidate)) {
                $org_logo = $logo_path_candidate;
            }
        }
    }
    $stmt->close();
}

// Fetch admin full name and email
$admin_name = '';
$admin_email = '';
if ($user_id) {
    $sql_admin = "SELECT firstName, middleName, lastName, email FROM newusers WHERE ID = ?";
    $stmt2 = $conn->prepare($sql_admin);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result_admin = $stmt2->get_result();
    if ($result_admin->num_rows > 0) {
        $row_admin = $result_admin->fetch_assoc();

        // Concatenate name parts into full name
        $first = ucfirst(strtolower($row_admin['firstName']));
        $middle = ucfirst(strtolower($row_admin['middleName']));
        $last = ucfirst(strtolower($row_admin['lastName']));
      
        $admin_email = strtolower($row_admin['email']);
    }
    $stmt2->close();
}

$conn->close();

$title = "Settings";
$style = "new_settings.css";
include '../includes/header.php';
?>

<!-- Add shared CSS for admin_org section -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_shared.css">

<!-- Hamburger Menu Button -->
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

  <form action="../api/org-setting.php" method="POST" enctype="multipart/form-data">
    <div class="inner-card">
      <div class="card-section upload-section">
        <h3>Organization Logo</h3>
        <div class="upload-frame">
            <label for="logo-upload" class="upload-label">
                <!-- Show organization logo from DB or default -->
                <img src="<?= htmlspecialchars($org_logo) ?>" alt="Organization Logo" style="max-width: 200px;" />
                <span class="upload-text">Upload Media</span>
            </label>
            <input type="file" id="logo-upload" name="logo" accept="image/*" />
        </div>
      </div>

      <div class="card-section">
        <h3>Account Settings</h3>

<!-- First Name -->
<div class="card-row" data-field="firstName">
  <div class="card-label">First Name:</div>
  <div class="card-value" id="display-firstName"><?= htmlspecialchars($first) ?></div>
  <input type="text" class="card-input d-none" id="input-firstName" name="firstName" value="<?= htmlspecialchars($first) ?>" />
  <div class="card-action">
    <span class="edit-text" onclick="startEdit('firstName')" role="button" tabindex="0">[Edit First Name] ✎</span>
  </div>
</div>

<!-- Middle Name -->
<div class="card-row" data-field="middleName">
  <div class="card-label">Middle Name:</div>
  <div class="card-value" id="display-middleName"><?= htmlspecialchars($middle) ?></div>
  <input type="text" class="card-input d-none" id="input-middleName" name="middleName" value="<?= htmlspecialchars($middle) ?>" />
  <div class="card-action">
    <span class="edit-text" onclick="startEdit('middleName')" role="button" tabindex="0">[Edit Middle Name] ✎</span>
  </div>
</div>

<!-- Last Name -->
<div class="card-row" data-field="lastName">
  <div class="card-label">Last Name:</div>
  <div class="card-value" id="display-lastName"><?= htmlspecialchars($last) ?></div>
  <input type="text" class="card-input d-none" id="input-lastName" name="lastName" value="<?= htmlspecialchars($last) ?>" />
  <div class="card-action">
    <span class="edit-text" onclick="startEdit('lastName')" role="button" tabindex="0">[Edit Last Name] ✎</span>
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
        
        <div class="card-row d-none" data-field="confirmPassword" id="confirm-password-row" style="display:none;">
          <div class="card-label">Confirm Password:</div>
          <input type="password" class="card-input" id="input-confirm-password" name="confirmPassword" placeholder="Confirm new password" />
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

  // Show confirm password field if editing password
  if (field === 'password') {
    const confirmRow = document.getElementById('confirm-password-row');
    if (confirmRow) {
      confirmRow.classList.remove('d-none');
      confirmRow.style.display = "flex";
    }
  }
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
<script src="../assets/scripts/admin_org_shared.js"></script>
<script src="../assets/scripts/admin_org_mobile.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
