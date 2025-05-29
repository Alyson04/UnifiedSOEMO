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
        // Initial organization display (all organizations)
        $sql = "SELECT * FROM organizations ORDER BY created_at ASC";
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
            echo '<p style="color: white;">No organizations found with.</p>';
        endif;
        ?>

        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("orgSearch");
    const resultsContainer = document.getElementById("orgResults");

    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim();

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `search_orgs.php?q=${encodeURIComponent(query)}`, true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                resultsContainer.innerHTML = xhr.responseText;
            } else {
                resultsContainer.innerHTML = "<p style='color: white;'>Something went wrong.</p>";
            }
        };

        xhr.send();
    });
});
</script>
<script src="../assets/scripts/notif_script.js"></script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>
