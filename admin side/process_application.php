<?php
include '../functions/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    // Determine status and message
    if ($action == 'accept') {
        $status = 'approved';
        $message = "Your application has been accepted!";
    } elseif ($action == 'decline') {
        $status = 'declined';
        $message = "Your application has been declined.";
    } else {
        die("Invalid action.");
    }

    // Update user's status
    $sql = "UPDATE users SET is_approved = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $user_id);

    if ($stmt->execute()) {
        // Add notification
        $notification_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notification_sql);
        $notif_stmt->bind_param("is", $user_id, $message);
        $notif_stmt->execute();

        // Redirect back to admin dashboard
        header("Location: admin_dashboard.php?status=success&action=$action");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
