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

$title = "Student Dashboard";
$style = "studentdashboard_styles.css";
include '../includes/header.php';
include '../includes/navbar.php'; ?>

<?php
require '../config/db_conn.php';
$tutorial_seen = 0;

if ($student_id) {
    $sql_student = "SELECT fullName, tutorial_seen FROM users WHERE ID = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();
    if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();
        $student_name = ucwords(strtolower($row['fullName']));
        $tutorial_seen = $row['tutorial_seen'];
    }
    $stmt->close();
}
?>

<?php if (!$tutorial_seen): ?>
<div class="popup-overlay" id="tutorial">
    <div class="popup" id="popupBox">
        <button class="close-btn" onclick="closeTutorial()">&times;</button>
        <img id="tutorial-image" class="tutorial-image" src="" alt="Tutorial Step">
        <div id="tutorial-text">Welcome to our site! Let's take a quick tour.</div>
        <div class="button-container">
            <button class="prev" onclick="prevStep()" disabled>Previous</button>
            <button class="next" onclick="nextStep()">Next</button>
            <button class="skip" onclick="closeTutorial()">Skip</button>
            <button class="never-show-btn" onclick="neverShowAgain()">Don't show again</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Additional Content -->
<section class="welcome">
    <div class="text">
        <h1>WELCOME TO UNIFIED SOEMO!</h1>
        <p>Your ultimate gateway to connecting with student organizations and discovering tailored opportunities. Dive into a dynamic community, stay informed with announcements, and engage in events that spark your interests. Sign in to unlock a world of connections and start your journey with us today!</p>
    </div>
    <div class="image">
        <img src="../assets/pictures/WelcomeIMG.png" alt="University Image">
    </div>
</section>

<!-- Your other sections continue here -->
<script>
    // JavaScript logic for tutorial steps
    function closeTutorial() {
        document.getElementById("tutorial").style.display = "none";
    }

    function prevStep() {
        // Logic for previous step
    }

    function nextStep() {
        // Logic for next step
    }

    function neverShowAgain() {
        // Logic to never show the tutorial again
    }
</script>

<script src="../assets/scripts/studentdashboard_script.js"></script>
<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
