<?php 
require '../api/auth.php';
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

$year = date('Y');
$today = date('Y-m-d');
$limit = 3;

// UPCOMING EVENTS PAGINATION
$upage = isset($_GET['upage']) && is_numeric($_GET['upage']) ? (int)$_GET['upage'] : 1;
$up_offset = ($upage - 1) * $limit;

// Count upcoming events
$sql = "SELECT COUNT(*) as total FROM events e 
INNER JOIN organizations o ON e.org_id = o.ID 
WHERE e.event_date >= ? 
AND YEAR(e.event_date) = ? 
AND EXISTS (SELECT 1 FROM users u WHERE u.org_id = o.id AND u.status != 'deleted')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $today, $year);
$stmt->execute();
$result = $stmt->get_result();
$up_total = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();
$up_total_pages = ceil($up_total / $limit);

// Fetch upcoming events
$sql = "SELECT * FROM events e 
WHERE e.event_date >= ? 
AND YEAR(e.event_date) = ? 
ORDER BY e.event_date ASC 
LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $today, $year, $limit, $up_offset);
$stmt->execute();
$upcoming_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// PAST EVENTS PAGINATION
$ppage = isset($_GET['ppage']) && is_numeric($_GET['ppage']) ? (int)$_GET['ppage'] : 1;
$past_offset = ($ppage - 1) * $limit;

// Count past events
$sql = "SELECT COUNT(*) as total FROM events e 
INNER JOIN organizations o ON e.org_id = o.ID 
WHERE e.event_date < ? 
AND YEAR(e.event_date) = ? 
AND EXISTS (SELECT 1 FROM users u WHERE u.org_id = o.id AND u.status != 'deleted')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $today, $year);
$stmt->execute();
$result = $stmt->get_result();
$past_total = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();
$past_total_pages = ceil($past_total / $limit);

// Fetch past events
$sql = "SELECT * FROM events e 
WHERE e.event_date < ? 
AND YEAR(e.event_date) = ? 
ORDER BY e.event_date DESC 
LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $today, $year, $limit, $past_offset);
$stmt->execute();
$past_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

function pagination_links($current_page, $total_pages, $param_name) {
    echo '<div class="pagination">';
    if ($current_page > 1) {
        echo '<a href="?' . $param_name . '=' . ($current_page - 1) . '">&laquo; Prev</a>';
    }
    echo "<span> Page $current_page of $total_pages </span>";
    if ($current_page < $total_pages) {
        echo '<a href="?' . $param_name . '=' . ($current_page + 1) . '">Next &raquo;</a>';
    }
    echo '</div>';
}

$title = "Events";
$style = "events_styles.css";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="blue-section">
    <img src="../assets/pictures/ribbons.png" alt="ribbon" class="section-image">
</div>

<div class="text-section">
    <p>
        The UNIFIED SOEMO at the Main Campus brings together student organizations to promote collaboration, engagement, and growth. Explore 
        opportunities to join organizations, participate in campus-wide events, and make meaningful connections within the vibrant university community!
    </p>
</div>

<div class="calendar-section">
    <div class="calendar">
        <div class="calendar-header">
            <button id="prevMonthBtn" aria-label="Previous Month">&#8592;</button>
            <span class="month-year" id="monthYear"></span>
            <button id="nextMonthBtn" aria-label="Next Month">&#8594;</button>
        </div>

        <div class="calendar-weekdays">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>

        <div class="calendar-days" id="calendarDays"></div>
    </div>

    <div class="events-section">
    <div class="upcoming-events">
        <h2>UPCOMING EVENTS</h2>
        <?php if (count($upcoming_events) > 0): ?>
            <?php foreach ($upcoming_events as $event): ?>
                <a href="#">
                    <div class="event-item">
                        <img src="<?= !empty($event['thumbnail']) ? "../assets/uploads_highlights/" . htmlspecialchars($event['thumbnail']) : '../assets/default-thumbnail.jpg' ?>" alt="Thumbnail">
                        <div>
                            <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                            <?= date('F j, Y', strtotime($event['event_date'])) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php pagination_links($upage, $up_total_pages, 'upage'); ?>
        <?php else: ?>
            <p>No upcoming events.</p>
        <?php endif; ?>
    </div>

    <div class="past-events">
        <h2>PAST EVENTS</h2>
        <?php if (count($past_events) > 0): ?>
            <?php foreach ($past_events as $event): ?>
                <a href="#">
                    <div class="event-item">
                        <img src="<?= !empty($event['thumbnail']) ? "../assets/uploads_highlights/" . htmlspecialchars($event['thumbnail']) : '../assets/default-thumbnail.jpg' ?>" alt="Highlight">
                        <div>
                            <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                            <?= date('F j, Y', strtotime($event['event_date'])) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php pagination_links($ppage, $past_total_pages, 'ppage'); ?>
        <?php else: ?>
            <p>No past events.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Hamburger Icon for Mobile -->
