<?php
include '../config/db_conn.php';

$orgId = null;

// --- 1. Insert into `organizations` table ---
if (isset($_POST['name'], $_POST['description'])) {
    $orgName = $_POST['name'];
    $description = $_POST['description'];

    // Insert org WITHOUT image first
    $stmt2 = $conn->prepare("INSERT INTO organizations (name, description) VALUES (?, ?)");
    $stmt2->bind_param("ss", $orgName, $description);
    $stmt2->execute();

    $orgId = $conn->insert_id; // Get newly created org_id
    $stmt2->close();

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newImageName = "org$orgId.$ext";
        $uploadDir = "../assets/uploads_organizations/";
        $targetPath = $uploadDir . $newImageName;

        // Ensure upload folder exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Save only the filename in the DB, NOT the full path
            $stmtUpdate = $conn->prepare("UPDATE organizations SET image_path = ? WHERE id = ?");
            $stmtUpdate->bind_param("si", $newImageName, $orgId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }
}

// --- 2. Insert into `users` table and assign org_id ---
if (isset($_POST['fullName'], $_POST['email'], $_POST['password']) && $orgId !== null) {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // secure hash
    $role = $_POST['role'];
    $is_approved = $_POST['is_approved'];

    $stmt1 = $conn->prepare("INSERT INTO users (fullName, email, password, role, is_approved, org_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssssi", $fullName, $email, $password, $role, $is_approved, $orgId);
    $stmt1->execute();
    $stmt1->close();
}

// --- 3. Insert into `loadorg` table ---
if (isset($_POST['objectives'], $_POST['skills'], $_POST['requirements'])) {
    $obj = $_POST['objectives'];
    $how_to_join = $_POST['skills'];
    $requirements = $_POST['requirements'];

    $stmt3 = $conn->prepare("INSERT INTO loadorg (objective, how_to_join, requirements, org_id) VALUES (?, ?, ?, ?)");
    $stmt3->bind_param("sssi", $obj, $how_to_join, $requirements, $orgId);
    $stmt3->execute();
    $stmt3->close();
}
?>


<script src="../assets/scripts/notif_script.js"></script>
<?php include '../includes/footer.php'; ?>