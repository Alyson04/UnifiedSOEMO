<?php
require '../api/auth.php';
checkUserRole('org_admin'); // Ensure only org admins access this

require '../config/db_conn.php';

$org_id = $_SESSION['org_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';

    // Basic validation
    if (empty($title) || empty($description) || empty($event_date)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, org_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $title, $description, $event_date, $org_id);
        if ($stmt->execute()) {
            $success = "Event added successfully!";
        } else {
            $error = "Failed to add event. Please try again.";
        }
        $stmt->close();
    }
}
$conn->close();

$title = "Add New Event";
$style = "add_event.css"; // optional CSS file
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

    <form method="POST" class="event-form">
    <label for="title">Event Title</label>
    <input type="text" name="title" id="title" required>

    <label for="description">Event Description</label>
    <textarea name="description" id="description" rows="5" required></textarea>

    <label for="event_date">Event Date</label>
    <input type="date" name="event_date" id="event_date" required>

    <button type="submit">Create Event</button>
    <a href="new-manage_events.php" class="btn-cancel">Cancel</a>
</form>

</div>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>
