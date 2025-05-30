<?php
require '../api/auth.php';
checkUserRole('org_admin'); // Only org admins can access this page

require '../config/db_conn.php';

$org_id = $_SESSION['org_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$error = '';
$success = '';
$thumbnail_filename = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';

    // Basic validation
    if (empty($title) || empty($description) || empty($event_date)) {
        $error = "All fields are required.";
    } else {
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/events/';
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
        }

        // Insert event if no error
        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, org_id, thumbnail, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssis", $title, $description, $event_date, $org_id, $thumbnail_filename);

            if ($stmt->execute()) {
                $success = "Event added successfully!";
            } else {
                $error = "Failed to add event. Please try again.";
            }
            $stmt->close();
        }
    }
}

$conn->close();

// Page metadata
$title = "Add New Event";
$style = "add_event.css";

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/navbar.php'; ?>

    <div class="content">
        <h2 class="page-title">ADD NEW EVENT</h2>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="event-form" enctype="multipart/form-data" onsubmit="return confirmCreateEvent()">
          <label for="title">Event Title</label>
    <input type="text" name="title" id="title" required>

    <label for="description">Event Description</label>
    <textarea name="description" id="description" rows="5" required></textarea>

    <label for="event_date">Event Date</label>
    <input type="date" name="event_date" id="event_date" required>

    <label for="thumbnail">Event Thumbnail</label>
    <input type="file" name="thumbnail" id="thumbnail" accept="image/*">

    <button type="submit">Create Event</button>
    <a href="new-manage_events.php" class="btn-cancel">Cancel</a>
</form>

<script>
function confirmCreateEvent() {
    return confirm("Are you sure you want to create this event?");
}
</script>

    </div>
</main>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
