<?php
require 'auth.php';

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;

$content = $_POST['content'] ?? '';

// Debug
// file_put_contents('../log.txt', "POST:\n" . print_r($_POST, true), FILE_APPEND);
// file_put_contents('../log.txt', "SESSION:\n" . print_r($_SESSION, true), FILE_APPEND);

if (!$content || !$admin_id /* || !$org_id */) {
    echo json_encode(['success' => false, 'message' => 'Missing content, user, or org.']);
    exit;
}

// Continue saving to database...
$imagePath = '';
if (!empty($_FILES['image']['name'])) {
    $targetDir = "../assets/uploads/";
    $imagePath = $targetDir . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
}

$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_path, org_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $admin_id, $content, $imagePath, $org_id);
$stmt->execute();

echo json_encode(['success' => true]);
?>
