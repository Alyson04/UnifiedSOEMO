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
    // Query posts with user fullName and organization image_path
    $query = "
      SELECT posts.*, users.fullName, organizations.image_path AS org_image_path
      FROM posts
      LEFT JOIN users ON posts.user_id = users.id
      LEFT JOIN organizations ON users.org_id = organizations.id
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
        echo "<p>No posts available.</p>";
    }
    ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
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
</script>
<script src="../assets/scripts/notif_script.js"></script>
