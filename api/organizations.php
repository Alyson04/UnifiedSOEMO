<?php
require '../config/db_conn.php';
require 'auth.php';

// Handle different request types
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    checkUserRole('admin'); // Only admins can create organizations
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['name']) && !empty($data['description'])) {
        $name = $conn->real_escape_string($data['name']);
        $description = $conn->real_escape_string($data['description']);
        $admin_id = $_SESSION['user_id'];

        $sql = "INSERT INTO organizations (name, description, created_by) VALUES ('$name', '$description', '$admin_id')";
        if ($conn->query($sql)) {
            echo json_encode(["message" => "Organization created successfully"]);
        } else {
            echo json_encode(["error" => "Failed to create organization"]);
        }
    } else {
        echo json_encode(["error" => "Missing required fields"]);
    }
} elseif ($method === 'GET') {
    $result = $conn->query("SELECT * FROM organizations");
    $organizations = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($organizations);
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
