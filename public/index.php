<?php 
session_start();
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

$title = "Student Dashboard";
$style = "studentdashboard_styles.css";
include '../includes/header.php';
include '../includes/navbar.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    &#9776;
</div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="#">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="new-post.php">Posts</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="about_us.php">About Us</a></li>
        <li><a href="../api/logout.php">Logout</a></li>
    </ul>
</div>

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
            <a href="../students/about_us.php" style="text-decoration: none" button class="learn-more-btn">LEARN MORE</button></a>
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
    const imagePath = "../assets/uploads_organizations/";
    container.innerHTML = "";

    const selected = shuffle([...orgs]).slice(0, 3);
    selected.forEach(org => {
        const card = document.createElement("div");
        card.className = "org-card";
        card.innerHTML = `
            <div class="org-content">
                <img src="${imagePath + org.img}" alt="${org.name}">
                <h3>${org.name}</h3>
                <p>${org.desc}</p>
            </div>
            <a class="join-btn" href="org_page.php?id=${encodeURIComponent(org.id)}">LEARN MORE</a>
        `;
        container.appendChild(card);
    });
}


let cachedOrgs = [];

async function initOrgs() {
    cachedOrgs = await fetchOrgs();
    displayOrgs(cachedOrgs);
    setInterval(() => displayOrgs(cachedOrgs), 5000);
}

document.addEventListener("DOMContentLoaded", initOrgs);
const alertBox = document.querySelector('.session-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }
</script>

<style>
    .session-alert {
    position: fixed;
    top: 20px;
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

.welcome {
    margin-top: 15px;
}
</style>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>


<?php include '../includes/footer.php'; ?>
