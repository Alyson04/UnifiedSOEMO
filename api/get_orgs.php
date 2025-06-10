<?php
require '../config/db_conn.php';

$sql = "SELECT ID, image_path, name, description FROM neworganizations"; // Change to your actual table and column names
$result = $conn->query($sql);

$orgs = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orgs[] = [
            'id' => $row['ID'],
            'img' => $row['image_path'],
            'name' => $row['name'],
            'desc' => $row['description']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($orgs);
$conn->close();
