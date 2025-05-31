<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

// Fetch admin's full name
if ($admin_id) {
    $sql_admin = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $admin_name = ucwords(strtolower($result_admin->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

// Optional role filter
$role_filter = $_GET['role'] ?? '';
$sql = "SELECT id, fullName, email, role, created_at FROM users WHERE role != 'admin'";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role_filter);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

$title = "Unified SOEMO Dashboard";
$style = "create_orgadmin.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div id="addRecordSection">
  <div class="card">
    <div class="card-header">
      <h3>Create an Organization Admin</h3>
    </div>
    <div class="card-body">
      <form id="form1">
        <div class="form-group">
          <label for="fullName">Fullname:</label>
          <input type="text" id="fullName" name="fullName" required />
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" required />
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" required />
        </div>
        <input type="hidden" id="role" name="role" value="org_admin" />
        <input type="hidden" id="is_approved" name="is_approved" value="approved" />
      </form>

      <form id="form2">
        <div class="form-group">
          <label for="name">Name of Organization:</label>
          <input type="text" id="name" name="name" required />
        </div>
        <div class="form-group">
          <label for="description">Short Description:</label>
          <input type="text" id="description" name="description" required />
        </div>
      </form>

      <form id="form3" enctype="multipart/form-data">
        <div class="form-group">
          <label for="objectives">Introduction:</label>
          <input type="text" id="objectives" name="objectives" required />
        </div>
        <div class="form-group">
          <label for="skills">Skills:</label>
          <input type="text" id="skills" name="skills" required />
        </div>
        <div class="form-group">
          <label for="requirements">Requirements:</label>
          <input type="text" id="requirements" name="requirements" required />
        </div>
        <div class="form-group">
          <label for="image">Organization Logo:</label>
          <input type="file" name="image" accept="image/*" required/>
        </div>
      </form>

      <button type="button" onclick="showConfirmModal()" class="btn btn-success">Create Organization</button>
      <button type="button" onclick="history.back()" class="btn-cancel">Cancel</button>
    </div>
  </div>
</div>

<!-- Alert box -->
<div id="formError" class="floating-alert" style="display: none;"></div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal">
    <p>Are you sure you want to create an organization?</p>
    <button class="confirm" onclick="submitAllForms()">Yes</button>
    <button class="cancel" onclick="hideConfirmModal()">No</button>
  </div>
</div>

<!-- Custom styles -->
<style>
  .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .modal button {
            margin: 10px 5px 0;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal .confirm {
            background: linear-gradient(135deg, #36577d, #2A4365);
            color: white;
        }

        .modal .cancel {
            background: linear-gradient(135deg, #36577d, #2A4365);
            color: white;
        }
  .floating-alert {
    position: fixed;
    top: 50px;
    right: 30%;
    transform: translateX(-50%);
    background-color: #f44336;
    color: white;
    padding: 14px 20px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    animation: fadeIn 0.3s ease-in-out;
    max-width: 90%;
    text-align: center;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translate(-50%, -20px); }
    to { opacity: 1; transform: translate(-50%, 0); }
  }
</style>

<!-- Confirmation & form logic -->
<script>
function showConfirmModal() {
    const inputs = [
        ...document.querySelectorAll('#form1 input[required]'),
        ...document.querySelectorAll('#form2 input[required]'),
        ...document.querySelectorAll('#form3 input[required]')
    ];

    const emptyFields = inputs.filter(input => input.value.trim() === '');
    if (emptyFields.length > 0) {
        showFormError("Please fill out all required fields.");
        return;
    }

    const email = document.getElementById('email').value.trim();

    // ✅ Email format check only
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showFormError("Please enter a valid email address.");
        return;
    }

    // ✅ Show confirmation modal if everything is valid
    document.getElementById('confirmModal').style.display = 'block';
}


function hideConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function showFormError(message) {
    const errorBox = document.getElementById("formError");
    errorBox.textContent = message;
    errorBox.style.display = "block";

    setTimeout(() => {
        errorBox.style.display = "none";
    }, 3000);
}

function submitAllForms() {
    // Call your actual submit function here
    if (typeof submitCombinedForms === 'function') {
        submitCombinedForms();
    } else {
        console.error("submitCombinedForms() not defined.");
    }

    hideConfirmModal();
}
</script>


<script src="../assets/scripts/createorg_script.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
