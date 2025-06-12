<?php 
require '../api/auth.php';
require '../config/db_conn.php';

$title = "Posts";
$style = "student-new-post.css";
include '../includes/header.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<div id="postsWrapper">
  <div id="postsContainer">
    <?php
    // Query posts excluding deleted users/orgs
    $query = "
  SELECT posts.*, 
    CASE 
      WHEN users.middleName IS NULL OR users.middleName = '' OR LOWER(users.middleName) = 'n/a' 
      THEN CONCAT(users.firstName, ' ', users.lastName)
      ELSE CONCAT(users.firstName, ' ', users.middleName, ' ', users.lastName)
    END AS fullName,
    organizations.image_path AS org_image_path
  FROM posts
  LEFT JOIN users ON posts.user_id = users.id
  LEFT JOIN organizations ON users.org_id = organizations.id
  WHERE users.status != 'deleted'
  ORDER BY posts.created_at DESC
";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $content = htmlspecialchars($row['content']);
            $username = htmlspecialchars($row['fullName'] ?? 'Unknown');

            $default_img = '../assets/pictures/icon.png';
            $profile_img = $default_img;

            if (!empty($row['org_image_path'])) {
                $possible_path = '../assets/uploads_organizations/' . $row['org_image_path'];
                if (file_exists($possible_path)) {
                    $profile_img = $possible_path;
                }
            }

            echo "<div class='post-card'>
                    <div class='post-header'>
                      <img src='{$profile_img}' alt='Profile picture of {$username}' />
                      <span class='username'>{$username}</span>
                    </div>
                    <div class='post-content'>{$content}</div>";

            if (!empty($row['image_path'])) {
                $postImage = "../uploads/" . htmlspecialchars($row['image_path']);
                echo "<div class='post-image'>
                        <img src='{$postImage}' alt='Post Image' style='max-width: 100%; border-radius: 10px; margin-top: 10px;' />
                      </div>";
            }

            echo "</div>";
        }
    } else {
        echo "<p class='no-post'>No posts available.</p>";
    }
    ?>
  </div>
</div>

<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
