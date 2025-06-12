<?php
require_once '../config/db_conn.php';
require_once 'auth.php';
require_once 'notifications.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
$org_id = null;

// Get org_id based on user role
if ($role === 'orgAdmin') {
    // For org admins, get their organization's ID
    $stmt = $conn->prepare("SELECT id FROM neworganizations WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $org_id = $row['id'];
    }
    $stmt->close();
} else if ($role === 'admin') {
    // For system admins, org_id will be null
    $org_id = null;
} else if ($role === 'student') {
    // For students, get their organization's ID
    $stmt = $conn->prepare("SELECT org_id FROM newusers WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $org_id = $row['org_id'];
    }
    $stmt->close();
}

$content = $_POST['content'] ?? '';
$image_path = null;

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post content is required']);
    exit;
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG and GIF are allowed.']);
        exit;
    }
    
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($_FILES['image']['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB.']);
        exit;
    }
    
    $upload_dir = '../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_path = uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $image_path;
    
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit;
    }
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert post
    $sql = "INSERT INTO posts (content, image_path, user_id, org_id, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $content, $image_path, $user_id, $org_id);
    
    if (!$stmt->execute()) {
        // If insert fails, delete uploaded image if exists
        if ($image_path && file_exists($upload_dir . $image_path)) {
            unlink($upload_dir . $image_path);
        }
        throw new Exception("Failed to create post");
    }

    $post_id = $conn->insert_id;
    
    // Send notification about the new post if org_id is not null
    if ($org_id !== null) {
        notifyNewPost($post_id, $org_id, $content);
    } else if ($role === 'admin') {
        // For admin posts, notify all users
        $message = "New announcement from System Administrator: " . substr($content, 0, 50) . "...";
        notifyAllUsers($message);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Post created successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
