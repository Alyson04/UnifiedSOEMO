<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;

if (!$admin_id) {
  header("Location: ../public/login.php");
  exit();
}

$title = "Unified SOEMO Dashboard";
$style = "new-post.css";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Panel -->
<main class="main-content">
<?php
include '../includes/navbar.php';
?>

<form id="postForm" enctype="multipart/form-data">
  <div class="form-wrapper">
    <textarea name="content" placeholder="What's on your mind?" required></textarea>

    <div class="form-bottom">
      <label for="image-upload" class="upload-label">
        <img src="../assets/pictures/icon.png" alt="Upload" />
      </label>
      <input type="file" name="image" id="image-upload" accept="image/*" style="display: none;">
      <button type="submit">Post</button>
    </div>
  </div>
</form>

<?php include '../includes/modals.php';?>

<div id="postsWrapper">
  <div id="postsContainer">
    <?php
    $query = "SELECT posts.*, users.fullName 
              FROM posts
              LEFT JOIN users ON posts.user_id = users.id
              ORDER BY posts.created_at DESC";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $content = htmlspecialchars($row['content']);
            $profile_img = '../assets/pictures/icon.png'; // Or fetch user profile picture if you have it
            $username = htmlspecialchars($row['fullName'] ?? 'Unknown');

            echo "<div class='post-card'>
                    <div class='post-header'>
                      <img src='{$profile_img}' alt='{$username}' />
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
        echo "<p>No posts available.</p>";
    }
    ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Add the event listeners for confirm and cancel buttons only once
document.addEventListener('DOMContentLoaded', function() {
    const confirmationModal = document.getElementById('confirmationModal');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const postForm = document.getElementById('postForm');

    if (confirmationModal) {
        confirmationModal.style.display = 'none';
    }

    // Handle form submission
    postForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the form from submitting immediately
        confirmationModal.style.display = 'flex';
    });

    // Handle confirm button click
    confirmBtn.addEventListener('click', function() {
        const formData = new FormData(postForm);

        fetch('../api/submit_post.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload the page to show new post
            } else {
                alert(data.message || "Something went wrong.");
                confirmationModal.style.display = 'none';
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('An error occurred.');
            confirmationModal.style.display = 'none';
        });
    });

    // Handle cancel button click
    cancelBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
    });
});
</script>
