<?php 
session_start();
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

$title = "Home Page";
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
    <div class="hamburger-lines">&#9776;</div>
</div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="index.php">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="about_us.php">About Us</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="../api/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
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
        background-color: #4CAF50;
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
        background-color: #f44336;
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

    /* Hamburger Menu Styles */
    .hamburger {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1000;
        cursor: pointer;
        background: #1e3a4f;
        width: 35px;
        height: 35px;
        border-radius: 6px;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        padding: 0;
    }

    .hamburger-lines {
        color: #fff;
        font-size: 20px;
        line-height: 35px;
        text-align: center;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .hamburger:hover {
        background: #2c4d66;
    }

    .mobile-sidebar {
        display: none;
        position: fixed;
        top: 0;
        right: -280px;
        width: 280px;
        height: 100vh;
        background: #1e3a4f;;
        z-index: 999;
        transition: all 0.3s ease-in-out;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .mobile-sidebar.active {
        left: 0;
    }

    .sidebar-list {
        list-style: none;
        padding: 25px 0;
        margin: 0;
    }

    .sidebar-list li {
        padding: 0;
        margin: 5px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .sidebar-list li a {
        color: rgb(255, 255, 255);
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        display: block;
        padding: 12px 2px;
        border-radius: 8px;
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
    }

    .sidebar-list li:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar-list li a:hover {
        color: rgba(173, 211, 204, 1);
        transform: translateX(5px);
    }

    .sidebar-list li:last-child {
        margin-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 0;
    }

    .sidebar-list li:last-child a {
        color: #E74C3C;
    }

    .sidebar-list li:last-child:hover {
        background: rgba(231, 76, 60, 0.15);
    }

    .sidebar-list li:last-child a:hover {
        color: #ff6b6b;
    }

    /* Mobile Responsive Styles */
    @media only screen and (max-width: 600px) {
        .hamburger {
            display: block;
        }

        .mobile-sidebar {
            display: block;
        }

        .profile {
            display: none !important;
        }

        /* Welcome Section */
        .welcome {
            flex-direction: column;
            padding: 20px;
            margin-top: 60px;
        }

        .welcome .text {
            text-align: center;
            padding: 20px 10px;
        }

        .welcome .text h1 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .welcome .text p {
            font-size: 0.9rem;
        }

        .welcome .image {
            width: 100%;
            margin-top: 20px;
        }

        .welcome .image img {
            width: 100%;
            height: auto;
        }

        /* Organizations Section */
        .organizations {
            padding: 20px;
        }

        .organizations h2 {
            font-size: 1.6rem;
            margin-bottom: 20px;
        }

        .org-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .org-card {
            margin: 10px 0;
        }

        /* Services Section */
        .services {
            padding: 20px;
        }

        .services h2 {
            font-size: 1.6rem;
        }

        .services-container {
            flex-direction: column;
        }

        .services-image {
            width: 100%;
            margin-bottom: 20px;
        }

        .services-text {
            width: 100%;
            padding: 0;
        }

        .services-text p {
            font-size: 0.9rem;
        }

        .discover-btn,
        .learn-more-btn {
            width: 100%;
            margin: 10px 0;
        }
    }
</style>

<script>
// Add this at the beginning of your script section
function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('mobileSidebar');
    const hamburger = document.querySelector('.hamburger');
    
    if (!sidebar.contains(event.target) && !hamburger.contains(event.target) && sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    }
});

// Your existing scripts...
</script>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>


<?php include '../includes/footer.php'; ?>
