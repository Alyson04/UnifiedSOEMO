<?php
require '../config/db_conn.php';
require 'auth.php';
checkUserRole('student'); // Only students can join organizations

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];

if (!empty($data['organization_id'])) {
    $organization_id = intval($data['organization_id']);

    // Check if the organization is ID 7 and verify course requirements
    if ($organization_id === 7) {
        $sql = "SELECT course FROM newusers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && $user['course'] !== 'DIT' && $user['course'] !== 'DOMT') {
            $_SESSION['error'] = "Course requirement not met. You must be enrolled in Diploma in Information Technology (DIT) or Diploma in Office Management Technology (DOMT).";
            echo json_encode(["error" => "Course requirement not met"]);
            $conn->close();
            exit;
        }
    }

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
