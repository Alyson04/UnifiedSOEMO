<?php
require 'auth.php';
checkUserRole('orgAdmin');

require '../config/db_conn.php';

// Return JSON response
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the post ID from the request
$post_id = $_POST['post_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Validate inputs
if (!$post_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // First verify that the post belongs to the user
    $check_sql = "SELECT id FROM posts WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('You do not have permission to delete this post');
    }
    $check_stmt->close();

    // Get image path before deleting the post (if exists)
    $image_sql = "SELECT image_path FROM posts WHERE id = ?";
    $image_stmt = $conn->prepare($image_sql);
    $image_stmt->bind_param("i", $post_id);
    $image_stmt->execute();
    $image_result = $image_stmt->get_result();
    $image_path = null;
    
    if ($image_result->num_rows > 0) {
        $image_data = $image_result->fetch_assoc();
        $image_path = $image_data['image_path'];
    }
    $image_stmt->close();

    // Delete the post
    $delete_sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $post_id, $user_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete post');
    }
    $delete_stmt->close();

    // If post had an image, delete it from the filesystem
    if ($image_path) {
        $file_path = "../uploads/" . $image_path;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 