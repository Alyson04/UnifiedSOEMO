<?php
include '../config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    $org_id = $_POST['org_id'] ?? null;  // get org_id from POST

    if ($org_id === null) {
        die("Organization ID is required.");
    }

    // Fetch org name for the notification message
    $sql_org = "SELECT name FROM organizations WHERE id = ?";
    $stmt_org = $conn->prepare($sql_org);
    $stmt_org->bind_param("i", $org_id);
    $stmt_org->execute();
    $result_org = $stmt_org->get_result();
    $org_name = $result_org->num_rows > 0 ? $result_org->fetch_assoc()['name'] : "the organization";
    $stmt_org->close();

    // Determine status and create custom message
    if ($action == 'accept') {
        $status = 'approved';
        $message = "Your application to join '$org_name' has been accepted. Congratulations!";
    } elseif ($action == 'decline') {
        $status = 'declined';
        $message = "Your application to join '$org_name' has been declined.";
    } else {
        die("Invalid action.");
    }

    $conn->begin_transaction();

    try {
        // Update application status
        $sql = "UPDATE org_applications SET application_status = ? WHERE student_id = ? AND org_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $user_id, $org_id);
        $stmt->execute();
        $stmt->close();

        // If accepted, update user's org_id to the admin's org_id
        if ($status === 'approved') {
            $update_user_sql = "UPDATE users SET org_id = ? WHERE ID = ?";
            $stmt = $conn->prepare($update_user_sql);
            $stmt->bind_param("ii", $org_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        // Insert notification
        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("is", $user_id, $message);
        $notif_stmt->execute();
        $notif_stmt->close();

        $conn->commit();

        header("Location: ../admin/manage_users.php?status=success&action=$action");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }

    $conn->close();
}
?>
