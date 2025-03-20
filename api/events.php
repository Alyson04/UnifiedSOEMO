<?php
require '../config/db_conn.php';
require 'auth.php';

// Handle different request types
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    checkUserRole('admin'); // Only admins can create events
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['title']) && !empty($data['event_date']) && !empty($data['organization_id'])) {
        $title = $conn->real_escape_string($data['title']);
        $event_date = $conn->real_escape_string($data['event_date']);
        $organization_id = intval($data['organization_id']);

        $sql = "INSERT INTO events (title, event_date, organization_id) VALUES ('$title', '$event_date', '$organization_id')";
        if ($conn->query($sql)) {
            echo json_encode(["message" => "Event created successfully"]);
        } else {
            echo json_encode(["error" => "Failed to create event"]);
        }
    } else {
        echo json_encode(["error" => "Missing required fields"]);
    }
} elseif ($method === 'GET') {
    if (isset($_GET['organization_id'])) {
        $organization_id = intval($_GET['organization_id']);
        $result = $conn->query("SELECT * FROM events WHERE organization_id = $organization_id");
    } else {
        $result = $conn->query("SELECT * FROM events");
    }

    $events = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($events);
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
