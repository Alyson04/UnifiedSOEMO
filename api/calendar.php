<?php
require '../config/db_conn.php';
require 'auth.php';

$today = date("Y-m-d");
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['type']) && $_GET['type'] === 'past') {
        $result = $conn->query("SELECT * FROM events WHERE event_date < '$today' ORDER BY event_date DESC");
    } else {
        $result = $conn->query("SELECT * FROM events WHERE event_date >= '$today' ORDER BY event_date ASC");
    }

    $events = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($events);
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
