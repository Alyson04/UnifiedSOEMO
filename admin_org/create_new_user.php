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
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($studentNumber) || empty($email)) {
        $_SESSION['error'] = "Please fill in all required fields.";
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

        // Create new user
        $insert_sql = "INSERT INTO newusers (firstName, middleName, lastName, studentNumber, course, year, section, email, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student', 'active', NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssssss", $firstName, $middleName, $lastName, $studentNumber, $course, $year, $section, $email);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create user.");
        }
        
        $new_user_id = $conn->insert_id;
        $insert_stmt->close();

        // Add user to organization_members
        $member_sql = "INSERT INTO organization_members (user_id, organization_id, joined_at) VALUES (?, ?, NOW())";
        $member_stmt = $conn->prepare($member_sql);
        $member_stmt->bind_param("ii", $new_user_id, $org_id);
        
        if (!$member_stmt->execute()) {
            throw new Exception("Failed to add user to organization.");
        }
        
        $member_stmt->close();

        // Add entry to join_org table with accepted status
        $join_sql = "INSERT INTO join_org (student_id, org_id, status, application_date) VALUES (?, ?, 'accepted', NOW())";
        $join_stmt = $conn->prepare($join_sql);
        $join_stmt->bind_param("ii", $new_user_id, $org_id);
        
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
$style = "create_new_user.css";
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

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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
                    <option value="BSIT">BSIT</option>
                    <option value="BSIS">BSIS</option>
                    <option value="BSCS">BSCS</option>
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
                <input type="email" id="email" name="email" required>
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
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<script src="../assets/scripts/hamburger.js"></script>
<?php include '../includes/footer.php'; ?> 