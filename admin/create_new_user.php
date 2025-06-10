<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$admin_name = '';

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
$style = "create_admin.css";
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

<div id="addRecordSection">
    <div class="card mb-4">
        <div class="card-header">
            <h3>Create New User</h3>
        </div>
        <div class="card-body">
    <form id="createAdminForm" method="POST" action="../api/handle_user_create.php">
  <label for="role">Select Role:</label>
  <select id="role" name="role" onchange="updateForm()" required>
    <option value="">-- Select Role --</option>
    <option value="admin">Admin</option>
    <option value="orgAdmin">Org Admin</option>
    <option value="student">Student</option>
  </select>

  <!-- Admin Fields -->
  <div id="adminFields" class="form-section">
    <div class="form-group">
      <label for="adminLastName">Last Name:</label>
      <input type="text" id="adminLastName" name="adminLastName">
    </div>
    <div class="form-group">
      <label for="adminFirstName">First Name:</label>
      <input type="text" id="adminFirstName" name="adminFirstName">
    </div>
    <div class="form-group">
      <label for="adminMiddleName">Middle Name (N/A if not applicable):</label>
      <input type="text" id="adminMiddleName" name="adminMiddleName">
    </div>
    <div class="form-group">
      <label for="adminEmail">PUP Web Mail:</label>
      <input type="email" id="adminEmail" name="adminEmail">
    </div>
    <div class="form-group">
      <label for="adminPassword">Password:</label>
      <div class="input-icon">
        <input type="password" id="adminPassword" name="adminPassword">
        <span class="toggle-icon" onclick="togglePassword('adminPassword', this)">👁️</span>
      </div>
    </div>
    <div class="form-group">
      <label for="adminConfirmPassword">Confirm Password:</label>
      <div class="input-icon">
        <input type="password" id="adminConfirmPassword" name="adminConfirmPassword">
        <span class="toggle-icon" onclick="togglePassword('adminConfirmPassword', this)">👁️</span>
      </div>
      <small id="adminPasswordMismatch" style="color: red; display: none;">Passwords do not match</small>
      <small id="adminPasswordWarning" style="color: red; display: none;">Password must be 8–20 characters, include 2 numbers and 2 special characters.</small>
    </div>
  </div>

  <!-- Org Admin Fields -->
  <div id="orgAdminFields" class="form-section">
    <div class="form-group">
      <label for="orgLastName">Last Name:</label>
      <input type="text" id="orgLastName" name="orgLastName">
    </div>
    <div class="form-group">
      <label for="orgFirstName">First Name:</label>
      <input type="text" id="orgFirstName" name="orgFirstName">
    </div>
    <div class="form-group">
      <label for="orgMiddleName">Middle Name:</label>
      <input type="text" id="orgMiddleName" name="orgMiddleName">
    </div>
    <div class="form-group">
      <label for="orgStudentNumber">Student Number:</label>
      <input type="text" id="orgStudentNumber" name="studentNumber">
    </div>
    <!-- Org Admin Fields -->
<div id="orgAdminFields" class="form-section">
  <!-- existing inputs... -->

  <div class="form-group">
    <label for="orgCourse">Course:</label>
    <select id="orgCourse">
      <option value="">-- Select Course --</option>
      <option value="DCvET">Diploma in Civil Engineering Technology</option>
      <option value="DCET">Diploma in Computer Engineering Technology</option>
      <option value="DEET">Diploma in Electrical Engineering Technology</option>
      <option value="DECET">Diploma in Electronics Engineering Technology</option>
      <option value="DIT">Diploma in Information Technology</option>
      <option value="DMET">Diploma in Mechanical Engineering Technology</option>
      <option value="DOMT">Diploma in Office Management Technology</option>
      <option value="DRET">Diploma in Railway Engineering Technology</option>
    </select>
  </div>

  <div class="form-group">
    <label for="orgYear">Year:</label>
    <select id="orgYear">
      <option value="">-- Select Year --</option>
      <option value="1">1</option>
      <option value="2">2</option>
      <option value="3">3</option>
    </select>
  </div>

  <div class="form-group">
    <label for="orgSection">Section:</label>
    <input type="text" id="orgSection">
  </div>
