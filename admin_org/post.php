<?php
require '../api/auth.php';
checkUserRole('orgAdmin'); // Only allow admins

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
  <input type="file" name="image" accept="image/*">
  <button type="submit">Post</button>
</form>

<?php include '../includes/modals.php'; include '../includes/sidebar.php';?>

<div id="postsWrapper">
  <div id="postsContainer">
    <!-- Posts will be loaded here -->
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="../assets/scripts/notif_script.js"></script>
<script>
// Add the event listeners for confirm and cancel buttons only once
document.addEventListener('DOMContentLoaded', function() {
    loadPosts();

    const confirmationModal = document.getElementById('confirmationModal');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const postForm = document.getElementById('postForm');
    
    // Handle form submission
    postForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the form from submitting immediately
        
        // Show the confirmation modal
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
                loadPosts(); // reload posts
                postForm.reset(); // reset the form
                confirmationModal.style.display = 'none'; // Hide modal
            } else {
                alert(data.message || "Something went wrong.");
                confirmationModal.style.display = 'none'; // Hide modal
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('An error occurred.');
            confirmationModal.style.display = 'none'; // Hide modal
        });
    });
    
    // Handle cancel button click
    cancelBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none'; // Hide modal
    });
});

// Function to load posts
function loadPosts() {
    fetch('../api/get_post.php') // Fetch posts from get_posts.php
    .then(res => res.text())
    .then(html => {
        document.getElementById('postsContainer').innerHTML = html;
    })
    .catch(err => {
        console.error('Error loading posts:', err);
    });
}
</script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

