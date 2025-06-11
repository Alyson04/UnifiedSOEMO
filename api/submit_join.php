<?php
require 'auth.php';
require '../config/db_conn.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        $_SESSION['error'] = "Invalid file type. Please upload PDF, DOC, DOCX, JPG, or PNG files only.";
        header("Location: ../students/organizations.php");
        exit;
    }
}

// Insert the application into the join_org table
$sql = "INSERT INTO join_org (student_id, org_id, last_name, first_name, middle_name, student_number, 
        course, year, section, email, portfolio_file, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisssssssss", $student_id, $org_id, $last_name, $first_name, $middle_name, 
                  $student_number, $course, $year, $section, $email, $portfolio_file);

if ($stmt->execute()) {
    // Fetch organization name and admin_id
    $org_name = '';
    $admin_id = null;
    $org_stmt = $conn->prepare("SELECT name, user_id FROM neworganizations WHERE id = ?");
    $org_stmt->bind_param("i", $org_id);
    $org_stmt->execute();
    $org_result = $org_stmt->get_result();
    if ($org_result->num_rows > 0) {
        $org_row = $org_result->fetch_assoc();
        $org_name = $org_row['name'];
        $admin_id = $org_row['user_id'];
    }
    $org_stmt->close();

    // Fetch student full name
    $student_name = $first_name . ' ' . $last_name;

    // Notify student
    $student_message = "Your application to join '$org_name' is under review. Please wait for at least 1 week to see the results.";
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

    $_SESSION['success'] = "Your application has been submitted successfully.";
} else {
    $_SESSION['error'] = "Error submitting your application: " . $conn->error;
}

$stmt->close();
$conn->close();

// Redirect to organizations page
header("Location: ../students/organizations.php");
exit;
?>
