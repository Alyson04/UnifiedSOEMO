<?php
require '../config/db_conn.php';
require '../api/auth.php';
checkUserRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['studentNumber'] ?? '');

    if (empty($studentNumber)) {
        $_SESSION['error'] = "Student Number is required.";
        header("Location: new-manage_users.php");
        exit;
    }

    // Prevent deletion of admins
    $sql = "UPDATE newusers SET status = 'disabled' WHERE studentNumber = ? AND role != 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentNumber);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "User '$studentNumber' has been soft-deleted.";
        } else {
            $_SESSION['error'] = "No '$studentNumber' has been found";
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
