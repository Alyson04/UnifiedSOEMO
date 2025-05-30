<?php
require '../config/db_conn.php';
require '../api/auth.php';

$query = $_GET['q'] ?? '';
$query = trim($query);

if ($query === '') {
    // Return all organizations (with user status != deleted)
    $sql = "
        SELECT organizations.*
        FROM organizations
        INNER JOIN users ON organizations.id = users.ID
        WHERE users.status != 'deleted'
        ORDER BY organizations.created_at ASC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<p style="color: black;">Database error: ' . htmlspecialchars($conn->error) . '</p>';
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    // Search organizations with query
    $sql = "
        SELECT organizations.*
        FROM organizations
        INNER JOIN users ON organizations.id = users.ID
        WHERE users.status != 'deleted'
        AND (organizations.name LIKE ? OR organizations.description LIKE ?)
        ORDER BY organizations.created_at ASC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<p style="color: black;">Database error: ' . htmlspecialchars($conn->error) . '</p>';
        exit;
    }
    $likeQuery = '%' . $query . '%';
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row['name']);
        $description = htmlspecialchars($row['description']);
        $imagePath = !empty($row['image_path']) 
            ? "../assets/uploads_organizations/" . htmlspecialchars($row['image_path']) 
            : "../assets/pictures/default.jpg";

        echo '<div class="student-org-card">';
        echo "<img src=\"$imagePath\" alt=\"$name\">";
        echo "<h4>$name</h4>";
        echo "<p>$description</p>";
        echo '<a href="org_page.php?id=' . $row['id'] . '" class="join-btn">LEARN MORE</a>';
        echo '</div>';
    }
} else {
    echo '<p style="color: black;">No organizations found.</p>';
}

$stmt->close();
$conn->close();
?>
