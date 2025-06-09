<?php 
require '../config/db_conn.php';
require 'auth.php';

// Check for the required fields in POST request
$requiredFields = ['name', 'description', 'mission', 'vision', 'objective', 'howToJoin', 'requirements'];

$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        $missingFields[] = $field;
    }
}

// If there are any missing fields, return an error message with the list of missing fields
if (count($missingFields) > 0) {
    $_SESSION['error'] = 'Missing required fields: ' . implode(', ', $missingFields);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missingFields)
    ]);
    exit;
}

$orgName = $_POST['name'];
$description = $_POST['description'];
$mission = $_POST['mission'];
$vision = $_POST['vision'];
$objective = $_POST['objective'];
$howToJoin = $_POST['howToJoin'];
$requirements = $_POST['requirements'];

// Optional: Get the status from the form, default to 'active' if not provided
$status = $_POST['status'] ?? 'active';  // If no status is provided, default to 'active'

// Validate name
if (strlen($orgName) < 5 || strlen($orgName) > 100) {
    $_SESSION['error'] = 'Organization name must be between 5 and 100 characters.';
    echo json_encode(['success' => false, 'message' => 'Organization name must be between 5 and 100 characters.']);
    exit;
}

// Validate description
if (strlen($description) < 10 || strlen($description) > 500) {
    $_SESSION['error'] = 'Description must be between 10 and 500 characters.';
    echo json_encode(['success' => false, 'message' => 'Description must be between 10 and 500 characters.']);
    exit;
}

// Validate mission and vision
if (strlen($mission) < 20 || strlen($mission) > 1000) {
    $_SESSION['error'] = 'Mission must be between 20 and 1000 characters.';
    echo json_encode(['success' => false, 'message' => 'Mission must be between 20 and 1000 characters.']);
    exit;
}

if (strlen($vision) < 20 || strlen($vision) > 1000) {
    $_SESSION['error'] = 'Vision must be between 20 and 1000 characters.';
    echo json_encode(['success' => false, 'message' => 'Vision must be between 20 and 1000 characters.']);
    exit;
}

// Validate objective, howToJoin, and requirements
if (strlen($objective) < 10 || strlen($objective) > 500) {
    $_SESSION['error'] = 'Objective must be between 10 and 500 characters.';
    echo json_encode(['success' => false, 'message' => 'Objective must be between 10 and 500 characters.']);
    exit;
}

if (strlen($howToJoin) < 10 || strlen($howToJoin) > 500) {
    $_SESSION['error'] = 'How to join must be between 10 and 500 characters.';
    echo json_encode(['success' => false, 'message' => 'How to join must be between 10 and 500 characters.']);
    exit;
}

if (strlen($requirements) < 10 || strlen($requirements) > 500) {
    $_SESSION['error'] = 'Requirements must be between 10 and 500 characters.';
    echo json_encode(['success' => false, 'message' => 'Requirements must be between 10 and 500 characters.']);
    exit;
}

// Insert org into `neworganizations` table
$stmt2 = $conn->prepare("INSERT INTO neworganizations (name, description, mission, vision, objective, how_to_join, requirements, status, created_at, renewal_date, expiry_date) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 11 MONTH), DATE_ADD(NOW(), INTERVAL 12 MONTH))");

$stmt2->bind_param("ssssssss", $orgName, $description, $mission, $vision, $objective, $howToJoin, $requirements, $status);

if ($stmt2->execute()) {
    $orgId = $conn->insert_id; // Get newly created org_id

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
            $stmtUpdate = $conn->prepare("UPDATE neworganizations SET image_path = ? WHERE id = ?");
            $stmtUpdate->bind_param("si", $newImageName, $orgId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }
    $_SESSION['success'] = 'Organization created successfully!';
    echo json_encode(['success' => true, 'message' => 'Organization created successfully!']);
    exit;
} else {
    $_SESSION['error'] = 'Failed to create organization.';
    echo json_encode(['success' => false, 'message' => 'Failed to create organization.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Missing required data.']);
?>
