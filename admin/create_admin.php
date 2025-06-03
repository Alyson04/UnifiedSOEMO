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
            <h3>Create an Admin</h3>
        </div>
        <div class="card-body">
            <form id="createAdminForm" action="../api/create_admin.php" method="POST">
                <div class="form-group">
                    <label for="fullName">Fullname:</label>
                    <input type="text" id="fullName" name="fullName" required />
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" required />
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />
                </div>

                <input type="hidden" name="role" value="admin" />
                <input type="hidden" name="is_approved" value="approved" />

                <button type="submit" class="btn btn-success">Add Record</button>
                <button type="button" onclick="history.back()" class="btn-cancel">Cancel</button>
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
</style>

<script>
function validateFormAndShowModal(event) {
    event.preventDefault();

    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!fullName || !email || !password) {
        showAlert("All fields are required.");
        return;
    }

    const emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
    if (!emailPattern.test(email)) {
        showAlert("Please enter a valid email address.");
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
    document.getElementById('createAdminForm').submit();
}

function showAlert(message) {
    const alertBox = document.createElement('div');
    alertBox.className = 'floating-alert';
    alertBox.innerText = message;
    document.body.appendChild(alertBox);
    setTimeout(() => alertBox.remove(), 3000);
}

document.getElementById('createAdminForm').addEventListener('submit', validateFormAndShowModal);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    function toggleMenu() {
        hamburgerMenu.classList.toggle('active');
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    }

    hamburgerMenu.addEventListener('click', toggleMenu);
    sidebarOverlay.addEventListener('click', toggleMenu);
});
</script>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>

</main>
</div>
</body>
</html>
