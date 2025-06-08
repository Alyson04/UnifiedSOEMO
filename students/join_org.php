<?php
require '../api/auth.php';
require '../config/db_conn.php';

$student_id = $_SESSION['user_id'] ?? null;
$org_name = '';
$org_id = $_GET['id'] ?? null;

$firstName = '';
$middleName = '';
$lastName = '';
$email = '';
$studentNumber = '';
$course = '';
$year = '';
$section = '';

if ($student_id) {
    $stmt = $conn->prepare("SELECT firstName, middleName, lastName, email, studentNumber, course, year, section FROM newusers WHERE ID = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $firstName = $row['firstName'] ?? '';
        $middleName = $row['middleName'] ?? '';
        $lastName = $row['lastName'] ?? '';
        $email = $row['email'] ?? '';
        $studentNumber = $row['studentNumber'] ?? '';
        $course = $row['course'] ?? '';
        $year = $row['year'] ?? '';
        $section = $row['section'] ?? '';
    }
    $stmt->close();
}

if ($org_id) {
    $stmt = $conn->prepare("SELECT name FROM neworganizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $org_name = $result->fetch_assoc()['name'];
    }
    $stmt->close();
}

$conn->close();
$title = "Join Organization";
$style = "joinorg_styles.css";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="outer-container">
  <div class="join-container">
      <h2 class="section-title">Join Organization</h2>
      <form action="../api/submit_join.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="org_id" value="<?= htmlspecialchars($org_id) ?>">

          <label>Organization Name:</label>
          <input type="text" value="<?= htmlspecialchars($org_name) ?>" readonly>

          <label>First Name:</label>
          <input type="text" name="firstName" value="<?= htmlspecialchars($firstName) ?>" readonly>

          <label>Middle Name:</label>
          <input type="text" name="middleName" value="<?= htmlspecialchars($middleName) ?>" readonly>

          <label>Last Name:</label>
          <input type="text" name="lastName" value="<?= htmlspecialchars($lastName) ?>" readonly>

          <label>Email:</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>

          <label>Student Number:</label>
          <input type="text" name="studentNumber" value="<?= htmlspecialchars($studentNumber) ?>" readonly>

          <label>Course:</label>
          <input type="text" name="course" value="<?= htmlspecialchars($course) ?>" readonly>

          <label>Year:</label>
          <input type="text" name="year" value="<?= htmlspecialchars($year) ?>" readonly>

          <label>Section:</label>
          <input type="text" name="section" value="<?= htmlspecialchars($section) ?>" readonly>

          <label for="portfolio">Upload Portfolio / Required Files (PDF, DOCX, JPG, PNG):</label>
          <input type="file" name="portfolio_file" id="portfolio" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

          <div class="form-buttons">
              <button type="submit">Submit Application</button>
              <a href="org_page.php?id=<?= urlencode($org_id) ?>" class="cancel-button">Cancel</a>
          </div>
      </form>
  </div>
</div>

<script src="../assets/scripts/notif_script.js"></script>
<script src="../assets/scripts/inactive.js"></script>

<?php include '../includes/footer.php'; ?>
