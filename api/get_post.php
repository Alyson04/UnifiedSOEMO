<?php
require '../config/db_conn.php';

// Fetch posts including the image_path
$query = "SELECT posts.content, posts.image_path, posts.created_at, users.fullName 
          FROM posts 
          JOIN users ON posts.user_id = users.id
          ORDER BY posts.created_at DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="post">';
        echo '<p><strong>' . htmlspecialchars($row['fullName']) . '</strong></p>';
        echo '<p>' . htmlspecialchars($row['content']) . '</p>';

        // Display image if it exists
        if (!empty($row['image_path'])) {
            echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="Post Image" style="max-width: 300px; display: block; margin-top: 10px;">';
        }

        echo '<p><em>Posted on ' . htmlspecialchars($row['created_at']) . '</em></p>';
        echo '</div>';
    }
} else {
    echo 'Error fetching posts.';
}

mysqli_close($conn);
?>
