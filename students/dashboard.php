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
        <div class="org-container" id="orgContainer">
        </div>
        <button class="discover-btn" onclick="window.location.href='organizations.php'">DISCOVER MORE</button>
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
            <button class="learn-more-btn">LEARN MORE ABOUT US</button>
        </div>
    </div>
</section>

<script>
async function fetchOrgs() {
    try {
        const res = await fetch('../api/get_orgs.php');
        const orgs = await res.json();
        return orgs;
    } catch (error) {
        console.error("Failed to fetch orgs:", error);
        return [];
    }
}

function shuffle(array) {
    return array.sort(() => 0.5 - Math.random());
}

function displayOrgs(orgs) {
    const container = document.getElementById("orgContainer");
    container.innerHTML = "";

    const selected = shuffle([...orgs]).slice(0, 3); // Show 2 random orgs
    selected.forEach(org => {
        const card = document.createElement("div");
        card.className = "org-card";
        card.innerHTML = `
            <div class="org-content">
                <img src="${org.img}" alt="${org.name}">
                <h3>${org.name}</h3>
                <p>${org.desc}</p>
            </div>
            <a class="join-btn" href="org_page.php?id=${encodeURIComponent(org.id)}">JOIN NOW</a>
        `;
        container.appendChild(card);
    });
}

let cachedOrgs = [];

async function initOrgs() {
    cachedOrgs = await fetchOrgs();
    displayOrgs(cachedOrgs);
    setInterval(() => displayOrgs(cachedOrgs), 20000);
}

document.addEventListener("DOMContentLoaded", initOrgs);
</script>

<script src="../assets/scripts/studentdashboard_script.js"></script>
<script src="../assets/scripts/notif_script.js"></script>


<?php include '../includes/footer.php'; ?>
