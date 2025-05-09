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

$title = "Events"; 
$style = "events_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php';
?>
        <div class="blue-section">
        <img src="IMG/ribbon.png" alt="Ribbon Cutting Ceremony" class="section-image">
    </div>
    <div class="text-section">
        <p>
            The UNIFIED SOEMO at the Main Campus brings together student organizations to promote collaboration, engagement, and growth. Explore 
            opportunities to join organizations, participate in campus-wide events, and make meaningful connections within the vibrant university community!
        </p>
    </div>
    <div class="calendar-section">
        <div class="calendar">
            <!-- Month and Year -->
            <div class="calendar-header">
                <span class="month-year">January 2025</span>
            </div>
        
            <!-- Weekdays -->
            <div class="calendar-weekdays">
                <span>Sun</span>
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
            </div>
        
            <!-- Days of the Month (Starting on Wednesday, Jan 1, 2025) -->
            <div class="calendar-days">
                <span class="empty"></span> <!-- Sunday -->
                <span class="empty"></span> <!-- Monday -->
                <span class="empty"></span> <!-- Tuesday -->
                <span>1</span>
                <span>2</span>
                <span>3</span>
                <span>4</span>
                <span>5</span>
                <span>6</span>
                <span>7</span>
                <span>8</span>
                <span>9</span>
                <span>10</span>
                <span>11</span>
                <span>12</span>
                <span>13</span>
                <span>14</span>
                <span>15</span>
                <span>16</span>
                <span>17</span>
                <span>18</span>
                <span>19</span>
                <span>20</span>
                <span>21</span>
                <span>22</span>
                <span>23</span>
                <span>24</span>
                <span>25</span>
                <span>26</span>
                <span>27</span>
                <span>28</span>
                <span>29</span>
                <span>30</span>
                <span>31</span>
                <!-- Continue for all days -->
            </div>
            
        </div>
        
    
        <div class="events-section">
            <div class="upcoming-events">
                <h2>UPCOMING EVENTS</h2>
                <a href="#">
                    <img src="IMG/pupcet.png" alt="PUPCET 2025">
                    <p><strong>#PUPCET2025</strong><br>January 12, 2025<br>View Details</p>
                </a>
                <a href="#">
                    <img src="IMG/exam1.png" alt="Final Examination">
                    <p><strong>Final Examination</strong><br>January 12, 2025<br>View Details</p>
                </a>
                <a href="#">
                    <img src="IMG/exam2.png" alt="Final Examination">
                    <p><strong>Final Examination</strong><br>January 12, 2025<br>View Details</p>
                </a>
            </div>
    
            <div class="past-events">
                <h2>PAST EVENT HIGHLIGHTS</h2>
                <a href="#">
                    <img src="IMG/sucaa.png" alt="SUCAA 2024">
                    <p><strong>#SUCAA2024</strong><br>January 12, 2025<br>View Details</p>
                </a>
                <a href="#">
                    <img src="IMG/iskolaris.png" alt="Iskolaris 2025">
                    <p><strong>Iskolaris 2025</strong><br>January 12, 2025<br>View Details</p>
                </a>
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>