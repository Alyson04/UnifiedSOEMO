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
        <input type="text" class="search-bar" placeholder="Search Organizations...">
    </div>
</section>

<?php
// Fetch all student organizations ordered by creation date
$sql = "SELECT * FROM organizations ORDER BY created_at ASC";
$result = $conn->query($sql);
?>

<section class="student-org">
    <h2 class="section-title">STUDENT ORGANIZATION</h2>
    <div class="student-org-wrapper">
        <div class="student-org-container">

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $name = htmlspecialchars($row['name']);
                $description = htmlspecialchars($row['description']);

                // Prepare image path — prepend folder if image_path exists, else default image
                if (!empty($row['image_path'])) {
                    $imagePath = "../assets/uploads_organizations/" . htmlspecialchars($row['image_path']);
                } else {
                    $imagePath = "../assets/pictures/default.jpg";
                }
            ?>
            <div class="student-org-card">
                <img src="<?= $imagePath ?>" alt="<?= $name ?>">
                <h4><?= $name ?></h4>
                <p><?= $description ?></p>
                <a href="org_page.php?id=<?= $row['id'] ?>" class="join-btn">LEARN MORE</a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: white;">No organizations found.</p>
        <?php endif; ?>

        </div>
    </div>
</section>

<script src="../assets/scripts/notif_script.js"></script>
<?php 
$conn->close();
include '../includes/footer.php'; 
?>
