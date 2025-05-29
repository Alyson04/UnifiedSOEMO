<?php
require '../api/auth.php';
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

          <label>Full Name:</label>
          <input type="text" value="<?= htmlspecialchars($student_name) ?>" disabled>

          <label for="contact_number">Contact Number:</label>
          <input type="text" name="contact_number" id="contact_number" required>

          <label for="age">Age:</label>
          <input type="number" name="age" id="age" required>

          <label for="year_section">Year & Section:</label>
          <input type="text" name="year_section" id="year_section" required>

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

<?php include '../includes/footer.php'; ?>
