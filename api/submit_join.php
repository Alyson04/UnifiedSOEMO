<?php
require 'auth.php';
require '../config/db_conn.php';

$student_id = $_SESSION['user_id'] ?? null;
$org_id = $_POST['org_id'] ?? null;
$contact_number = trim($_POST['contact_number'] ?? '');
$age = trim($_POST['age'] ?? '');
$year_section = trim($_POST['year_section'] ?? '');

// Validate required fields
if (!$student_id || !$org_id || !$contact_number || !$age || !$year_section) {
    die("Missing required fields.");
}

$portfolio_path = null;

// Optional file upload
if (isset($_FILES['portfolio_file']) && $_FILES['portfolio_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "../assets/uploads_portfolios/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $portfolio = $_FILES['portfolio_file'];
    $filename = basename($portfolio['name']);
    $sanitized_name = preg_replace("/[^A-Za-z0-9_.-]/", '_', $filename);
    $target_file = $upload_dir . time() . '_' . $sanitized_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    if (!in_array($file_type, $allowed_types)) {
        die("Invalid file type. Allowed types: PDF, DOC, DOCX, JPG, JPEG, PNG.");
    }

    if (move_uploaded_file($portfolio['tmp_name'], $target_file)) {
        $portfolio_path = $target_file;
    } else {
        die("❌ Failed to upload file.");
    }
}

// Save application
$stmt = $conn->prepare("
    INSERT INTO org_applications 
    (student_id, org_id, contact_number, age, year_section, portfolio_path, applied_at) 
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("iissss", $student_id, $org_id, $contact_number, $age, $year_section, $portfolio_path);

if ($stmt->execute()) {

    // Fetch organization name and admin_id
    $org_name = '';
    $admin_id = null;
    $org_stmt = $conn->prepare("SELECT name, id FROM organizations WHERE id = ?");
    $org_stmt->bind_param("i", $org_id);
    $org_stmt->execute();
    $org_result = $org_stmt->get_result();
    if ($org_result->num_rows > 0) {
        $org_row = $org_result->fetch_assoc();
        $org_name = $org_row['name'];
        $admin_id = $org_row['id'];
    }
    $org_stmt->close();

    // Fetch student full name
    $student_name = '';
    $user_stmt = $conn->prepare("SELECT fullName FROM users WHERE ID = ?");
    $user_stmt->bind_param("i", $student_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows > 0) {
        $student_name = $user_result->fetch_assoc()['fullName'];
    }
    $user_stmt->close();

    // Notify student
    $student_message = "Your application to join '$org_name' is under review. Please wait for atleast 1 week to see the results.";
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif_stmt->bind_param("is", $student_id, $student_message);
    $notif_stmt->execute();
    $notif_stmt->close();

    // Notify admin with student's name
    if ($admin_id) {
        $admin_message = "A new application has been submitted by $student_name to your organization '$org_name'.";
        $admin_notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $admin_notif_stmt->bind_param("is", $admin_id, $admin_message);
        $admin_notif_stmt->execute();
        $admin_notif_stmt->close();
    }

    echo "✅ Application submitted successfully!";
    header("refresh:2;url=../students/dashboard.php");

} else {
    echo "❌ Error saving application: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
