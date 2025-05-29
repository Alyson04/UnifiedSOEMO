<?php
require '../config/db_conn.php';

$q = $_GET['q'] ?? '';
$q = strtolower(trim($q));

$sql = "SELECT * FROM organizations WHERE LOWER(name) LIKE ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$searchTerm = "%$q%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()):
        $name = htmlspecialchars($row['name']);
        $description = htmlspecialchars($row['description']);
        $imagePath = !empty($row['image_path']) 
            ? "../assets/uploads_organizations/" . htmlspecialchars($row['image_path']) 
            : "../assets/pictures/default.jpg";
?>
        <div class="student-org-card">
            <img src="<?= $imagePath ?>" alt="<?= $name ?>">
            <h4><?= $name ?></h4>
            <p><?= $description ?></p>
            <a href="org_page.php?id=<?= $row['id'] ?>" class="join-btn">LEARN MORE</a>
        </div>
<?php
    endwhile;
} else {
    echo '<div class="no-org-message">No results found.</div>';
}
$stmt->close();
$conn->close();
?>
