<?php
require '../config/db_conn.php';

// Fetch users with the role 'orgAdmin'
$sql = "SELECT ID, firstName, middleName, lastName FROM newusers WHERE role = 'orgAdmin'";
$result = $conn->query($sql);

$admins = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Handle cases where middleName might be null or empty
        $middleName = !empty($row['middleName']) ? $row['middleName'] : '';
        $fullName = ucwords(strtolower($row['firstName'] . ' ' . $middleName . ' ' . $row['lastName']));
        
        $admins[] = [
            'id' => $row['ID'],
            'fullName' => $fullName
        ];
    }
}

// Set the content type to JSON and return the response
header('Content-Type: application/json');
echo json_encode($admins);

$conn->close();
?>
