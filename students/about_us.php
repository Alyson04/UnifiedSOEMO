<?php 
require '../api/auth.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';

require '../config/db_conn.php';

// Fetch user's full name from newusers table
if ($student_id) {
    $sql_student = "SELECT firstName, middleName, lastName FROM newusers WHERE id = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();

    if ($result_student->num_rows > 0) {
        $row = $result_student->fetch_assoc();
        $firstName = $row['firstName'] ?? '';
        $middleName = $row['middleName'] ?? '';
        $lastName = $row['lastName'] ?? '';
        $student_name = ucwords(strtolower(trim("$firstName $middleName $lastName")));
    }

    $stmt->close();
}

$conn->close();

$title = "About Us"; 
$style = "about_us_styles.css"; 
include '../includes/header.php'; 
include '../includes/navbar.php'; 
?>


<div class="about-wrapper">
    <section class="about-us-section">
        <h2 class="section-title">ABOUT US</h2>
        <div class="about-us-content">
            <div class="about-text-box">
                <p><strong>Unified SOEMO</strong> is a dynamic platform designed to connect students and organizations, fostering collaboration and engagement. Our goal is to empower individuals to discover opportunities, participate in meaningful activities, and build lasting connections within their academic and social communities.</p>
            </div>
            <div class="about-image-box">
                <img src="../assets/pictures/about-us.png" alt="About Us">
            </div>
        </div>
    </section>

    <section class="mission-vision-section">
        <h2 class="section-title">MISSION and VISION</h2>
        <div class="mission-vision-grid">
            <div class="mission-box">
                <h3>Mission</h3>
                <p>At Unified SOEMO, our mission is to empower students by connecting them with organizations that foster growth, learning, and community involvement. We strive to create an inclusive platform where every student can easily discover and engage with groups that align with their passions and aspirations, helping them maximize their potential and enrich their campus experience.</p>
            </div>
            <div class="vision-box">
                <h3>Vision</h3>
                <p>Our vision is to be the leading platform for student engagement, fostering a vibrant and interconnected campus community. We aim to inspire students to build meaningful connections, develop lifelong skills, and contribute to a culture of collaboration and inclusivity within their universities and beyond.</p>
            </div>
        </div>
    </section>

    <section class="contact-us-section">
        <h2 class="section-title">CONTACT US</h2>
        <div class="contact-content">
            <div class="contact-text-box">
                <h4>Contact Us</h4>
                <p>We’d love to hear from you! Whether you have questions, feedback, or need assistance, the Unified SOEMO team is here to help. Feel free to reach out to us through any of the following channels:</p>
                <p><strong>Email</strong><br>
                📧 <a href="mailto:support@unsoemo.com">support@unsoemo.com</a><br>
                Send us an email, and we'll get back to you within 24–48 hours.</p>

              <p><strong>Social Media</strong><br>
            Connect with us on our social platforms for updates and quick assistance:<br>
            Facebook: <a href="https://www.facebook.com/UnifiedSoemo" target="_blank">UnifiedSoemo Official</a><br>
            Twitter: <a href="https://twitter.com/UnifiedSoemo" target="_blank">@UnifiedSoemo</a><br>
            Instagram: <a href="https://instagram.com/UnifiedSoemo" target="_blank">@UnifiedSoemo</a>
            </p>

            </div>
            <div class="contact-image-box">
                <img src="../assets/pictures/contact.png" alt="Contact Image">
            </div>
        </div>
    </section>
</div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>
<?php include '../includes/footer.php'; ?>
