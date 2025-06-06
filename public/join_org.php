<?php
require '../config/db_conn.php';

$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
$org_name = '';
$org_id = $_GET['org_id'] ?? null;

if ($student_id) {
    $stmt = $conn->prepare("SELECT fullName FROM users WHERE ID = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student_name = ucwords(strtolower($result->fetch_assoc()['fullName']));
    }
    $stmt->close();
}

if ($org_id) {
    $stmt = $conn->prepare("SELECT name FROM organizations WHERE id = ?");
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
          <input type="text" value="<?= htmlspecialchars($org_name) ?>" disabled>

          <label>Last Name:</label>
          <input type="text" name="lastName" id="lastName" required>
          
          <label>First Name:</label>
          <input type="text" name="lastName" id="lastName" required>

          <label>Middle Name (N/A if not applicable):</label>
          <input type="text" name="lastName" id="lastName" required>

          <label for="studentNumber">Student Number:</label>
          <input type="text" name="studentNumber" id="studentNumber" required>

        <label for="studentCourse">Course:</label>
        <select id="studentCourse" name="studentCourse">
            <option value="">-- Select Course --</option>
            <option value="DCvET">Diploma in Civil Engineering Technology</option>
            <option value="DCET">Diploma in Computer Engineering Technology</option>
            <option value="DEET">Diploma in Electrical Engineering Technology</option>
            <option value="DECET">Diploma in Electronics Engineering Technology</option>
            <option value="DIT">Diploma in Information Technology</option>
            <option value="DMET">Diploma in Mechanical Engineering Technology</option>
            <option value="DOMT">Diploma in Office Management Technology</option>
            <option value="DRET">Diploma in Railway Engineering Technology</option>
        </select>

        <label for="studentYear">Year:</label>
        <select id="studentYear" name="studentYear">
            <option value="">-- Select Year --</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
    
        <label for="studentSection">Section:</label>
        <input type="text" id="studentSection" name="studentSection">
        <label for="studentEmail">Email:</label>
        <input type="email" id="studentEmail" name="studentEmail">

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
