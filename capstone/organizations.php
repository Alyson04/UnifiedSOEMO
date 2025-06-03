<?php 
require '../api/auth.php';
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

// Fetch student's full name from database
if ($student_id) {
    $sql_student = "SELECT fullName FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();
    if ($result_student->num_rows > 0) {
        $student_name = ucwords(strtolower($result_student->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

$title = "Organizations";
$style = "organizations_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<section class="background">
    <div class="search-container">
        <input type="text" class="search-bar" id="orgSearch" placeholder="Search Organizations...">
    </div>
</section>

<section class="student-org">
    <h2 class="section-title">STUDENT ORGANIZATION</h2>
    <div class="student-org-wrapper">
        <div class="student-org-container" id="orgResults">
        
        <?php
        // Fetch organizations whose owners are NOT deleted
        $sql = "
    SELECT DISTINCT o.id, o.name, o.description, o.image_path, o.created_at
    FROM organizations o
    INNER JOIN users u ON u.org_id = o.id
    WHERE u.status != 'deleted'
    ORDER BY o.created_at ASC
";

        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $name = htmlspecialchars($row['name']);
                $description = htmlspecialchars($row['description']);
                $imagePath = !empty($row['image_path']) 
                    ? "../assets/uploads_organizations/" . htmlspecialchars($row['image_path']) 
                    : "../assets/pictures/default.jpg";
        ?>
            <div class="student-org-card">
                <img src="<?= $imagePath ?>" alt="<?= $name ?>">
                <h4><?= $name ?></h4>
                <p><?= $description ?></p>
                <a href="org_page.php?id=<?= $row['id'] ?>" class="join-btn">LEARN MORE</a>
            </div>
        <?php
            endwhile;
        else:
            echo '<p style="color: white;">No organizations found.</p>';
        endif;

        ?>

        </div>
    </div>
</section>

<style>
    .session-alert {
    position: fixed;
    top: 100px;
    left: 50%;
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
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("orgSearch");
    const resultsContainer = document.getElementById("orgResults");

    function fetchOrgs(query) {
        let url = 'search_orgs.php';
        if (query.trim() !== '') {
            url += '?q=' + encodeURIComponent(query.trim());
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", url, true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                resultsContainer.innerHTML = xhr.responseText;
            } else {
                resultsContainer.innerHTML = "<p style='color: white;'>Something went wrong.</p>";
            }
        };

        xhr.send();
    }

    // Initially load all organizations
    fetchOrgs('');

    searchInput.addEventListener("input", () => {
        fetchOrgs(searchInput.value);
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

</script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>