<div class="hamburger" onclick="toggleSidebar()">
    <div class="hamburger-lines">&#9776;</div>
</div>

<!-- Sidebar for Mobile -->
<div class="mobile-sidebar" id="mobileSidebar">
    <ul class="sidebar-list">
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="organizations.php">Organizations</a></li>
        <li><a href="new-post.php">Posts</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="about_us.php">About Us</a></li>
        <li><a href="../api/logout.php">Logout</a></li>
    </ul>
</div>

<script src="../assets/scripts/notif_script.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const monthYearElement = document.getElementById('monthYear');
    const calendarDaysElement = document.getElementById('calendarDays');
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');

    const months = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    const eventDatesPHP = <?php
        $all_event_dates = array_map(fn($e) => $e['event_date'], array_merge($upcoming_events, $past_events));
        echo json_encode($all_event_dates);
    ?>;

    const eventDateSet = new Set(eventDatesPHP);

    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();

    function renderCalendar(year, month) {
        monthYearElement.textContent = `${months[month]} ${year}`;
        calendarDaysElement.innerHTML = '';

        const firstDayIndex = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();

        for(let i = 0; i < firstDayIndex; i++) {
            const blank = document.createElement('span');
            blank.classList.add('empty');
            calendarDaysElement.appendChild(blank);
        }

        for(let day = 1; day <= totalDays; day++) {
            const daySpan = document.createElement('span');
            daySpan.textContent = day;

            const dateStr = `${year}-${String(month + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            if(eventDateSet.has(dateStr)) {
                daySpan.classList.add('event-day');
                daySpan.title = "Event day";
            }

            calendarDaysElement.appendChild(daySpan);
        }
    }

    prevMonthBtn.addEventListener('click', () => {
        currentMonth--;
        if(currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar(currentYear, currentMonth);
    });

    nextMonthBtn.addEventListener('click', () => {
        currentMonth++;
        if(currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar(currentYear, currentMonth);
    });

    renderCalendar(currentYear, currentMonth);
});
</script>

<style>
.calendar-days span.event-day {
    background-color: #ffcc00;
    border-radius: 50%;
    font-weight: bold;
    cursor: pointer;
    color: #000;
}
.calendar-header {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}
.calendar-header button {
    cursor: pointer;
    font-size: 1.2rem;
    background: none;
    border: none;
    padding: 5px 10px;
    color: #007bff;
    transition: color 0.3s;
}
.calendar-header button:hover {
    color: #0056b3;
}
.event-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    text-decoration: none;
    color: inherit;
}
.event-item img {
    width: 180px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
}
.pagination {
    margin-top: 15px;
    font-weight: bold;
}
.pagination a {
    text-decoration: none;
    margin: 0 10px;
    color: #0077cc;
}

/* Mobile Menu Styles */
.hamburger {
    display: none;
    position: fixed;
    top: 15px;
    left: 30px;
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

.mobile-sidebar {
    display: none;
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100vh;
    background: #1e3a4f;
    z-index: 999;
    transition: all 0.3s ease-in-out;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.mobile-sidebar.active {
    left: 0;
}

.sidebar-list {
    list-style: none;
    padding: 50px 0;
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
    padding: 12px 20px;
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
    
    .navbar {
        display: none !important;
        visibility: hidden;
        opacity: 0;
    }
    
    nav {
        display: none !important;
    }
    
    .nav-list {
        display: none !important;
    }

    .logo {
        display: none !important;
    }
    
    .logo img {
        display: none !important;
    }

    .profile {
        display: none !important;
    }

    .top-bar {
        display: none !important;
    }
    
    .blue-section {
        margin-top: 60px;
    }
}
</style>

<script>
// Mobile menu functionality
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
</script>

<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
