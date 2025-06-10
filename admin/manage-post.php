<?php 
require '../api/auth.php';
checkUserRole('admin'); // Only allow admins

require '../config/db_conn.php';

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

// Redirect if not logged in
if (!$admin_id) {
  header("Location: ../public/login.php");
  exit();
}

$title = "Unified SOEMO Dashboard";
$style = "new-post.css";
include '../includes/header.php';
?>

<!-- Add mobile-specific styles -->
<link rel="stylesheet" href="../assets/stylesheets/admin_org_mobile.css">

<!-- Hamburger Menu Button -->
<button class="hamburger-menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>

<?php include '../includes/sidebar.php'; ?>

<!-- Main Panel -->
<main class="main-content">
<?php include '../includes/navbar.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<form id="postForm" enctype="multipart/form-data">
  <div class="form-wrapper">
    <textarea id="content" name="content" placeholder="What's on your mind?" style="overflow:hidden; resize:none;" required></textarea>
    <p id="warning" style="color:red;"></p>

    <div class="form-bottom">
      <label for="image-upload" class="upload-label">
        <img src="../assets/pictures/icon.png" alt="Upload" />
      </label>
      <input type="file" name="image" id="image-upload" accept="image/*" style="display: none;">
      <button type="submit">Post</button>
    </div>
  </div>
</form>

<?php include '../includes/modals.php'; ?>

<div id="postsWrapper">
  <div id="postsContainer">
    <?php
    // Query with LEFT JOIN to get user info and their organization's image path
    $query = "
  SELECT p.*,  CONCAT_WS(' ', u.firstName, u.middleName, u.lastName) AS fullName, o.name, o.image_path as org_image_path
FROM posts p
LEFT JOIN newusers u ON p.user_id = u.ID
LEFT JOIN neworganizations o ON p.org_id = o.ID
ORDER BY p.created_at DESC"; 

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $content = htmlspecialchars($row['content']);
            $username = htmlspecialchars($row['fullName'] ?? 'Unknown');

            // Determine organization profile picture
            $default_img = '../assets/pictures/icon.png';
            $profile_img = $default_img;

            if (!empty($row['org_image_path'])) {
                $possible_path = '../assets/uploads_organizations/' . $row['org_image_path'];
                if (file_exists($possible_path)) {
                    $profile_img = $possible_path;
                }
            }

            echo "<div class='post-card' data-post-id='{$row['id']}'>
                    <div class='post-header'>
                      <img src='{$profile_img}' alt='Profile picture of {$username}' />
                      <span class='username'>{$username}</span>
                      <button class='delete-post-btn' onclick='confirmDelete({$row['id']})'>
                        <img src='../assets/pictures/delete.png' alt='Delete' style='width: 20px; height: 20px;'>
                      </button>
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Delete Post</h3>
        <p>Are you sure you want to delete this post?</p>
        <div class="modal-actions">
            <button onclick="deletePost()" class="delete-btn">Delete</button>
            <button onclick="closeDeleteModal()" class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<style>
    .session-alert {
    position: fixed;
    top: 20px;
    left: 55%;
    transform: translateX(-50%);
    background-color: #4CAF50; /* Green by default for success */
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    max-width: 80%;
    text-align: center;
    animation: fadeInSlideDown 0.4s ease-in-out;
}

.session-alert.error {
    background-color: #f44336; /* Red for error */
}

@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

.post-header {
    display: flex;
    align-items: center;
    padding: 10px;
    position: relative;
}

.delete-post-btn {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: background-color 0.3s;
}

.delete-post-btn:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    text-align: center;
}

.modal-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.delete-btn {
    background: linear-gradient(135deg, #36577d, #2A4365);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.cancel-btn {
    background: linear-gradient(135deg, #36577d, #2A4365);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.delete-btn:hover {
    background-color: #c82333;
}

.cancel-btn:hover {
    background-color: #5a6268;
}
</style>

<script>
// Your existing JS code here (for form submission, confirmation modal, textarea auto-grow, etc.)
document.addEventListener('DOMContentLoaded', function() {
    const confirmationModal = document.getElementById('confirmationModal');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const postForm = document.getElementById('postForm');

    if (confirmationModal) {
        confirmationModal.style.display = 'none';
    }

    postForm.addEventListener('submit', function(e) {
        e.preventDefault();
        confirmationModal.style.display = 'flex';
    });

    confirmBtn.addEventListener('click', function() {
        const formData = new FormData(postForm);
        fetch('../api/submit_post.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
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

    cancelBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
    });

    const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
});

const textarea = document.getElementById('content');
const warning = document.getElementById('warning');
const maxChars = 500;
const maxWordLength = 20;

function autoGrow(element) {
  element.style.height = 'auto';
  element.style.height = element.scrollHeight + 'px';
}

textarea.addEventListener('input', function () {
  autoGrow(textarea);
  let value = textarea.value;

  if (value.length > maxChars) {
    value = value.substring(0, maxChars);
    warning.textContent = `Maximum character limit reached (${maxChars}).`;
  } else {
    warning.textContent = "";
  }

  const words = value.trim().split(/\s+/);
  let modified = false;
  for (let i = 0; i < words.length; i++) {
    if (words[i].length > maxWordLength) {
      words[i] = words[i].substring(0, maxWordLength);
      warning.textContent = `Word too long (max ${maxWordLength} characters), truncated.`;
      modified = true;
    }
  }

  if (modified) {
    value = words.join(' ');
  }

  if (textarea.value !== value) {
    textarea.value = value;
  }
});

let postToDelete = null;

function confirmDelete(postId) {
    postToDelete = postId;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    postToDelete = null;
}

function deletePost() {
    if (!postToDelete) return;

    fetch('../api/delete_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postToDelete}&is_admin=true`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the post from the DOM
            const postElement = document.querySelector(`[data-post-id="${postToDelete}"]`);
            if (postElement) {
                postElement.remove();
            }
            closeDeleteModal();
            
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'session-alert success';
            successAlert.textContent = 'Post deleted successfully';
            document.body.appendChild(successAlert);
            
            // Remove the success message after 4 seconds
            setTimeout(() => {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }, 4000);
        } else {
            alert(data.message || 'Failed to delete post');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the post');
    })
    .finally(() => {
        closeDeleteModal();
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const deleteModal = document.getElementById('deleteModal');
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
}
</script>
<script src="../assets/scripts/admin_org_mobile.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
