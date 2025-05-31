<?php 
require '../api/auth.php';
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

// Fetch student name
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

// Fetch all events for current year ordered by event_date ascending
$year = date('Y');
$sql_events = "SELECT e.id, e.title, e.description, e.event_date, e.org_id, e.created_at, e.thumbnail 
               FROM events e
               INNER JOIN organizations o ON e.org_id = o.ID
               INNER JOIN users u ON o.id = u.ID
               WHERE YEAR(e.event_date) = ? AND u.status != 'deleted'
               ORDER BY e.event_date ASC";

$stmt = $conn->prepare($sql_events);
$stmt->bind_param("i", $year);
$stmt->execute();
$result_events = $stmt->get_result();

$upcoming_events = [];
$past_events = [];
$today = date('Y-m-d');

while ($row = $result_events->fetch_assoc()) {
    if ($row['event_date'] >= $today) {
        $upcoming_events[] = $row;
    } else {
        $past_events[] = $row;
    }
}

$stmt->close();
$conn->close();

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
                    <?php
                        $thumb = !empty($event['thumbnail']) ? "../assets/uploads_highlights/" . htmlspecialchars($event['thumbnail']) : "../assets/default-thumbnail.jpg";
                    ?>
                    <a href="#" style="text-decoration: none; color: inherit;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <img src="<?= $thumb ?>" alt="Event Thumbnail" style="width: 180px; height: 80px; object-fit: cover; border-radius: 5px;">
                            <div>
                                <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                                <?= date('F j, Y', strtotime($event['event_date'])) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No upcoming events.</p>
            <?php endif; ?>
        </div>

        <div class="past-events">
            <h2>PAST EVENTS</h2>
            <?php if (count($past_events) > 0): ?>
                <?php foreach ($past_events as $event): ?>
                    <?php
                        $highlight = !empty($event['thumbnail']) ? "../assets/uploads_highlights/" . htmlspecialchars($event['thumbnail']) : "../assets/uploads_highlights/default-highlight.jpg";
                    ?>
                    <a href="#" style="text-decoration: none; color: inherit;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <img src="<?= $highlight ?>" alt="Event Highlight" style="width: 180px; height: 80px; object-fit: cover; border-radius: 5px;">
                            <div>
                                <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                                <?= date('F j, Y', strtotime($event['event_date'])) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No past events.</p>
            <?php endif; ?>
        </div>
    </div>
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
</style>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
