<?php
require '../config/db_conn.php';
require 'notifications.php';

// Check for upcoming renewals
checkRenewals();

// Check for expiring memberships
checkExpiringMemberships();

$conn->close();
?> 