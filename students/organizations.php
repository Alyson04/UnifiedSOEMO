<?php 
require '../api/auth.php';
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';
// Fetch admin's full name from database
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

$conn->close();

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
require '../config/db_conn.php';
$sql = "SELECT * FROM organizations ORDER BY created_at ASC";
$result = mysqli_query($conn, $sql);
?>

<section class="student-org">
    <h2 class="section-title">STUDENT ORGANIZATION</h2>
    <div class="student-org-wrapper">
        <div class="student-org-container">

        <?php
        // Fetch all student organizations
        $sql = "SELECT * FROM organizations ORDER BY created_at ASC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $name = htmlspecialchars($row['name']);
                $description = htmlspecialchars($row['description']);
                $imagePath = htmlspecialchars($row['image_path'] ?? '../assets/pictures/default.jpg'); // Fallback if no image
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
            echo "<p style='color: white;'>No organizations found.</p>";
        endif;

        $conn->close();
        ?>

        </div>
    </div>
</section>

<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>