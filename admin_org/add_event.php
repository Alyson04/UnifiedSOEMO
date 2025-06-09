<?php
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only org admins can access this page

require '../config/db_conn.php';

$user_id = $_SESSION['user_id'] ?? null;
$org_id = null;
$error = '';
$success = '';
$thumbnail_filename = null;

// Fetch the org_id for this orgAdmin user
if ($user_id) {
    $stmtOrg = $conn->prepare("SELECT id FROM neworganizations WHERE user_id = ?");
    $stmtOrg->bind_param("i", $user_id);
    $stmtOrg->execute();
    $stmtOrg->bind_result($org_id);
    $stmtOrg->fetch();
    $stmtOrg->close();

    if (!$org_id) {
        $error = "Organization not found for the current user.";
    }
} else {
    $error = "User not logged in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';

    // Basic validation
    if (empty($title) || empty($description) || empty($event_date)) {
        $error = "All fields are required.";
    } else {
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/uploads_highlights/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $original_name = basename($_FILES['thumbnail']['name']);
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($ext, $allowed_exts)) {
                $error = "Invalid file type for thumbnail.";
            } else {
                $thumbnail_filename = uniqid('thumb_', true) . '.' . $ext;
                $target_path = $upload_dir . $thumbnail_filename;

                if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_path)) {
                    $error = "Failed to upload thumbnail.";
                }
            }
        } else {
            $error = "Thumbnail is required.";
        }

        // Insert event if no error
        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, org_id, thumbnail, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssis", $title, $description, $event_date, $org_id, $thumbnail_filename);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Event added successfully!";
                header("Location: new-manage_events.php");
                exit;
            } else {
                $_SESSION['error'] = "Event adding failed!";
            }
            $stmt->close();
        }
    }
}

$conn->close();

$title = "Add New Event";
$style = "add_event.css";
include '../includes/header.php';
?>

<!-- Add mobile-specific styles -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_mobile.css">

<!-- Hamburger Menu Button -->
<button class="hamburger-menu">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
</button>

<!-- Overlay for mobile sidebar -->
<div class="overlay"></div>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
    <?php include '../includes/navbar.php'; ?>

    <div class="content">
        <h2 class="page-title">ADD NEW EVENT</h2>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Internal Modal Styles -->
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

        <!-- Custom Alert Message -->
        <div id="formError" class="floating-alert" style="display: none;"></div>

        <!-- Event Form -->
        <form id="eventForm" method="POST" class="event-form" enctype="multipart/form-data">
            <label for="title">Event Title</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Event Description</label>
            <textarea name="description" id="description" rows="5" required></textarea>

            <label for="event_date">Event Date</label>
            <input type="date" name="event_date" id="event_date" required>

            <label for="thumbnail">Event Thumbnail</label>
            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required>

            <button type="button" onclick="validateFormAndShowModal()">Create Event</button>
            <a href="new-manage_events.php" class="btn-cancel">Cancel</a>
        </form>

        <!-- Confirmation Modal -->
        <div class="modal-overlay" id="confirmModal">
            <div class="modal">
                <p>Are you sure you want to create this event?</p>
                <button class="confirm" onclick="submitForm()">Yes</button>
                <button class="cancel" onclick="hideConfirmModal()">No</button>
            </div>
        </div>

        <!-- Modal Script -->
        <script>
        function validateFormAndShowModal() {
            const errorBox = document.getElementById('formError');
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const eventDate = document.getElementById('event_date').value;
            const thumbnail = document.getElementById('thumbnail').files.length;

            // Reset alert
            errorBox.style.display = 'none';
            errorBox.innerText = '';

            if (!title || !description || !eventDate || thumbnail === 0) {
                errorBox.innerText = 'All fields are required.';
                errorBox.style.display = 'block';

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    errorBox.style.display = 'none';
                }, 5000);
                return;
            }

            showConfirmModal(); // All fields are valid
        }

        function showConfirmModal() {
            document.getElementById('confirmModal').style.display = 'block';
        }

        function hideConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function submitForm() {
            document.getElementById('eventForm').submit();
        }
        </script>
    </div>
</main>

<script src="../assets/scripts/admin_org_mobile.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
