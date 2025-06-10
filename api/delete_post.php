<?php
require 'auth.php';
// Allow both admin and orgAdmin roles
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'orgAdmin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

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
$is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === 'true';

// Validate inputs
if (!$post_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // If user is admin, they can delete any post
    // If user is org admin, they can only delete their own posts
    $check_sql = $is_admin ? 
        "SELECT id, image_path FROM posts WHERE id = ?" :
        "SELECT id, image_path FROM posts WHERE id = ? AND user_id = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    if ($is_admin) {
        $check_stmt->bind_param("i", $post_id);
    } else {
        $check_stmt->bind_param("ii", $post_id, $user_id);
    }
    
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception($is_admin ? 'Post not found' : 'You do not have permission to delete this post');
    }

    // Get image path before deleting
    $image_path = $result->fetch_assoc()['image_path'];
    $check_stmt->close();

    // Delete the post
    $delete_sql = "DELETE FROM posts WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $post_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete post');
    }
    $delete_stmt->close();

    // Delete the image file if it exists
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