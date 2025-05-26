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
            <button class="prev" onclick="prevStep()" disabled>Previous</button>
            <button class="next" onclick="nextStep()">Next</button>
            <button class="skip" onclick="closeTutorial()">Skip</button>
            <button class="never-show-btn" onclick="neverShowAgain()">Don't show again</button>
        </div>
    </div>
<?php endif; ?>
    <section class="welcome">
        <div class="text">
            <h1>WELCOME TO UNIFIED SOEMO!</h1>
            <p>Your ultimate gateway to connecting with student organizations and discovering tailored opportunities. Dive into a dynamic community, stay informed with announcements, and engage in events that spark your interests. Sign in to unlock a world of connections and start your journey with us today!</p>
        </div>
        <div class="image">
            <img src="../assets/pictures/WelcomeIMG.png" alt="University Image">
        </div>
    </section>

    <section class="organizations">
        <h2>STUDENT ORGANIZATION</h2>
        <div class="org-container">
            <div class="org-card">
                <div class="org-content">
                    <img src="../assets/pictures/PUP_SNKNMA.png" alt="PUP SKM">
                    <h3>PUP SENTRAL NA KONSEHO NG MGA-ARAL</h3>
                    <p>The PUP SKM serves as the prime representative of the student body of the PUP Main Campus. #ServeThePeople</p>
                </div>
                <button class="join-btn">JOIN NOW</button>
            </div>
            <div class="org-card">
                <div class="org-content">
                    <img src="../assets/pictures/PUP_IOTSC.png" alt="PUP Student Council">
                    <h3>PUP INSTITUTE OF TECHNOLOGY STUDENT COUNCIL</h3>
                    <p>May the voices in your head be soothed, and the rest of your journey be filled with tranquillity.
                        Empowering Innovators, Shaping Tomorrow’s Technology.</p>
                </div>
                <button class="join-btn">JOIN NOW</button>
            </div>
            <div class="org-card">
                <div class="org-content">
                    <img src="../assets/pictures/PUP_YFAP.png" alt="Youth for Animals">
                    <h3>YOUTH FOR ANIMALS PUP</h3>
                    <p>Your Voice Matters: For Animals, For Our Future.</p>
                </div>
                <button class="join-btn">JOIN NOW</button>
            </div>
            <div class="org-card">
                <div class="org-content">
                    <img src="../assets/pictures/H_PUP.png" alt="HATAW PUP">
                    <h3>HATAW PUP</h3>
                    <p>Hataw PUP is an accredited university-wide, advocacy student organization at PUP–Manila.</p>
                </div>
                <button class="join-btn">JOIN NOW</button>
            </div>
            <a href="../students/organizations.php" style="text-decoration: none" button class="discover-btn">DISCOVER MORE</button></a>
        </div>
    </section>

<section class="services">
    <h2>OUR SERVICES</h2>
    <div class="services-container">
        <div class="services-image">
            <img src="../assets/pictures/servicesimg.png" alt="Services Image">
        </div>
        <div class="services-text">
            <p>
                Our platform offers a centralized directory of student organizations, real-time updates on campus events, 
                and tools to streamline communication and engagement. It helps students discover opportunities, 
                connect with communities, and stay informed about activities that align with their interests.
            </p>
            <a href="../students/about_us.php" style="text-decoration: none" button class="learn-more-btn">LEARN MORE ABOUT US</button></a>
        </div>
    </div>
</section>

<script src="../assets/scripts/studentdashboard_script.js"></script>

<?php include '../includes/footer.php'; ?>
