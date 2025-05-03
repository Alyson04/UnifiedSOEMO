<?php
require '../config/db_conn.php';

$result = $conn->query("SELECT content, image_path, created_at FROM posts ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    echo '<div class="post">';
    echo '<p>' . htmlspecialchars($row['content']) . '</p>';
    if (!empty($row['image_path'])) {
        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="Post image">';
    }
    echo '<small>' . htmlspecialchars($row['created_at']) . '</small>';
    echo '</div>';
}
