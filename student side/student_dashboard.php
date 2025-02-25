<?php
// student_dashboard.php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../functions/login.php");
    exit;
}

require '../functions/db_conn.php';

// Fetch user details and notifications
$userId = $_SESSION['user']['ID'];
$userSql = "SELECT fullName, is_approved FROM users WHERE ID = ?";
$notificationSql = "SELECT message, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC";

$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userDetails = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare($notificationSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($userDetails['fullName']) ?>!</h1>

    <h2>Application Status</h2>
    <p>
        Status: <strong><?= htmlspecialchars(ucfirst($userDetails['is_approved'])) ?></strong>
    </p>

    <h2>Notifications</h2>
    <?php if (count($notifications) > 0): ?>
        <ul>
            <?php foreach ($notifications as $notification): ?>
                <li style="<?= $notification['is_read'] ? '' : 'font-weight: bold;' ?>">
                    <?= htmlspecialchars($notification['message']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No notifications yet.</p>
    <?php endif; ?>

    <a href="../functions/logout.php">Logout</a>
</body>
</html>