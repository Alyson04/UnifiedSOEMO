<?php 


require '../api/auth.php';
$student_id = $_SESSION['user_id'] ?? null;
$student_name = '';
require '../config/db_conn.php';

// Fetch student's full name from database
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

$org_id = $_GET['id'] ?? null;
$org = null;

if ($org_id) {
    $stmt = $conn->prepare("
        SELECT o.name, o.description, o.image_path, o.created_at,
               l.objective, l.how_to_join, l.requirements, l.highlights
        FROM organizations o
        LEFT JOIN loadorg l ON o.id = l.org_id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $org = $result->fetch_assoc();
    }

    $stmt->close();
}

$conn->close();

$title = "Organizations";
$style = "orgpage_styles.css"; 
include '../includes/header.php';
include '../includes/navbar.php'; 
?>

<div class="outer-container">
<?php if ($org): ?>
    <div class="org-container">
        <div class="corner-top-right"></div>
        <div class="corner-bottom-left"></div>
        
        <?php 
        
        // Prepare image path: prepend folder path if image exists, else default
        if (!empty($org['image_path'])) {
            $imagePath = "../assets/uploads_organizations/" . htmlspecialchars($org['image_path']);
        } else {
            $imagePath = "../assets/pictures/default.jpg";
        }
        ?>
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($org['name']) ?> Logo" class="org-logo">

        <h1><?= htmlspecialchars($org['name']) ?></h1>

        <?php if (!empty($org['description'])): ?>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($org['description'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($org['objective'])): ?>
            <div class="details-box">
                <details>
                    <summary>🎯 Objective</summary>
                    <p><?= nl2br(htmlspecialchars($org['objective'])) ?></p>
                </details>
            </div>
        <?php endif; ?>

        <?php if (!empty($org['how_to_join'])): ?>
            <div class="details-box">
                <details>
                    <summary>📝 How to Join</summary>
                    <p><?= nl2br(htmlspecialchars($org['how_to_join'])) ?></p>
                </details>
            </div>
        <?php endif; ?>

        <?php if (!empty($org['requirements'])): ?>
            <div class="details-box">
                <details>
                    <summary>📌 Requirements</summary>
                    <ul>
                        <?php
                        $requirements = explode(',', $org['requirements']);
                        foreach ($requirements as $req):
                        ?>
                            <li><?= htmlspecialchars(trim($req)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            </div>
        <?php endif; ?>

        <div class="join-section">
            <a href="join_org.php?org_id=<?= urlencode($org_id) ?>" class="join-button">Join Now</a>
        </div>
    </div>

<?php else: ?>
    <p>Organization not found.</p>
<?php endif; ?>
</div>

<script src="../assets/scripts/notif_script.js"></script>

<?php include '../includes/footer.php'; ?>
