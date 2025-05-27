<?php 
require '../api/auth.php';
checkUserRole('org_admin'); // Only allow admins

require '../config/db_conn.php';

// Get logged-in user's ID from session
$admin_id = $_SESSION['user_id'] ?? null;
$org_id = $_SESSION['org_id'] ?? null;
$admin_name = '';

// Fetch admin's full name from database
if ($admin_id) {
    $sql_admin = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result_admin = $stmt->get_result();
    if ($result_admin->num_rows > 0) {
        $admin_name = ucwords(strtolower($result_admin->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

$sql = "SELECT id, fullName, email, is_approved, created_at FROM users WHERE role = 'student' AND org_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();

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
    <!-- Posts will be loaded here -->
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Add the event listeners for confirm and cancel buttons only once
document.addEventListener('DOMContentLoaded', function() {
    loadPosts();

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

document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('image-upload');
  const fileNameDisplay = document.getElementById('file-name');

  fileInput.addEventListener('change', function() {
    fileNameDisplay.textContent = fileInput.files.length > 0
      ? fileInput.files[0].name
      : 'No file chosen';
  });
});

</script>
<script src="../assets/scripts/notif_script.js"></script>


