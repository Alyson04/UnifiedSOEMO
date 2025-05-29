<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow org admins

require '../config/db_conn.php';

// Fetch the logged-in user's ID
$user_id = $_SESSION['user_id'] ?? null;

$org_logo = '../assets/default-logo.png'; // Default logo if none found

if ($user_id) {
    // Get the organization logo for the user's organization
    // Assumes users.organization_id links to organizations.id
    $sql_org = "
        SELECT o.image_path 
        FROM organizations o
        JOIN users u ON u.org_id = o.id
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
    $sql_admin = "SELECT fullName, email FROM users WHERE ID = ?";
    $stmt2 = $conn->prepare($sql_admin);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result_admin = $stmt2->get_result();
    if ($result_admin->num_rows > 0) {
        $row_admin = $result_admin->fetch_assoc();
        $admin_name = ucwords(strtolower($row_admin['fullName']));
        $admin_email = strtolower($row_admin['email']);
    }
    $stmt2->close();
}

$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "new-settings.css";
include '../includes/header.php';
?>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

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
</style>
<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
