<?php
require '../config/db_conn.php';
require 'auth.php';
checkUserRole('student'); // Only students can join organizations

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];

if (!empty($data['organization_id'])) {
    $organization_id = intval($data['organization_id']);

    $sql = "INSERT INTO user_organizations (user_id, organization_id) VALUES ('$user_id', '$organization_id') 
            ON DUPLICATE KEY UPDATE joined_at = CURRENT_TIMESTAMP";
    
    if ($conn->query($sql)) {
        echo json_encode(["message" => "Joined organization successfully"]);
    } else {
        echo json_encode(["error" => "Failed to join organization"]);
    }
} else {
    echo json_encode(["error" => "Missing required fields"]);
}

$conn->close();
?>
