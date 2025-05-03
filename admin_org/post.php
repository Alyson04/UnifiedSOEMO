<?php require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

$admin_id = $_SESSION['user_id'] ?? null;

if (!$admin_id) {
    header("Location: ../login.php");
    exit();
}

$title = "Admin Settings"; 
$style = "post_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
 ?>

<form id="postForm" enctype="multipart/form-data">
  <textarea name="content" placeholder="What's on your mind?" required></textarea>
  <input type="file" name="image">
  <button type="submit">Post</button>
</form>

<div id="postsContainer">
  <!-- Posts will be loaded here -->
</div>

<?php include '../includes/footer.php'; ?>

<!-- 
<script>
document.getElementById('postForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('submit_post.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadPosts(); // reload posts
            this.reset();
        } else {
            alert(data.message || "Something went wrong.");
        }
    });
});

function loadPosts() {
    fetch('get_posts.php')
    .then(res => res.text())
    .then(html => {
        document.getElementById('postsContainer').innerHTML = html;
    });
}

document.addEventListener('DOMContentLoaded', loadPosts);
</script> -->
