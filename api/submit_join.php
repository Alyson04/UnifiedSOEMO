<?php
require 'auth.php';
require '../config/db_conn.php';


// Assuming the user is logged in and the session contains the user_id
$student_id = $_SESSION['user_id'] ?? null;
$org_id = $_POST['org_id'] ?? null;
$last_name = $_POST['lastName'];
$first_name = $_POST['firstName'];
$middle_name = $_POST['middleName'] ?? 'N/A'; // Default to N/A if not provided
$student_number = $_POST['studentNumber'];
$course = $_POST['course'];
$year = $_POST['year'];
$section = $_POST['section'];
$email = $_POST['email'];

// Handle file upload
$portfolio_file = null;
if (isset($_FILES['portfolio_file']) && $_FILES['portfolio_file']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['portfolio_file']['name'];
    $file_tmp = $_FILES['portfolio_file']['tmp_name'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

    // Ensure the file is a valid type (PDF, DOCX, JPG, PNG)
    $valid_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    if (in_array(strtolower($file_ext), $valid_types)) {
        $file_path = '../uploads/' . basename($file_name);
        move_uploaded_file($file_tmp, $file_path);
        $portfolio_file = $file_path;
    } else {
        echo "Invalid file type.";
        exit;
    }
} else {
}

// Insert the application into the join_org table
$sql = "INSERT INTO join_org (student_id, org_id, last_name, first_name, middle_name, student_number, 
        course, year, section, email, portfolio_file, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisssssssss", $student_id, $org_id, $last_name, $first_name, $middle_name, 
                  $student_number, $course, $year, $section, $email, $portfolio_file);

if ($stmt->execute()) {
    echo "Your application has been submitted successfully.";
} else {
    echo "Error submitting your application: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
