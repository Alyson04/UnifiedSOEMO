<?php
require '../../config/db_conn.php';

$sql = "
    UPDATE newusers
    SET status = 'renewal'
    WHERE status = 'active'
    AND created_at <= DATE_SUB(NOW(), INTERVAL 11 MONTH)
";

if ($conn->query($sql)) {
    echo "Renewal check complete.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