</div>

    <div class="form-group">
      <label for="orgEmail">PUP Web Mail:</label>
      <input type="email" id="orgEmail" name="orgEmail">
    </div>
    <div class="form-group">
      <label for="orgPassword">Password:</label>
      <div class="input-icon">
        <input type="password" id="orgPassword" name="orgPassword">
        <span class="toggle-icon" onclick="togglePassword('orgPassword', this)">👁️</span>
      </div>
    </div>
    <div class="form-group">
      <label for="orgConfirmPassword">Confirm Password:</label>
      <div class="input-icon">
        <input type="password" id="orgConfirmPassword" name="orgConfirmPassword">
        <span class="toggle-icon" onclick="togglePassword('orgConfirmPassword', this)">👁️</span>
      </div>
      <small id="orgPasswordMismatch" style="color: red; display: none;">Passwords do not match</small>
      <small id="orgPasswordWarning" style="color: red; display: none;">Password must be 8–20 characters, include 2 numbers and 2 special characters.</small>
    </div>
  </div>

  <!-- Student Fields -->
  <div id="studentFields" class="form-section">
    <div class="form-group">
      <label for="studentLastName">Last Name:</label>
      <input type="text" id="studentLastName" name="studentLastName">
    </div>
    <div class="form-group">
      <label for="studentFirstName">First Name:</label>
      <input type="text" id="studentFirstName" name="studentFirstName">
    </div>
    <div class="form-group">
      <label for="studentMiddleName">Middle Name:</label>
      <input type="text" id="studentMiddleName" name="studentMiddleName">
    </div>
    <div class="form-group">
      <label for="studentNumber">Student Number:</label>
      <input type="text" id="studentNumber" name="studentNumber">
    </div>
    <div class="form-group">
      <label for="studentCourse">Course:</label>
      <select id="studentCourse" name="studentCourse">
        <option value="">-- Select Course --</option>
        <option value="DCvET">Diploma in Civil Engineering Technology</option>
        <option value="DCET">Diploma in Computer Engineering Technology</option>
        <option value="DEET">Diploma in Electrical Engineering Technology</option>
        <option value="DECET">Diploma in Electronics Engineering Technology</option>
        <option value="DIT">Diploma in Information Technology</option>
        <option value="DMET">Diploma in Mechanical Engineering Technology</option>
        <option value="DOMT">Diploma in Office Management Technology</option>
        <option value="DRET">Diploma in Railway Engineering Technology</option>
      </select>
    </div>
    <div class="form-group">
      <label for="studentYear">Year:</label>
      <select id="studentYear" name="studentYear">
        <option value="">-- Select Year --</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
      </select>
    </div>
    <div class="form-group">
      <label for="studentSection">Section:</label>
      <input type="text" id="studentSection" name="studentSection">
    </div>
    <div class="form-group">
      <label for="studentEmail">PUP Web Mail:</label>
      <input type="email" id="studentEmail" name="studentEmail">
    </div>
    <div class="form-group">
      <label for="studentPassword">Password:</label>
      <div class="input-icon">
        <input type="password" id="studentPassword" name="studentPassword">
        <span class="toggle-icon" onclick="togglePassword('studentPassword', this)">👁️</span>
      </div>
    </div>
    <div class="form-group">
      <label for="studentConfirmPassword">Confirm Password:</label>
      <div class="input-icon">
        <input type="password" id="studentConfirmPassword" name="studentConfirmPassword">
        <span class="toggle-icon" onclick="togglePassword('studentConfirmPassword', this)">👁️</span>
      </div>
      <small id="studentPasswordMismatch" style="color: red; display: none;">Passwords do not match</small>
      <small id="studentPasswordWarning" style="color: red; display: none;">Password must be 8–20 characters, include 2 numbers and 2 special characters.</small>
    </div>
  </div>
<input type="hidden" name="firstName" id="mainFirstName">
<input type="hidden" name="middleName" id="mainMiddleName">
<input type="hidden" name="lastName" id="mainLastName">
<input type="hidden" name="studentNumber" id="mainStudentNumber">
<input type="hidden" name="email" id="mainEmail">
<input type="hidden" name="password" id="mainPassword">
<input type="hidden" name="confirmPassword" id="mainConfirmPassword">
<input type="hidden" name="course">
<input type="hidden" name="year">
<input type="hidden" name="section">


  <br>
  <div id="submitBtnWrapper">
    <button type="submit" class="btn btn-success">Submit</button>
  </div>
</form>

        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <p>Are you sure you want to create this admin?</p>
        <button class="confirm" onclick="submitForm()">Yes</button>
        <button class="cancel" onclick="hideConfirmModal()">No</button>
    </div>
</div>

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

