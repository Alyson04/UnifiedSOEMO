<?php
require '../api/auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Get org admin's organization ID
$user_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id && $user_id) {
    $sql_org = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_id = $result->fetch_assoc()['id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $middleName = $_POST['middleName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $studentNumber = $_POST['studentNumber'] ?? '';
    $course = $_POST['course'] ?? '';
    $year = $_POST['year'] ?? '';
    $section = $_POST['section'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($studentNumber) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: create_new_user.php");
        exit;
    }

    // Validate password
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: create_new_user.php");
        exit;
    }

    if (!preg_match("/[A-Z]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one uppercase letter.";
        header("Location: create_new_user.php");
        exit;
    }

    if (!preg_match("/[a-z]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one lowercase letter.";
        header("Location: create_new_user.php");
        exit;
    }

    if (!preg_match("/[0-9]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one number.";
        header("Location: create_new_user.php");
        exit;
    }

    if (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
        $_SESSION['error'] = "Password must contain at least one special character.";
        header("Location: create_new_user.php");
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: create_new_user.php");
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if user already exists
        $check_sql = "SELECT id FROM newusers WHERE studentNumber = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $studentNumber, $email);
        $check_stmt->execute();
        $exists = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();

        if ($exists) {
            throw new Exception("A user with this student number or email already exists.");
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $graduated = 'no';

        // Create new user
        $insert_sql = "INSERT INTO newusers (firstName, middleName, lastName, studentNumber, course, year, section, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $role = 'student';
        $status = 'active';
        $insert_stmt->bind_param("sssssssssss", 
            $firstName, 
            $middleName, 
            $lastName, 
            $studentNumber, 
            $course, 
            $year, 
            $section, 
            $email, 
            $hashedPassword,
            $role,
            $status
        );
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create user.");
        }
        
        $new_user_id = $conn->insert_id;
        $insert_stmt->close();

        // Add user to organization_members
        $member_sql = "INSERT INTO organization_members (user_id, organization_id) VALUES (?, ?)";
        $member_stmt = $conn->prepare($member_sql);
        $member_stmt->bind_param("ii", $new_user_id, $org_id);
        
        if (!$member_stmt->execute()) {
            throw new Exception("Failed to add user to organization.");
        }
        
        $member_stmt->close();

        // Add entry to join_org table with accepted status
        $join_sql = "INSERT INTO join_org (student_id, org_id, last_name, first_name, middle_name, student_number, course, year, section, email, status, application_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $join_stmt = $conn->prepare($join_sql);
        $application_status = 'approved';
        $join_stmt->bind_param("iisssssssss", 
            $new_user_id, 
            $org_id, 
            $lastName, 
            $firstName, 
            $middleName, 
            $studentNumber, 
            $course, 
            $year, 
            $section, 
            $email,
            $application_status
        );
        
        if (!$join_stmt->execute()) {
            throw new Exception("Failed to create join record.");
        }
        
        $join_stmt->close();

        $conn->commit();
        $_SESSION['success'] = "New member created successfully.";
        header("Location: new-manage_users.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: create_new_user.php");
        exit;
    }
}

$title = "Add New Member";
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

<main class="main-content">
    <?php include '../includes/navbar.php'; ?>

    <div class="content">
        <h2 class="page-title">ADD NEW MEMBER</h2>        

        <form method="POST" class="create-user-form">
            <div class="form-group">
                <label for="firstName">First Name *</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>

            <div class="form-group">
                <label for="middleName">Middle Name</label>
                <input type="text" id="middleName" name="middleName">
            </div>

            <div class="form-group">
                <label for="lastName">Last Name *</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>

            <div class="form-group">
                <label for="studentNumber">Student Number *</label>
                <input type="text" id="studentNumber" name="studentNumber" required>
            </div>

            <div class="form-group">
                <label for="course">Course *</label>
                <select id="course" name="course" required>
                    <option value="">Select Course</option>
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
                <label for="year">Year *</label>
                <select id="year" name="year" required>
                    <option value="">Select Year</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>

            <div class="form-group">
                <label for="section">Section *</label>
                <input type="text" id="section" name="section" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required pattern=".*@iskolarngbayan\.pup\.edu\.ph$" title="Must be a valid PUP email address (@iskolarngbayan.pup.edu.ph)">
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password *</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Create Member</button>
                <a href="new-manage_users.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</main>

<style>
.content {
    padding: 20px;
}
.create-user-form {
    max-width: 600px;
    margin: 0 auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}
.form-group input,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}
.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.btn-submit,
.btn-cancel {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
}
.btn-submit {
    background-color: #2A4365;
    color: white;
}
.btn-cancel {
    background-color: #e2e8f0;
    color: #2d3748;
}
.alert {
    padding: 10px 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    color: white;
}
.alert.error {
    background-color: #f44336;
}
.password-requirements {
    font-size: 0.85em;
    color: #666;
    margin-top: 5px;
}
.password-requirements ul {
    margin: 5px 0 0 20px;
    padding: 0;
}
.password-requirements li {
    margin: 2px 0;
}
input:invalid {
    border-color: #ff6b6b;
}
input:valid {
    border-color: #51cf66;
}
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<script src="../assets/scripts/hamburger.js"></script>
<script>
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()\-_=+{};:,<.>]/.test(password)
    };

    const requirementsList = document.querySelector('.password-requirements ul');
    requirementsList.innerHTML = `
        <li style="color: ${requirements.length ? '#51cf66' : '#ff6b6b'}">8 characters</li>
        <li style="color: ${requirements.uppercase ? '#51cf66' : '#ff6b6b'}">One uppercase letter</li>
        <li style="color: ${requirements.lowercase ? '#51cf66' : '#ff6b6b'}">One lowercase letter</li>
        <li style="color: ${requirements.number ? '#51cf66' : '#ff6b6b'}">One number</li>
        <li style="color: ${requirements.special ? '#51cf66' : '#ff6b6b'}">One special character</li>
    `;
});

document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (this.value === password) {
        this.style.borderColor = '#51cf66';
    } else {
        this.style.borderColor = '#ff6b6b';
    }
});
</script>
<?php include '../includes/footer.php'; ?> 