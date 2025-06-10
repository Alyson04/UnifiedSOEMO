<?php
require 'auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Get org admin's organization ID
$user_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id && $user_id) {
    $sql_org = "SELECT id FROM neworganizations WHERE user_id = ?";
    $stmt = $conn->prepare($sql_org);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_id = $result->fetch_assoc()['id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt->close();
}

if (!$org_id) {
    echo json_encode(['success' => false, 'message' => 'Organization not found']);
    exit;
}

// Get post content
$content = $_POST['content'] ?? '';

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Content cannot be empty']);
    exit;
}

$image_path = null;

// Handle image upload if present
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

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Post created successfully']);

} catch (Exception $e) {
    $conn->rollback();
    
    // Delete uploaded image if exists
    if ($image_path && file_exists($upload_dir . $image_path)) {
        unlink($upload_dir . $image_path);
    }
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