.modal .confirm, .modal .cancel {
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

.input-icon {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;   
}

.input-icon input {
  width: 100%;            /* Ensure input takes full width */
  padding-right: 36px;    /* Space for the icon */
  border-radius: 14px;    /* Match other inputs */
  border: 2px solid #a3b2d1;
  background-color: #eef4fb;
  color: #2A4365;
  font-weight: 600;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding: 10px 16px;
  text-align: center;
  box-sizing: border-box;
}

.toggle-icon {
  position: absolute;
  right: 12px;
  cursor: pointer;
  user-select: none;
  width: 24px;
  text-align: center;
  font-size: 18px;
  color: #2A4365;
}


</style>

<script>
 document.addEventListener('DOMContentLoaded', () => {
    document.getElementById("adminFields").style.display = "none";
    document.getElementById("orgAdminFields").style.display = "none";
    document.getElementById("studentFields").style.display = "none";
    document.getElementById("submitBtnWrapper").style.display = "none";

    // Add section validation
    document.getElementById('studentSection').addEventListener('input', function(e) {
        if (this.value && !isNaN(this.value) && parseInt(this.value) < 1) {
            this.value = 1;
        }
    });
    document.getElementById('orgSection').addEventListener('input', function(e) {
        if (this.value && !isNaN(this.value) && parseInt(this.value) < 1) {
            this.value = 1;
        }
    });
  });

  function updateForm() {
    const role = document.getElementById("role").value;

    const adminSection = document.getElementById("adminFields");
    const orgAdminSection = document.getElementById("orgAdminFields");
    const studentSection = document.getElementById("studentFields");
    const submitWrapper = document.getElementById("submitBtnWrapper");

    adminSection.style.display = "none";
    orgAdminSection.style.display = "none";
    studentSection.style.display = "none"; // Hide by default
    submitWrapper.style.display = "none";

    // Remove all required attributes
const allInputs = document.querySelectorAll("#adminFields input, #orgAdminFields input, #studentFields input");
allInputs.forEach(input => input.required = false);
function setupPasswordValidation(passwordId, warningId) {
  const password = document.getElementById(passwordId);
  const warning = document.getElementById(warningId);

  password.addEventListener('input', () => {
    const value = password.value;
    const lengthValid = value.length >= 8 && value.length <= 20;
    const numbers = (value.match(/\d/g) || []).length;
    const specials = (value.match(/[^A-Za-z0-9]/g) || []).length;
    const valid = lengthValid && numbers >= 2 && specials >= 2;

    warning.style.display = valid ? 'none' : 'inline';
  });
}

    // Show section based on selected role
if (role === "admin") {
  adminSection.style.display = "block";
  adminSection.querySelectorAll("input").forEach(input => input.required = true);
  submitWrapper.style.display = "block";
  setupPasswordMatching("adminPassword", "adminConfirmPassword", "adminPasswordMismatch");
  setupPasswordValidation("adminPassword", "adminPasswordWarning");
} else if (role === "orgAdmin") {
  orgAdminSection.style.display = "block";
  orgAdminSection.querySelectorAll("input").forEach(input => input.required = true);
  submitWrapper.style.display = "block";
  setupPasswordMatching("orgPassword", "orgConfirmPassword", "orgPasswordMismatch");
  setupPasswordValidation("orgPassword", "orgPasswordWarning");
} else if (role === "student") {
  studentSection.style.display = "block";
  studentSection.querySelectorAll("input").forEach(input => input.required = true);
  submitWrapper.style.display = "block";
  setupPasswordMatching("studentPassword", "studentConfirmPassword", "studentPasswordMismatch");
  setupPasswordValidation("studentPassword", "studentPasswordWarning");
}

}
function isValidEmailByRole(email, role) {
  if (!email) return false;

  if (role === 'admin') {
    return /^[^@\s]+@pup\.edu\.ph$/.test(email);
  } else {
    return /^[^@\s]+@iskolarngbayan\.pup\.edu\.ph$/.test(email);
  }
}
  function validateFormAndShowModal(event) {
    event.preventDefault();

    const role = document.getElementById("role").value;
    let lastNameInput,firstNameInput,middleNameInput, emailInput, passwordInput;

    if (role === "admin") {
      lastNameInput = document.getElementById("adminLastName");
      firstNameInput = document.getElementById("adminFirstName");
      middleNameInput = document.getElementById("adminMiddleName");
      emailInput = document.getElementById("adminEmail");
      passwordInput = document.getElementById("adminPassword");
    } else if (role === "orgAdmin") {
      lastNameInput = document.getElementById("orgLastName");
      firstNameInput = document.getElementById("orgFirstName");
      middleNameInput = document.getElementById("orgMiddleName");
      emailInput = document.getElementById("orgEmail");
      passwordInput = document.getElementById("orgPassword");
    } else if (role === "student") {
    lastNameInput = document.getElementById("studentLastName");
    firstNameInput = document.getElementById("studentFirstName");
    middleNameInput = document.getElementById("studentMiddleName");
    emailInput = document.getElementById("studentEmail");
    passwordInput = document.getElementById("studentPassword");
    confirmPasswordInput = document.getElementById("studentConfirmPassword");
    } else {
      showAlert("Please select a role.");
      return;
    }

    const fullName = `${firstNameInput.value.trim()} ${middleNameInput.value.trim()} ${lastNameInput.value.trim()}`.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();

    if (!fullName || !email || !password) {
      showAlert("Fullname, email, and password are required.");
      return;
    }

    if (!isValidEmailByRole(email, role)) {
    showAlert(role === 'admin' ? "Admin email must end with @pup.edu.ph" : "Email must be @iskolarngbayan.pup.edu.ph");
    return;
    }


    showConfirmModal();
  }

  function showConfirmModal() {
    document.getElementById('confirmModal').style.display = 'block';
  }

  function hideConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
  }

  function submitForm() {
  const role = document.getElementById('role').value;

  if (role === "admin") {
    document.getElementById("mainFirstName").value = document.getElementById("adminFirstName").value;
    document.getElementById("mainMiddleName").value = document.getElementById("adminMiddleName").value;
    document.getElementById("mainLastName").value = document.getElementById("adminLastName").value;
    document.getElementById("mainEmail").value = document.getElementById("adminEmail").value;
    document.getElementById("mainPassword").value = document.getElementById("adminPassword").value;
    document.getElementById("mainConfirmPassword").value = document.getElementById("adminConfirmPassword").value;
  } else if (role === "orgAdmin") {
    document.getElementById("mainFirstName").value = document.getElementById("orgFirstName").value;
    document.getElementById("mainMiddleName").value = document.getElementById("orgMiddleName").value;
    document.getElementById("mainLastName").value = document.getElementById("orgLastName").value;
    document.getElementById("mainStudentNumber").value = document.getElementById("orgStudentNumber").value;
    document.getElementById("mainEmail").value = document.getElementById("orgEmail").value;
    document.getElementById("mainPassword").value = document.getElementById("orgPassword").value;
    document.getElementById("mainConfirmPassword").value = document.getElementById("orgConfirmPassword").value;

    document.getElementsByName("course")[0].value = document.getElementById("orgCourse").value;
    document.getElementsByName("year")[0].value = document.getElementById("orgYear").value;
    document.getElementsByName("section")[0].value = document.getElementById("orgSection").value;
  } else if (role === "student") {
    document.getElementById("mainFirstName").value = document.getElementById("studentFirstName").value;
    document.getElementById("mainMiddleName").value = document.getElementById("studentMiddleName").value;
    document.getElementById("mainLastName").value = document.getElementById("studentLastName").value;
    document.getElementById("mainEmail").value = document.getElementById("studentEmail").value;
    document.getElementById("mainPassword").value = document.getElementById("studentPassword").value;
    document.getElementById("mainConfirmPassword").value = document.getElementById("studentConfirmPassword").value;
    document.getElementById("mainStudentNumber").value = document.getElementById("studentNumber").value;
    document.getElementsByName("course")[0].value = document.getElementById("studentCourse").value;
    document.getElementsByName("year")[0].value = document.getElementById("studentYear").value;
    document.getElementsByName("section")[0].value = document.getElementById("studentSection").value;

  }

  // Now submit the form
  document.getElementById('createAdminForm').submit();
}


  function showAlert(message) {
    const alertBox = document.createElement('div');
    alertBox.className = 'floating-alert';
    alertBox.innerText = message;
    document.body.appendChild(alertBox);
    setTimeout(() => alertBox.remove(), 5000);
  }

  document.getElementById('createAdminForm').addEventListener('submit', validateFormAndShowModal);

  function setupPasswordMatching(passwordId, confirmId, messageId) {
  const password = document.getElementById(passwordId);
  const confirm = document.getElementById(confirmId);
  const message = document.getElementById(messageId);

  function checkMatch() {
    if (confirm.value === "") {
      message.style.display = "none";
    } else if (password.value === confirm.value) {
      message.style.display = "none";
    } else {
      message.style.display = "inline";
    }
  }

  password.addEventListener('input', checkMatch);
  confirm.addEventListener('input', checkMatch);
}
function togglePassword(inputId, iconElement) {
  const input = document.getElementById(inputId);
  const isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  iconElement.textContent = isHidden ? '🙈' : '👁️';
}

</script>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
