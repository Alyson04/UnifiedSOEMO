<?php
require 'auth.php';
require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['user_id'] ?? null;

$content = trim($_POST['content'] ?? '');

if (!$content || !$admin_id /* || !$org_id */) {
    $_SESSION['error'] = "Posting failed!";
    echo json_encode(['success' => false, 'message' => 'Missing content, user, or org.']);
    exit;
}

// Handle file upload
$imagePath = '';
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "../assets/uploads/";
    $fileName = basename($_FILES['image']['name']);
    $imagePath = $targetDir . $fileName;

    // Optional: Generate unique filename to prevent overwrite
    $imagePath = $targetDir . uniqid() . "_" . $fileName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $_SESSION['error'] = "Image upload failed!";
        echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
        exit;
    }
}

// Insert post into database
$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_path, org_id) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    $_SESSION['error'] = "Database error!";
    echo json_encode(['success' => false, 'message' => 'Database preparation failed.']);
    exit;
}

$stmt->bind_param("isss", $admin_id, $content, $imagePath, $org_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Posted successfully!";
    echo json_encode(['success' => true]);
} else {
    $_SESSION['error'] = "Posting failed!";
    echo json_encode(['success' => false, 'message' => 'Database execution failed.']);
}

$stmt->close();
$conn->close();
?>
