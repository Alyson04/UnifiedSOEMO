<?php
require '../config/db_conn.php';
require '../api/auth.php';
checkUserRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');

    if (empty($fullName)) {
        $_SESSION['error'] = "Full name is required.";
        header("Location: new-manage_users.php");
        exit;
    }

    // Prevent deletion of admins
    $sql = "UPDATE users SET status = 'deleted' WHERE LOWER(fullName) = LOWER(?) AND role != 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $fullName);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "User '$fullName' has been soft-deleted.";
        } else {
            $_SESSION['error'] = "No '$fullName' has been found";
        }
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: new-manage_users.php");
    exit;
}
?>
